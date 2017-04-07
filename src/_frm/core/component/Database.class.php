<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 数据库抽象层基类，子类必须定义抽象方法，这些方法是常用的数据库操作。
 * 该基类已包含一些定义的公共继承方法。
 *
 *
 * @author John
 */
class Database
{
    protected $_options     = array(); // 连接参数
    protected $_debug    	= true;    // 是否调试，在调试状态下，类会记录很多有用的调试信息(例如SQL执行语句及执行时间等等)
    protected $_sqls     	= array(); // 执行过的SQL语句列表，包含执行时间
    protected $_maxSqlCount	= 1000;    // 记录执行的SQL的最大大小，调试模式下防止内存占用
    protected $_link     	= null;    // 当前对象建立数据库连接后保存的连接
    protected $_links    	= array(); // 用于主从连接时，用于存放主从两个连接(主从模式下，该对象会根据权重随机选用两个配置进行操作，所以同一个数据库对象中最多只存放主从两个连接)
    protected $_linkInfo 	= null;    // PDO连接信息
    protected $_halt       	= true;    // 当数据库错误发生时停止执行并显示错误
    protected $_error    	= null;    // 最新一次错误
    protected $_mode     	= null;    // 执行模式(master|slave)
    // 数据库类型对应的保留字操作符(当字段带有关键字时，用以做区分)
    protected static $_typeToReserveChars = array(
        'mysql'  => '`',
        'pgsql'  => '"',
        'mssql'  => '[]',
        'sqlite' => '`',
        'oracle' => '"',
    );


    /**
     *
     * 构造函数只保存所需变量.
     *
     * @param array $options 配置参数.
     *
     * @return void
     */
    public function __construct($options)
    {
        $this->_options = $options;
    }

