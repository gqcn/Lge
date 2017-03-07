<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}
/**
 * 云服务 - 接口管理
 */
class Controller_Api extends Controller_Base
{
    public $bindTableName = 'api_app_api';
    public $actMap        = array(
        'list' => 'apiList'
    );

    /**
     * 应用信息
     * @var array
     */
    public $app = array();

    /**
     * 初始化操作
     */
    public function __init()
    {
        $appid     = Lib_Request::get('appid');
        $appid     = intval($appid);
        $this->app = Instance::table('api_app')->getOne('*', array('id' => $appid));
        if (empty($this->app)) {
            exit('应用不存在！');
        }

        $menus = array(
            'index' => array(
                'name' => '文档首页',
                'icon' => 'menu-icon fa fa-file',
                'acts' => array('api', 'index'),
                'url'  => "/api/{$appid}",
            ),
            'list' => array(
                'name' => '接口列表',
                'icon' => 'menu-icon fa fa-list',
                'acts' => array('api', 'list'),
                'url'  => "/api/list/{$appid}",
                'subs' => array(),
            ),
        );
        $this->setMenus($menus);
        $this->assigns(array(
            'app' => $this->app,
        ));
    }

    /**
     * 接口文档.
     */
    public function index()
    {
        $this->assigns(array(
            'title'   => '文档首页',
            'mainTpl' => 'api/index',
        ));
        $this->display();
    }

    /**
     * 接口列表
     */
    public function apiList()
    {
        $apiList  = array();
        $catArray = Model_ApiCategory::instance()->getCatArray($this->app['id']);
        $list     = Instance::table($this->bindTableName)->getAll('*', array('appid' => $this->app['id']), null, "`order` ASC,`id` ASC");
        // 分类绑定接口
        foreach ($list as $api) {
            $catid = $api['cat_id'];
            if (isset($catArray[$catid])) {
                if (!isset($catArray[$catid]['api_list'])) {
                    $catArray[$catid]['api_list'] = array();
                }
                $api['content']      = json_decode($api['content'], true);
                $api['address_prod'] = empty($this->app['address_prod']) ? $api['address'] : $this->app['address_prod'].$api['address'];
                $api['address_test'] = empty($this->app['address_test']) ? $api['address'] : $this->app['address_test'].$api['address'];
                $catArray[$catid]['api_list'][] = $api;
            }
        }
        // 分类处理
        foreach ($catArray as $cat) {
            if (!empty($cat['api_list'])) {
                $pid           = $cat['pid'];
                $ancestorNames = array($cat['name']);
                while (isset($catArray[$pid])) {
                    $ancestorNames[] = $catArray[$pid]['name'];
                    $pid             = $catArray[$pid]['pid'];
                }
                $cat['ancestor_names'] = implode(' - ', array_reverse($ancestorNames));
                $apiList[] = $cat;
            }
        }
        $this->assigns(array(
            'title'   => '接口列表',
            'apiList' => $apiList,
            'mainTpl' => 'api/list',
        ));
        $this->display();
    }
}