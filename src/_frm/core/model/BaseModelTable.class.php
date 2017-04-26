<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 数据模型封装基类，该基类主要是对于特定数据库的操作封装。
 *
 * @author John
 */
class BaseModelTable extends Base
{
    /**
     * 数据表名称
     * @var string
     */
    public $table = null;

    /**
     * 配置项名称.
     *
     * @var string
     */
    public $dbConfigName = 'default';

    /**
     * 数据库链接对象(默认为MySQL，可以被继承类覆盖，也可以重新定义).
     * @var Object
     */
    public $db = null;

    /**
     * 数据表对象构造函数.
     * 
     * @param string $table        表名.
     * @param string $dbConfigName 数据库配置名称.
     * 
     * @throws Exception
     */
    public function __construct($table = '', $dbConfigName = 'default')
    {
        parent::__construct();
        if (!empty($table)) {
            $this->table = $table;
        }
        // 判断变量是否定义
        if (empty($this->table)) {
            exception('Table model not initialed, empty table name!');
        } else {
            $config = Config::getFile();
            if (!empty($config['DataBase'][$dbConfigName])
                && !empty($config['DataBase'][$dbConfigName]['prefix'])) {
                $prefix      = $config['DataBase'][$dbConfigName]['prefix'];
                $this->table = preg_replace("/(\s+)\_(\w+)/", "\$1{$prefix}\$2", ' '.$this->table);
                $this->table = ltrim($this->table);
            }
        }

        if (!empty($dbConfigName)) {
            $this->dbConfigName = $dbConfigName;
        }
        // 初始化数据库链接对象
        if (empty($this->db)) {
            $this->db = Instance::database($this->dbConfigName);
        }
    }

    /**
     * 获得对象的方法，请使用该方法获得对象.
     * 
     * @param mixed  $table        表名.
     * @param string $dbConfigName 数据库配置名称.
     * 
     * @return BaseModelTable
     */
    public static function getInstance ($table = '', $dbConfigName = 'default')
    {
        if (is_array($table)) {
            $table = implode(' ', $table);
        }
        $key = "_OBJ_TABLE_MODELS_{$dbConfigName}_{$table}";
        $obj = &Data::get($key);
        if (empty($obj)) {
            $className = __CLASS__;
            $obj       = new $className($table, $dbConfigName);
            Data::set($key, $obj);
        }

        return $obj;
    }

    /**
     * 根据条件查询记录数。
     * @param  mixed $condition 条件.
     * @param  mixed $groupBy   分组.
     * @param  mixed $fields    用于获得数量用到的字段.
     *
     * @return int
     */
    public function getCount($condition = array(), $groupBy = array(), $fields = array())
    {
        return $this->db->count($this->table, $condition, $groupBy, $fields);
    }

    /**
     *
     * 查询记录。
     *
     * @param mixed  $fields          查询字段.
     * @param mixed  $conditions      查询条件.
     * @param mixed  $groupBy         分组.
     * @param mixed  $orderBy         排序.
     * @param int    $first           分页起始.
     * @param int    $limit           查询条数.
     * @param string $arrayKey        作为返回数组的主键的字段名.
     *
     * @return array
     */
    public function getAll($fields     = array('*'),
                           $conditions = array(),
                           $groupBy    = array(),
                           $orderBy    = array(),
                           $first      = 0,
                           $limit      = 0,
                           $arrayKey   = null)
    {
        return $this->db->select($this->table, $fields, $conditions, $groupBy, $orderBy, $first, $limit, $arrayKey);
    }

    /**
     * 根据条件获得一条记录。
     *
     * @param mixed  $fields          查询字段.
     * @param mixed  $conditions      查询条件.
     * @param mixed  $groupBy         分组.
     * @param mixed  $orderBy         排序.
     *
     * @return array
     */
    public function getOne($fields     = array('*'),
                           $conditions = array(),
                           $groupBy    = array(),
                           $orderBy    = array())
    {
        $result = $this->db->select($this->table, $fields, $conditions, $groupBy, $orderBy, 0, 1);
        if (!empty($result) && isset($result[0])) {
            $result = $result[0];
        }
        return $result;
    }

    /**
     * 根据条件获得一条字段的值.
     *
     * @param string  $field          查询字段.
     * @param mixed   $conditions     查询条件.
     * @param mixed   $groupBy        分组.
     * @param mixed   $orderBy        排序.
     *
     * @return string
     */
    public function getValue($field,
                             $conditions = array(),
                             $groupBy    = array(),
                             $orderBy    = array())
    {
        $val = null;
        $one = $this->getOne($field, $conditions, $groupBy, $orderBy);
        if (!empty($one)) {
            list($_, $val) = each($one);
        }
        return $val;
    }