    /**
     * 初始化数据库连接。
     * 使用不同的数据库管理系统，连接方式会有所不同，这里在初始化的时候进行处理。
     *
     * @param string $mode 主从模式(master|slave)，如果不是主从模式，默认为空.
     *
     * @return PDO操作对象|null
     */
    public function getLink($mode = '')
    {
        $option = array();

        // 成员变量的优先级更高
        if (!empty($this->_mode)) {
            $mode = $this->_mode;
        }

        /**
         * 主从连接优先进行判断.
         */
        if (!empty($mode) && !empty($this->_options[$mode])) {
            $this->_link     = null;
            $this->_linkInfo = null;
            if (!empty($this->_links[$mode])) {
                $this->_link     = $this->_links[$mode]['link'];
                $this->_linkInfo = $this->_links[$mode]['linkinfo'];
            } else {
                $option = $this->_getOptionFromListByPriority($this->_options[$mode]);
            }
        }

        /**
         * 数据库连接判断。
         * 注意不同的数据库下，SQL的操作可能会有所区别。
         */
        if (empty($this->_link)) {
            if (empty($option)) {
                $option = &$this->_options;
            }
            try {
                if (empty($this->_linkInfo)) {
                    if (!empty($option['linkinfo'])) {
                        $this->_linkInfo = $option['linkinfo'];
                    } else {
                        switch ($option['type']) {
                            case 'mysql':
                                $charsetStr = '';
                                if (!empty($option['charset'])) {
                                    $charsetStr = ";charset={$option['charset']}";
                                }
                                $this->_linkInfo = "mysql:host={$option['host']};port={$option['port']};dbname={$option['database']}{$charsetStr}";
                                break;

                            case 'pgsql':
                                $this->_linkInfo = "pgsql:host={$option['host']};port={$option['port']};dbname={$option['database']}";
                                break;

                            case 'sqlite':
                                $this->_linkInfo = "sqlite:{$option['database']}";
                                break;

                            case 'oracle':
                                $charsetStr = '';
                                if (!empty($option['charset'])) {
                                    $charsetStr = ";charset={$option['charset']}";
                                }
                                /**
                                 * Oracle连接采用SID的方式.
                                 */
                                $tns = "(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)
                                        (HOST={$option['host']})(PORT={$option['port']})))
                                        (CONNECT_DATA=(SID={$option['database']})))";
                                // $this->_linkInfo = "oci:dbname=//{$option['host']}:{$option['port']}/{$option['database']}{$charsetStr}";
                                $this->_linkInfo = "oci:dbname={$tns}{$charsetStr}";
                                break;

                            case 'mssql':
                                $this->_linkInfo = "sqlsrv:Server={$option['host']};Database={$option['database']}";
                                break;

                            default:
                                $this->_halt("Database type '{$option['type']}' is not supported!");
                                break;
                        }
                    }
                }
                $this->_link = new \PDO($this->_linkInfo, $option['user'], $option['pass']);
            } catch (\Exception $e) {
                $this->_halt($e->getMessage());
            }
        }

        /**
         * 设置主从连接信息
         */
        if (!empty($mode) && !empty($this->_links[$mode])) {
            $this->_links[$mode] = array(
                'link'     => $this->_link,
                'linkinfo' => $this->_linkInfo,
            );
        }
        return $this->_link;
    }

    /**
     * 根据配置判断数据库使用的保留操作字段字符是什么。
     *
     * @param bool $left 该字符是否放于左边.
     *
     * @return string
     */
    private function _getReserveChar($left = true)
    {
        if (isset($this->_options['master'])) {
            $option = $this->_options['master'][0];
        } else {
            $option = &$this->_options;
        }
        $char = '';
        if (isset(self::$_typeToReserveChars[$option['type']])) {
            $reserveChar = self::$_typeToReserveChars[$option['type']];
            if (strlen($reserveChar) > 1 && $left == false) {
                $char = $reserveChar[1];
            } else {
                $char = $reserveChar;
            }
        }
        return $char;
    }

    /**
     * 数据库主从架构根据配置项的优先级确定使用哪个配置项.
     *
     * @param array $options 配置项列表.
     *
     * @return array
     */
    private function _getOptionFromListByPriority($options)
    {
        $option     = array();
        $index      = 0;
        $totalCount = 0;
        foreach ($options as $k => $v) {
            if (isset($v['priority'])) {
                $priority           = $v['priority']*100;
                $totalCount        += $priority;
                $options[$k]['min'] = $index;
                $options[$k]['max'] = $index + $priority;
                $index              = $totalCount + 1;
            }
        }
        $rand = rand(0, $totalCount);
        foreach ($options as $k => $v) {
            if (isset($v['priority'])) {
                if ($rand >= $v['min'] && $rand <= $v['max']) {
                    $option = $v;
                    break;
                }
            }
        }
        return $option;
    }

    /**
     * 设置运行模式.
     *
     * @param string $mode 模式(master|slave).
     *
     * @return void
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;
    }

    /**
     * 设置当前在master上执行.
     *
     * @return void
     */
    public function setMaster()
    {
        $this->setMode('master');
    }

    /**
     * 设置当前在slave上执行.
     *
     * @return void
     */
    public function setSlave()
    {
        $this->setMode('slave');
    }

    /**
     * 用户可自定义连接信息.
     *
     * @param string $linkInfo PDO连接信息.
     *
     * @return void
     */
    public function setLinkInfo($linkInfo)
    {
        $this->_linkInfo = $linkInfo;
    }

    /**
     * 设置是否错误发生时停止执行脚本并报错
     *
     * @param boolean $halt
     */
    public function setHalt($halt)
    {
        $this->_halt = $halt;
    }

    /**
     * 设置调试模式.
     *
     * @param boolean $debug 是否调试(0:否，1:是)
     *
     * @return void
     */
    public function setDebug($debug)
    {
        $this->_debug = $debug;
    }

    /**
     * 设置记录执行的SQL数组最大大小，超过这个大小则从头开始删除旧的SQL，保留最新的SQL.
     *
     * @param integer $count 大小.
     *
     * @return void
     */
    public function setMaxRecordedSqlCount($count)
    {
        $this->_maxSqlCount = $count;
    }

    /**
     * 获得最新一次的数据库操作错误
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 获得已经执行完毕的SQL列表.
     *
     * @return array
     */
    public function getQueriedSqls()
    {
        return $this->_sqls;
    }

    /**
     * 执行一条SQL操作，注意返回的数据类型是和exec方法不一样的。
     * query方法一般用于select查询。
     *
     * @param string $sql        SQL语句.
     * @param array  $bindParams 预执行绑定数据数组.
     * @param string $mode       主从模式(master|slave).
     *
     * @return PDOStatement|false
     */
    public function query($sql, $bindParams = array(), $mode = '')
    {
        if (!empty($bindParams)) {
            return $this->prepareExecute($sql, $bindParams, $mode);
        }
        $start  = microtime(true);
        $result = $this->getLink($mode) ? $this->getLink($mode)->query($sql) : false;
        $end    = microtime(true);
        if ($this->_debug) {
            $this->_recordSql(array(
                'sql'    => $sql,
                'mode'   => $mode,
                'cost'   => $end - $start,
                'link'   => &$this->_linkInfo,
                'time'   => time(),
                'method' => __FUNCTION__,
                'params' => $bindParams,
            ));
        }
        if ($result === false) {
            $this->_halt();
        }
        return $result;
    }

    /**
     * 预处理执行SQL语句.
     *
     * @param string $sql        SQL.
     * @param array  $bindParams 预处理绑定数据数组.
     * @param string $mode       主从模式(master|slave).
     *
     * @return PDOStatement|false
     */
    public function prepareExecute($sql, $bindParams = array(), $mode = '')
    {
        $result = $this->_doPrepareExecute($sql, $bindParams, $mode);
        if ($result === false) {
            $this->_halt();
        }
        return $result;
    }

    /**
     * 预处理SQL.
     *
     * @param string $sql  SQL.
     * @param string $mode 模式.
     *
     * @return PDOStatement | false
     */
    public function prepare($sql, $mode = '')
    {
        return $this->getLink($mode) ? $this->getLink($mode)->prepare($sql) : false;
    }

    /**
     * 预处理执行SQL语句.
     *
     * @todo 不支持in(?)这样的预处理，参见pdo::execute方法的官方说明。这种情况下采用拼接字符串的方式来处理.
     *
     * @param string $sql        SQL.
     * @param array  $bindParams 预处理绑定数据数组.
     * @param string $mode       主从模式(master|slave).
     *
     * @return PDOStatement|false
     */
    protected function _doPrepareExecute($sql, $bindParams = array(), $mode = '')
    {
        $result = false;
        $stmt   = $this->getLink($mode) ? $this->getLink($mode)->prepare($sql) : false;
        if (!empty($stmt)) {
            $start  = microtime(true);
            $result = $stmt->execute($bindParams);
            $end    = microtime(true);
            if ($this->_debug) {
                $this->_recordSql(array(
                    'sql'    => $sql,
                    'mode'   => $mode,
                    'cost'   => $end - $start,
                    'link'   => &$this->_linkInfo,
                    'time'   => time(),
                    'method' => __FUNCTION__,
                    'params' => $bindParams,
                ));
            }
            if ($result === false) {
                $errorInfo = $stmt->errorInfo();
                $this->_halt($errorInfo[2]);
            } else {
                $result = $stmt;
            }
        }
        return $result;
    }


    /**
     * 执行事务语句,返回受影响的记录数(该函数不支持预处理)。
     * 长用在事务处理操作中。
     *
     * @param string $sql  SQL.
     * @param string $mode 主从模式(master|slave).
     *
     * @return int|false
     */
    public function exec($sql, $mode = '')
    {
        $start  = microtime(true);
        $result = $this->getLink($mode) ? $this->getLink($mode)->exec($sql) : false;
        $end    = microtime(true);
        if ($this->_debug) {
            $this->_recordSql(array(
                'sql'    => $sql,
                'mode'   => $mode,
                'cost'   => $end - $start,
                'link'   => &$this->_linkInfo,
                'time'   => time(),
                'method' => __FUNCTION__,
                'params' => array(),
            ));
        }
        if($result === false){
            $this->_halt();
        }
        return $result;
    }

    /**
     * 开启事务(必需在主库上执行)
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->getLink('master') ? $this->getLink('master')->beginTransaction() : false;
    }

    /**
     * 回滚事务操作，当commit之后该操作无效(必需在主库上执行).
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->getLink('master') ? $this->getLink('master')->rollBack() : false;
    }

    /**
     * 提交事务操作(必需在主库上执行).
     *
     * @return bool
     */
    public function commit()
    {
        return $this->getLink('master') ? $this->getLink('master')->commit() : false;
    }

    /**
     * 取得结果集中行的数目。
     *
     * @param  PDOStatement $result 数据库操作结果资源
     *
     * @return int
     */
    public function rows(\PDOStatement &$result)
    {
        return @$result->rowCount();
    }

    /**
     * 从结果集中取得一行作为关联数组，及数字数组，二者兼有。
     * @link http://php.net/manual/en/pdostatement.fetch.php
     *
     * @param  PDOStatement $result 数据库操作结果资源.
     *
     * @return array
     */
    public function fetchArray(\PDOStatement &$result)
    {
        return @$result->fetch(\PDO::FETCH_BOTH);
    }

    /**
     * 从结果集中取得一行作为关联数组。
     * @link http://php.net/manual/en/pdostatement.fetch.php
     *
     * @param  PDOStatement $result 数据库操作结果资源.
     * @return array
     */
    public function fetchAssoc(\PDOStatement &$result)
    {
        return @$result->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 从结果集中取得一行作为枚举数组。
     * 每个结果的列储存在一个数组的单元中，偏移量从0开始。
     * @link http://php.net/manual/en/pdostatement.fetch.php
     *
     * @param  PDOStatement $result 数据库操作结果资源
     * @return array
     */
    public function fetchRow(\PDOStatement &$result)
    {
        return @$result->fetch(\PDO::FETCH_NUM);
    }

    /**
     * 取得结果中指定字段的字段名。
     *
     * @param  PDOStatement $result 数据库操作结果资源
     * @param  int          $index
     * @return string
     */
    public function fieldName(\PDOStatement &$result, $index)
    {
        return @$result->fetchColumn($index);
    }

    /**
     * 返回上一次执行insert操作产生的ID.
     * 注意：在MySQL中，只有当主键为自增字段时返回值>0，否则返回值为0。
     *
     * @return int
     */
    public function lastInsertId()
    {
        return @$this->_link->lastInsertId();
    }

    /**
     * 关闭数据库连接。
     *
     * @return void
     */
    public function close()
    {
        $this->_link  = null;
        $this->_links = array();
    }

    /**
     * 获取SQL返回的一条记录(SQL只获取一条记录关联数组)
     *
     * @param string $sql        SQL.
     * @param array  $bindParams 预处理绑定数据数组.
     *
     * @return array
     */
    public function getOne($sql, $bindParams = array())
    {
        $one    = array();
        $result = $this->query($sql, $bindParams, 'slave');
        if (!empty($result)) {
            $one = $this->fetchAssoc($result);
        }
        if (empty($one)) {
            $one = array();
        }
        return $one;
    }

    /**
     * 返回查询的结果列表，列表索引从0开始，每一项为一个数组。
     *
     * @param string $sql        SQL.
     * @param array  $bindParams 预处理绑定数据数组.
     *
     * @return array
     */
    public function getAll($sql, $bindParams = array())
    {
        $list   = array();
        $result = $this->query($sql, $bindParams, 'slave');
        if (!empty($result)) {
            while ($row = $this->fetchAssoc($result)) {
                $list[] = $row;
            }
        }
        return $list;
    }

    /**
     * 返回查询的记录数。
     *
     * @param string $sql        SQL.
     * @param array  $bindParams 预处理绑定数据数组.
     *
     * @return int
     */
    public function getCount($sql, $bindParams = array())
    {
        $count  = 0;
        $result = $this->query($sql, $bindParams, 'slave');
        if (!empty($result)) {
            $row   = $this->fetchRow($result);
            $count = intval($row[0]);
        }

        return $count;
    }

    /**
     * 根据条件获得一条字段的值。
     *
     * @param mixed   $tables         查询表名(可以查询多张表).
     * @param string  $field          查询字段.
     * @param mixed   $conditions     查询条件.
     * @param mixed   $groupBy        分组.
     * @param mixed   $orderBy        排序.
     *
     * @return string
     */
    public function getValue($tables,
                             $field,
                             $conditions = array(),
                             $groupBy    = array(),
                             $orderBy    = array())
    {
        $val = null;
        $one = array();
        $result = $this->select($tables, $field, $conditions, $groupBy, $orderBy, 0, 1);
        if (!empty($result) && isset($result[0])) {
            $one = $result[0];
        }
        if (!empty($one)) {
            list($_, $val) = each($one);
        }

        return $val;
    }

    /**
     *
     * 获得记录列表.
     *
     * @param mixed  $tables          查询表名(可以查询多张表).
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
    public function select($tables,
                           $fields     = array('*'),
                           $conditions = array(),
                           $groupBy    = array(),
                           $orderBy    = array(),
                           $first    = 0,
                           $limit    = 0,
                           $arrayKey = null)
    {
        $list          = array();
        $limitStr      = ($limit == 0) ? '' : " LIMIT {$first}, {$limit}";
        $newConditions = $this->_formatCondition($conditions);
        $tableStr      = $this->_formatTables($tables);
        $fieldsStr     = $this->_formatFields($fields);
        $groupByStr    = $this->_formatGroupBy($groupBy);
        $orderByStr    = $this->_formatOrderBy($orderBy);
        $conditionStr  = empty($newConditions[0]) ? '' : "WHERE {$newConditions[0]}";
        $sql           = "SELECT {$fieldsStr} FROM {$tableStr} {$conditionStr} {$groupByStr} {$orderByStr} {$limitStr}";
        if (!empty($arrayKey)) {
            $result = $this->query($sql, $newConditions[1], 'slave');
            if (!empty($result)) {
                while ($row = $this->fetchAssoc($result)) {
                    if (isset($row[$arrayKey])) {
                        $list[$row[$arrayKey]] = $row;
                    } else {
                        $list[] = $row;
                    }
                }
            }

        } else {
            $list = $this->getAll($sql, $newConditions[1]);
        }
        return $list;
    }

    /**
     * 根据条件查询记录数。
     * @param mixed  $tables     查询表名(可以查询多张表).
     * @param mixed  $conditions 条件数组.
     * @param mixed  $groupBy    分组.
     * @param mixed  $fields     用于获得数量用到的字段.
     * @return int
     */
    public function count($tables, $conditions = array(), $groupBy = array(), $fields = array())
    {
        $tableStr      = $this->_formatTables($tables);
        $newConditions = $this->_formatCondition($conditions);
        $conditionStr  = empty($newConditions[0]) ? '' : "WHERE {$newConditions[0]}";
        $groupByStr    = $this->_formatGroupBy($groupBy);
        $fieldsStr     = $this->_formatFields($fields);
        if (empty($fieldsStr) || $fieldsStr == '*') {
            $fieldsStr = 'COUNT(1)';
        }
        $sql = "SELECT {$fieldsStr} FROM {$tableStr} {$conditionStr} {$groupByStr}";
        if (!empty($groupByStr)) {
            $sql = "SELECT COUNT(1) FROM ({$sql}) count_alias";
        }
        return $this->getCount($sql, $newConditions[1]);
    }

    /**
     * 格式化表名语句.
     *
     * @param mixed $tables 查询数据表.
     *
     * @return string
     */
    protected function _formatTables($tables)
    {
        $tableStr = '';
        if (is_array($tables)) {
            foreach ($tables as $v) {
                $tableStr .= "{$v} ";
            }
        } else {
            $tableStr = $tables;
        }
        $tableStr = rtrim($tableStr);
        return $tableStr;
    }

    /**
     * 格式化字段数组语句，并返回可用在SQL语句中的语句字符串.
     *
     * @param mixed $fields 查询字段数组.
     *
     * @return string
     */
    protected function _formatFields($fields)
    {
        $fieldsStr = '';
        if (empty($fields)) {
            $fieldsStr = '*';
        } else {
            if (is_array($fields)) {
                foreach ($fields as $v) {
                    $fieldsStr .= trim($v).',';
                }
                $fieldsStr = rtrim($fieldsStr, ',');
            } else {
                $fieldsStr = $fields;
            }
        }
        return $fieldsStr;
    }


    /**
     * 将条件构造为以位置为主的预处理方式(注意预处理条件语句不支持in条件，必须转换为字符串输入).
     *
     * @param mixed $conditions 条件数组.
     * @param int   $index      Index(基本不用，内部只用于update方法).
     *
     * @return array
     */
    protected function _formatCondition($conditions, $index = 0)
    {
        $newCondition = array(
            0 => '',
            1 => array()
        );

        // 使用默认值(不传递条件参数)
        if (empty($conditions)) {
            return $newCondition;
        }

        // (不推荐)支持条件变量直接传字符串，例如:
        // "name='john' and age>18"
        if (!is_array($conditions)) {
            $newCondition[0] = $conditions;
            return $newCondition;
        }

        // 支持 key => value 单条件形式，例如：
        // array(
        //     'name' => 'john',
        //     'age'  => 18,
        // )
        if (is_array($conditions) && !empty($conditions) && empty($conditions[0])) {
            foreach ($conditions as $k => $v) {
                $newCondition[0]  .= empty($newCondition[0]) ? "{$k}=?" : " AND {$k}=?";
                $newCondition[1][] = $v;
            }
            return $newCondition;
        }

        // 多个条件组成一个大的条件数组(用第一个元素是否是数组来判断，正常单条件应当是字符串)，这时需要拆开一个一个递归处理，
        // 支持条件语句中带and或者or
        // 例如：
        // array(
        //     array('name=?', 'john'),
        //     array('age>?',  18),
        // );
        // array(
        //     array('name=?',     'john'),
        //     array('or name=?',  'johnson'),
        // );
        if (is_array($conditions) && is_array($conditions[0])) {
            foreach ($conditions as $k => $v) {
                $tempCondition    = $this->_formatCondition($v);
                $newCondition[0]  = trim($newCondition[0]);
                $tempCondition[0] = trim($tempCondition[0]);
                // 判断是否需要自动加上连接词(默认使用and连接)
                if (!empty($newCondition[0])) {
                    $keys  = array('and', 'or');
                    $array = explode(' ', $tempCondition[0]);
                    if (!in_array(strtolower($array[0]), $keys)) {
                        $array = explode(' ', $newCondition[0]);
                        if (!in_array(strtolower($array[count($array) - 1]), $keys)) {
                            $newCondition[0] .= ' AND';
                        }
                    }
                }
                $newCondition[0] .= ' '.$tempCondition[0];
                $newCondition[1]  = array_merge($newCondition[1], $tempCondition[1]);
            }
            return $newCondition;
        }

        // (不推荐)数组只有1个元素，是字符串条件，字符串中包含查询条件和值，例如：
        // array("name='john' and age > 18")
        if (is_array($conditions) && isset($conditions[0]) && !isset($conditions[1])) {
            $newCondition[0] = $conditions[0];
            return $newCondition;
        }

        // (推荐)支持单数组的条件查询方式(预处理变量放在一个数组中，第一个是条件字符串，其他的是预处理变量)，例如：
        // array('name=? and age > ?', 'john', 18)
        if (is_array($conditions) && isset($conditions[0]) && isset($conditions[1]) && !is_array($conditions[1])) {
            $tempCondition = array(
                0 => $conditions[0],
                1 => array()
            );
            $i = 1;
            while (true) {
                if (!isset($conditions[$i])) {
                    break;
                }
                $tempCondition[1][] = $conditions[$i];
                ++$i;
            }
            $conditions = $tempCondition;
        }


        // 根据$index索引重新组织预处理变量的索引顺序
        foreach ($conditions[1] as $k => $v) {
            $newCondition[1][$index++] = $v;
        }
        if (isset($conditions[1][0])) {
            $newCondition[0] = $conditions[0];
        } else {
            $searchArray  = array();
            $replaceArray = array();
            foreach ($conditions[1] as $k => $v) {
                $searchArray[]    = $k;
                $replaceArray[$k] = '?';
            }
            $newCondition[0] = str_replace($searchArray, $replaceArray, $conditions[0]);
        }
        return $newCondition;
    }

    /**
     * 格式化GROUP BY语句，并返回可用在SQL语句中的语句字符串.
     *
     * @param mixed $groupBy GROUP BY.
     *
     * @return string
     */
    protected function _formatGroupBy($groupBy)
    {
        $groupByStr = '';
        if (!empty($groupBy)) {
            $groupByStr = 'GROUP BY ';
            if (is_array($groupBy)) {
                foreach ($groupBy as $v) {
                    $groupByStr .= "{$v},";
                }
                $groupByStr = rtrim($groupByStr, ',');
            } else {
                $groupByStr .= $groupBy;
            }
        }
        return $groupByStr;
    }

    /**
     * ORDER BY语句，并返回可用在SQL语句中的语句字符串.
     *
     * @param mixed $orderBy ORDER BY.
     *
     * @return string
     */
    protected function _formatOrderBy($orderBy)
    {
        $orderByStr = '';
        if (!empty($orderBy)) {
            $orderByStr = 'ORDER BY ';
            if (is_array($orderBy)) {
                foreach ($orderBy as $k => $v) {
                    $v           = strtoupper($v);
                    $orderByStr .= "{$k} {$v},";
                }
                $orderByStr = rtrim($orderByStr, ',');
            } else {
                $orderByStr .= $orderBy;
            }
        }
        return $orderByStr;
    }

    /**
     * 记录执行的SQL信息.
     *
     * @param array $data SQL信息数组.
     *
     * @return void
     */
    protected function _recordSql(array $data)
    {
        static $sqlCount     = 0;
        static $sqlDropIndex = 0;

        $this->_sqls[] = $data;
        if (++ $sqlCount > $this->_maxSqlCount) {
            unset($this->_sqls[$sqlDropIndex]);
            $sqlCount --;
            $sqlDropIndex ++;
        }
    }

    /**
     * 插入数据操作.
     *
     * @param  string $table  表名称.
     * @param  array  $data   关联数组.
     * @param  mixed  $option 选项(replace:同记录替换, update:同记录更新，ignore:同记录忽略, 默认直接写入).
     *
     * @return PDOStatement|false
     */
    public function insert($table, array $data, $option = '')
    {
        if (!empty($data)) {
            $charLeft  = $this->_getReserveChar();
            $charRight = $this->_getReserveChar(false);
            foreach ($data as $key => $value) {
                $keys[]   = $charLeft.$key.$charRight;
                $values[] = ":{$key}";
            }
            $keyStr    = implode(',', $keys);
            $valueStr  = implode(',', $values);
            $operator  = $this->_getInsertOperatorByOption($option);
            $updateStr = '';
            if ($option == 'update') {
                foreach ($data as $key => $value) {
                    $updates[] = "{$charLeft}{$key}{$charRight}=:{$key}";
                }
                $updateStr = implode(',', $updates);
                $updateStr = " ON DUPLICATE KEY UPDATE {$updateStr}";
            }
            $sql = "{$operator} INTO {$table}({$keyStr}) VALUES({$valueStr}){$updateStr}";
            return $this->prepareExecute($sql, $data, 'master');
        }
    }

    /**
     * 批量写入数据库，注意每个数据数组的大小应该一致.
     *
     * @param  string $table    表名.
     * @param  array  $list     数据数组.
     * @param  int    $perCount 每批写入的数据数量(防止构建的SQL过长，必须分批).
     * @param  mixed  $option   选项(replace:同记录替换, update:同记录更新, ignore:同记录忽略, 默认直接写入)
     * @return bool
     */
    public function batchInsert($table, array $list, $perCount = 10, $option = '')
    {
        if (!empty($list)) {
            // 查询字段名称
            list($_, $firstItem) = each($list);
            $fieldLength = count($firstItem);
            // 数据校验
            foreach ($list as $item) {
                // 每个数组的长度必须一致
                if ($fieldLength != count($item)) {
                    return false;
                }
            }
            $keys      = array_keys($firstItem);
            $filedStr  = '';
            $valueStr  = '';
            $updateStr = '';
            $charLeft  = $this->_getReserveChar();
            $charRight = $this->_getReserveChar(false);
            foreach ($keys as $key){
                $filedStr .= "{$charLeft}{$key}{$charRight},";
                $valueStr .= '?,';
            }
            // insert update 操作
            if ($option == 'update') {
                foreach ($keys as $key){
                    $updates[] = "{$charLeft}{$key}{$charRight}=VALUES({$key})";
                }
                $updateStr = implode(',', $updates);
                $updateStr = " ON DUPLICATE KEY UPDATE {$updateStr}";
            }
            $filedStr  = rtrim($filedStr, ',');
            $valueStr  = rtrim($valueStr, ',');
            // 组织批量写入/更新SQL语句
            $index       = 0;
            $insertStr   = '';
            $insertArray = array();
            $operator    = $this->_getInsertOperatorByOption($option);
            foreach ($list as $item) {
                // 绑定预处理数据数组
                foreach ($item as $k => $v){
                    $insertArray[] = $v;
                }
                $insertStr .= "({$valueStr}),";
                if ((++$index) % $perCount == 0) {
                    $insertStr = rtrim($insertStr, ',');
                    $insertSql = "{$operator} INTO {$table}({$filedStr}) VALUES{$insertStr}{$updateStr}";
                    $this->prepareExecute($insertSql, $insertArray, 'master');
                    $insertStr   = '';
                    $insertArray = array();
                }
            }
            // 插入最后剩余不满$perCount的数据
            if (!empty($insertStr)) {
                $insertStr = rtrim($insertStr, ',');
                $insertSql = "{$operator} INTO {$table}({$filedStr}) VALUES{$insertStr}{$updateStr}";
                $this->prepareExecute($insertSql, $insertArray, 'master');
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 保存数据操作(如果数据中存在主键或者唯一索引，那么执行更新，否则执行插入).
     *
     * @param  string $table  表名称.
     * @param  array  $data   关联数组.
     *
     * @return PDOStatement|false
     */
    public function save($table, array $data)
    {
        return $this->insert($table, $data, 'update');
    }

    /**
     * 批量保存数据进入数据库(如果数据中存在主键或者唯一索引，那么执行更新，否则执行插入)，注意每个数据数组的大小应该一致.
     *
     * @param  string $table    表名.
     * @param  array  $list     数据数组.
     * @param  int    $perCount 每批写入的数据数量(防止构建的SQL过长，必须分批).
     *
     * @return bool
     */
    public function batchSave($table, array $list, $perCount = 10)
    {
        return $this->batchInsert($table, $list, $perCount, 'update');
    }

    /**
     * 根据选项参数获取INSERT操作的操作词.
     *
     * @param string $option Option.
     *
     * @return string
     */
    protected function _getInsertOperatorByOption($option)
    {
        switch ($option) {
            case 'replace':
                $operator = 'REPLACE';
                break;

            case 'ignore':
                $operator = 'INSERT IGNORE';
                break;

            default:
                $operator = 'INSERT';
                break;
        }
        return $operator;
    }

    /**
     * 修改操作UPDATE
     *
     * @param string $table      表名称.
     * @param mixed  $data       关联数组/字符串.
     * @param mixed  $conditions SQL的操作条件.
     *
     * @return PDOStatement|false
     */
    public function update($table, $data, $conditions)
    {
        if (!empty($data)) {
            if (is_array($data)) {
                // 全部转成以position的预处理，以方便处理
                $index     = 0;
                $charLeft  = $this->_getReserveChar();
                $charRight = $this->_getReserveChar(false);
                foreach ($data as $key => $value) {
                    $sets[]         = "{$charLeft}{$key}{$charRight}=?";
                    $data[$index++] = $value;
                    unset($data[$key]);
                }
                $setStr        = implode(',', $sets);
                $newConditions = $this->_formatCondition($conditions, $index);
                $conditionStr  = empty($newConditions[0]) ? '' : "WHERE {$newConditions[0]}";
                $data          = array_merge($data, $newConditions[1]);
            } else {
                $setStr        = $data;
                $newConditions = $this->_formatCondition($conditions, 0);
                $conditionStr  = empty($newConditions[0]) ? '' : "WHERE {$newConditions[0]}";
                $data          = $newConditions[1];
            }
            $sql  = "UPDATE {$table} SET {$setStr} {$conditionStr}";
            return $this->prepareExecute($sql, $data, 'master');
        }
        return false;
    }

    /**
     * 删除操作
     *
     * @param string $table      表名称
     * @param mixed  $conditions SQL的操作条件
     *
     * @return PDOStatement|false
     */
    public function delete($table, $conditions)
    {
        $newConditions = $this->_formatCondition($conditions);
        $conditionStr  = empty($newConditions[0]) ? '' : "WHERE {$newConditions[0]}";
        $sql           = "DELETE FROM {$table} {$conditionStr}";
        return $this->prepareExecute($sql, $newConditions[1], 'master');
    }

    /**
     * 错误产生时停止脚本并显示错误。
     *
     */
    protected function _halt($error = '')
    {
        if (empty($error) && !empty($this->_link)) {
            $array = $this->_link->errorInfo();
            $error = $array[2];
        }
        if (!empty($error)) {
            if ($this->_halt) {
                $errorContent  = "<h3>DB Error</h3>\n";
                $errorContent .= "<p>\n<b>Error:</b>\n{$error}\n</p>\n";
                if (!empty($this->_sqls)) {
                    $info = print_r($this->_sqls[count($this->_sqls) - 1], true);
                    $errorContent .= "<p>\n<b>Query:</b>\n{$info}\n</p>\n";
                }
                $this->close();
                if ((php_sapi_name() == 'cli')) {
                    $errorContent = strip_tags($errorContent);
                }
                echo $errorContent;
                exit();
            } else {
                $this->_error = $error;
            }
        }
    }


    /**
     * ==================================================================================================
     * 以下是MySQL定制化的操作方法，只在MySQL数据库上有效
     * ==================================================================================================
     */

    /**
     * 获取表字段列表，构成数组返回.
     *
     * @param string    $table             表名.
     * @param mixed     $filtFields        需要过滤的字段列表(可以是字符串用逗号分隔，也可以是数组).
     * @param bool|true $withoutPrimaryKey 是否去掉主键字段.
     *
     * @return array
     */
    public function mysqlGetFieldArray($table, $filtFields = array(), $withoutPrimaryKey = true)
    {

        if (is_array($filtFields)) {
            $filtFieldArray = $filtFields;
        } else {
            $filtFieldArray = explode(',', trim($filtFields));
        }
        $fileds = array();
        $result = $this->query("SHOW COLUMNS FROM `{$table}`");
        while ($row = $this->fetchAssoc($result)) {
            // 是否去掉主键字段
            if ($withoutPrimaryKey && $row['Key'] == 'PRI') {
                continue;
            }
            // 是否需要过滤该字段
            if (in_array($row['Field'], $filtFieldArray)) {
                continue;
            }
            $fileds[] = $row['Field'];
        }
        return $fileds;
    }

    /**
     * 获取过滤的表字段，构成字符串返回.
     *
     * @param string    $table             表名.
     * @param mixed     $filtFields        需要过滤的字段列表(可以是字符串-用逗号分隔，也可以是数组).
     * @param bool|true $withoutPrimaryKey 是否去掉主键字段.
     *
     * @return string
     */
    public function mysqlGetFieldStr($table, $filtFields = array(), $withoutPrimaryKey = true)
    {
        $fieldStr   = '';
        $fieldArray = $this->mysqlGetFieldArray($table, $filtFields, $withoutPrimaryKey);
        if (!empty($fieldArray)) {
            $fieldStr = "'".implode("','", $fieldArray)."'";
        }
        return $fieldStr;
    }

    /**
     * 根据表字段过滤数组，数组键名不是表字段时，直接过滤掉。
     *
     * @param  string $table 表名
     * @param  array  $data  数据数组.
     * @return array
     */
    public function mysqlFiltDataArray($table, array $data)
    {
        $fields = $this->mysqlGetFieldArray($table);
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
     * @param string $table  表名称.
     * @param array  $data   关联数组.
     * @param string $option 插入选项.
     *
     * @return PDOStatement|false
     */
    public function mysqlFiltInsert($table, array $data, $option = '')
    {
        return $this->insert($table, $this->mysqlFiltDataArray($table, $data), $option);
    }

    /**
     * MySQL的过滤更新，内部调用mysqlFiltDataArray进行数组过滤.
     *
     * @param string $table      表名称.
     * @param array  $data       关联数组.
     * @param mixed  $conditions SQL的操作条件.
     *
     * @return PDOStatement|false
     */
    public function mysqlFiltUpdate($table, array $data, $conditions)
    {
        return $this->update($table, $this->mysqlFiltDataArray($table, $data), $conditions);
    }

    /**
     * MySQL的过滤保存，内部调用mysqlFiltDataArray进行数组过滤，如果存在主键或者唯一索引，那么执行更新，否则执行写入.
     *
     * @param string $table      表名称.
     * @param array  $data       关联数组.
     *
     * @return PDOStatement|false
     */
    public function mysqlFiltSave($table, $data)
    {
        return $this->mysqlFiltInsert($table, $data, 'update');
    }
}