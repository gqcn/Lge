<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}
/**
 * 云服务 - 分类管理
 */
class Controller_ApiCategory extends BaseControllerAdminAuth
{
    public $bindTableName  = 'api_app_cat';

    /**
     * 分类列表.
     */
    public function index()
    {
        $appid     = Lib_Request::get('appid');
        $appid     = intval($appid);
        $tables    = array(
            'api_app_cat aac',
            'left join api_app_api aaa on(aaa.cat_id=aac.id)',
        );
        $fields    = 'aac.*,count(aaa.id) as api_count';
        $condition = array('aac.appid' => $appid, 'aac.uid' => $this->_session['user']['uid']);
        $catArray  = Instance::table($tables)->getAll($fields, $condition, "aac.id", "`order` ASC,`id` ASC", 0, 0, 'id');
        $tree      = new Lib_Tree($catArray, array(
            'id'        => 'id',
            'parent_id' => 'pid',
            'name'      => 'name'
        ));
        $catList = $tree->get_tree(0, '$spacer $name');
        $this->assigns(array(
            'catList' => $catList,
            'mainTpl' => 'api/category/embed_index',
        ));
        $this->display();
    }

    /**
     * 列表排序.
     *
     * @return void
     */
    public function ajaxSort()
    {
        $ids  = Lib_Request::getPost('ids');
        $pids = Lib_Request::getPost('pids');
        foreach ($ids as $k => $id) {
            if (empty($id)) {
                continue;
            }
            $k  = intval($k);
            $id = intval($id);
            Instance::table($this->bindTableName)->update(
                array('order' => $k + 1),
                array(
                    'id'  => $id,
                    'uid' => $this->_session['user']['uid']
                )
            );
        }
        Lib_Response::json(1);
    }

    /**
     * 添加/修改.
     */
    public function item()
    {
        if (Lib_Request::isRequestMethodPost()) {
            $this->_handleSave();
        } else {

        }
    }

    private function _handleSave()
    {
        $id     = Lib_Request::getPost('id');
        $data   = Lib_Request::getPostArray(array(
            'uid'         => $this->_session['user']['uid'],
            'update_time' => time(),
        ), true);
        if (empty($id)) {
            $data['create_time'] = time();
        }
        $result = Instance::table($this->bindTableName)->mysqlFiltSave($data);
        if (empty($result)) {
            Lib_Response::json(0, '', '分类保存失败');
        } else {
            Lib_Response::json(1, '', '分类保存成功');
        }
    }

    /**
     * 异步删除接口.
     */
    public function ajaxDelete()
    {
        $id = Lib_Request::getGet('id', 0);
        if (!empty($id)) {
            Instance::table($this->bindTableName)->delete(array('id' => $id));
        }
        Lib_Response::json(1, '', '分类信息删除成功');
    }
}