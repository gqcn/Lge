<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}
/**
 * 类别管理模型。
 *
 */
class Model_Category extends BaseModelTable
{
    public $table = 'category';

    /**
     * 所有分类.
     *
     * @var array
     */
    public $types = array(
        /*
        101 => '文章',
        102 => '图片',
        103 => '菜单',
        104 => '连接',
        105 => '碎片',
        106 => '笔记',
        */
    );

    /**
     * 获得实例.
     *
     * @return Model_Category
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * 根据类型名称获取类型ID。
     * @param $typeName
     * @return int
     */
    public function getTypeIdByTypeName($typeName)
    {
        static $typeNameIdMap = array();
        if (empty($typeNameIdMap)) {
            foreach ($this->types as $k => $v) {
                $typeNameIdMap[$v] = $k;
            }
        }
        $typeName = trim($typeName);
        return isset($typeNameIdMap[$typeName]) ? $typeNameIdMap[$typeName] : 0;
    }

    /**
     * 根据分类名称以及父类名称获取分类ID。
     * @param $catName
     * @param $pCatName
     * @param $type
     * @return array
     */
    public function getCatByCatNameAndPcatName($catName, $pCatName, $type)
    {

        if (empty($catName)) {
            $tables    = 'category a';
            $fields    = 'a.*';
            $condition = array('a.type=? and a.cat_name=?', $type, $pCatName);
        } else {
            $tables    = array(
                'category a',
                'left join category b ON(b.cat_id = a.pcat_id)',
            );
            $fields    = 'a.*';
            $condition = array('a.type=? and a.cat_name=? and b.cat_name=?', $type, $catName, $pCatName);
        }
        $result = Instance::table($tables)->getOne($fields, $condition);
        return $result;
    }

    /**
     * 获得分类列表.
     *
     * @param integer $type 分类类型.
     *
     * @return array
     */
    public function getCatArray($type = 0)
    {
        $condition = array();
        if (!empty($type)) {
            $condition[] = array('`type`=?', $type);
        }
        $catArray  = $this->getAll("*", $condition, null, "`order` ASC,`cat_id` ASC", 0, 0, 'cat_id');
        return $catArray;
    }

    /**
     * 获得树形列表.
     * 
     * @param integer $type 分类类型.
     * 
     * @return array
     */
    public function getCatTreeList($type = 0)
    {
        $catArray = $this->getCatArray($type);
        return $this->getCatTreeListByCatArray($catArray);
    }
    
    /**
     * 获得树形列表.
     * 
	 * @param array $catArray 分类数组.
     * @param integer $pCatId 父级ID.
     * 
     * @return array
     */
    public function getCatTreeListByCatArray(&$catArray, $pCatId = 0)
    {
        $tree = new Lib_Tree($catArray, array(
            'id'        => 'cat_id', 
            'parent_id' => 'pcat_id', 
            'name'      => 'cat_name'
        ));
        return $tree->get_tree($pCatId, '$spacer $cat_name');
    }
    
	/**
	 * 获得特定栏目的下一级子节点
	 *
	 * @param  array   $catArray 分类数组.
	 * @param  integer $pCatId   父级ID.
	 * @return array
	 */
	public function getSubList(&$catArray, $pCatId)
	{
	    $list = array();
	    foreach ($catArray as $v){
	        if($v['pcat_id'] != $pCatId){
	            continue;
	        }
	        $list[] = $v;
	    }
	    return $list;
	}
	
	/**
	 * 获取指定顶级栏目所有子栏目的ID，合成字符串返回
	 *
	 * @param  $catArray $catArray 分类数组.
	 * @param  integer   $pCatId   栏目ID 
	 * @param  string    $children (不需要传参)
	 * @return string
	 */
	public function getChildren(&$catArray, $pCatId, $children = '')
	{
		$list = $this->getSubList($catArray, $pCatId);
		foreach ($list as $row){
            if (empty($children)) {
                $children = $row['cat_id'];
            } else {
                $children .= ','.$row['cat_id'];
            }
			$children  = $this->getChildren($catArray, $row['cat_id'], $children);
		}
		return $children;
	}
}