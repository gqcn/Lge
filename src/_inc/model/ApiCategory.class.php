<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}
/**
 * 云服务 - 云服务分类管理模型。
 *
 */
class Model_ApiCategory extends BaseModelTable
{
    public $table = 'api_app_cat';

    /**
     * 获得实例.
     *
     * @return Model_ApiCategory
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * 获得分类列表.
     *
     * @param integer $appid 应用ID.
     *
     * @return array
     */
    public function getCatArray($appid)
    {
        $condition = array('appid' => $appid);
        $catArray  = $this->getAll("*", $condition, null, "`order` ASC,`id` ASC", 0, 0, 'id');
        return $catArray;
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
            if($v['pid'] != $pCatId){
                continue;
            }
            $list[] = $v;
        }
        return $list;
    }

    /**
     * 获取当前我可管理的应用列表.
     *
     * @return array
     */
    public function getCatTree($appid)
    {
        $catArray  = $this->getCatArray($appid);
        $tree      = new Lib_Tree($catArray, array(
            'id'        => 'id',
            'parent_id' => 'pid',
            'name'      => 'name'
        ));
        return $tree->get_tree(0, '$spacer $name');
    }
}