    /**
     * 添加记录，并返回添加记录的ID，失败返回false.
     * 注意：如果主键为非自增ID，那么成功会返回0，因此判断返回值是否为false来判断是否执行成功.
     *
     * @param  array   $data          写入的数据.
     * @param  mixed   $option        选项(replace:同记录替换, update:同记录更新, ignore:同记录忽略, 默认直接写入)
     * @param  boolean $getInsertedId 获取插入的主键ID(存在自动增加主键时有用).
     *
     * @return int|false
     */
    public function insert(array $data, $option = '', $getInsertedId = true)
    {
        $result = $this->db->insert($this->table, $data, $option);
        if ($result !== false) {
            if ($getInsertedId) {
                return $this->db->lastInsertId();
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * 批量添加记录，成功返回true，失败返回false.
     *
     * @param  array   $list     数据数组
     * @param  integer $perCount 每次写入的数据量
     * @param  mixed   $option   选项(replace:同记录替换, update:同记录更新, ignore:同记录忽略, 默认直接写入)
     *
     * @return boolean
     */
    public function batchInsert(array $list, $perCount = 10, $option = '')
    {
        return $this->db->batchInsert($this->table, $list, $perCount, $option);
    }

    /**
     * 保存记录(如果数据中存在主键或者唯一索引，那么执行更新，否则执行插入).
     *
     * @param  array   $data          写入的数据.
     * @param  boolean $getInsertedId 获取插入的主键ID(存在自动增加主键时有用).
     *
     * @return int|false
     */
    public function save(array $data, $getInsertedId = false)
    {
        return $this->insert($data, 'update', $getInsertedId);
    }

    /**
     * 批量保存记录(如果数据中存在主键或者唯一索引，那么执行更新，否则执行插入).
     *
     * @param  array   $list     数据列表.
     * @param  integer $perCount 批量写入的每批大小.
     *
     * @return boolean
     */
    public function batchSave(array $list, $perCount = 10)
    {
        return $this->batchInsert($list, $perCount, 'update');
    }

    /**
     * 更新记录.
     *
     * @param  mixed  $data       更新数据.
     * @param  mixed  $conditions 更新条件.
     *
     * @return boolean
     */
    public function update($data, $conditions)
    {
        $result = $this->db->update($this->table, $data, $conditions);
        return !empty($result);
    }

    /**
     * 删除记录.
     *
     * @param  mixed  $conditions 更新条件.
     *
     * @return boolean
     */
    public function delete($conditions = array())
    {
        $result = $this->db->delete($this->table, $conditions);
        return !empty($result);
    }

    /**
     * ==================================================================================================
     * 以下是MySQL定制化的操作方法，只在MySQL数据库上有效
     * ==================================================================================================
     */

    /**
     * 获取表字段列表，构成数组返回.
     *
     * @param mixed     $filtFields        需要过滤的字段列表(可以是字符串-用逗号分隔，也可以是数组).
     * @param bool|true $withoutPrimaryKey 是否去掉主键字段.
     *
     * @return array
     */
    public function mysqlGetFieldArray($filtFields = array(), $withoutPrimaryKey = true)
    {

        if (is_array($filtFields)) {
            $filtFieldArray = $filtFields;
        } else {
            $filtFieldArray = explode(',', trim($filtFields));
        }
        $fileds = array();
        $result = $this->db->query("SHOW COLUMNS FROM `{$this->table}`");
        while ($row = $this->db->fetchAssoc($result)) {
            // 是否去掉主键字段
            if ($withoutPrimaryKey && $row['Key'] == 'PRI') {
                continue;
            }
            // 是否需要过滤该字段
            if (in_array($row['Field'], $filtFieldArray)) {

            }
            $fileds[] = $row['Field'];
        }
        return $fileds;
    }

    /**
     * 获取过滤的表字段，构成字符串返回.
     *
     * @param mixed     $filtFields        需要过滤的字段列表(可以是字符串-用逗号分隔，也可以是数组).
     * @param bool|true $withoutPrimaryKey 是否去掉主键字段.
     *
     * @return string
     */
    public function mysqlGetFieldStr($filtFields = array(), $withoutPrimaryKey = true)
    {
        $fieldStr   = '';
        $fieldArray = $this->mysqlGetFieldArray($this->table, $filtFields, $withoutPrimaryKey);
        if (!empty($fieldArray)) {
            $fieldStr = "'".implode("','", $fieldArray)."'";
        }
        return $fieldStr;
    }

    /**
     * 根据表字段过滤数组，数组键名不是表字段时，直接过滤掉。
     *
     * @param  array     $data              数据数组.
     * @param  bool|true $withoutPrimaryKey 是否去掉主键字段.
     *
     * @return array
     */
    public function mysqlFiltDataArray(array $data, $withoutPrimaryKey = true)
    {
        $fields = $this->mysqlGetFieldArray($this->table, $withoutPrimaryKey);
        foreach ($data as $k => $v) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * MySQL过滤写入操作，内部调用mysqlFiltDataArray进行数组过滤.
     *
     * @param array   $data          关联数组.
     * @param string  $option        插入选项.
     * @param boolean $getInsertedId 获取插入的主键ID(存在自动增加主键时有用).
     *
     * @return false|PDOStatement
     */
    public function mysqlFiltInsert(array $data, $option = '', $getInsertedId = true)
    {
        return $this->insert($this->mysqlFiltDataArray($data), $option, $getInsertedId);
    }

    /**
     * MySQL的过滤更新，内部调用mysqlFiltDataArray进行数组过滤.
     *
     * @param array  $data       关联数组
     * @param mixed  $conditions SQL的操作条件
     *
     * @return false|PDOStatement
     */
    public function mysqlFiltUpdate(array $data, $conditions)
    {
        return $this->update($this->mysqlFiltDataArray($data), $conditions);
    }

    /**
     * MySQL的过滤保存，内部调用mysqlFiltDataArray进行数组过滤，如果存在主键或者唯一索引，那么执行更新，否则执行写入.
     *
     * @param array $data 关联数组.
     *
     * @return int|false
     */
    public function mysqlFiltSave(array $data)
    {
        return $this->insert($this->mysqlFiltDataArray($data, false), 'update');
    }
}