<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}
/**
 * 云服务 - 应用管理
 */
class Controller_ApiApp extends BaseControllerAdminAuth
{

    /**
     * 应用列表.
     */
    public function index()
    {
        $this->assigns(array(
            'list'     => Model_ApiApp::instance()->getMyApps(),
            'mainTpl' => 'api/app/index',
        ));
        $this->display();
    }

    /**
     * 列表排序.
     *
     * @return void
     */
    public function sort()
    {
        $ids     = Lib_Request::getPost('ids');
        $idArray = explode(',', $ids);
        foreach ($idArray as $k => $id) {
            Instance::table('api_app')->update(
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
        $id = Lib_Request::getGet('id');
        if (Lib_Request::isRequestMethodPost()) {
            $this->_handleSave();
        } else {
            $this->setCurrentMenu('api-app', 'index');
            $data = array(
                'thumb' => '/static/resource/images/default.jpg',
                'order' => 99,
            );
            if (!empty($id)) {
                $result = Instance::table('api_app')->getOne("*", array('id' => $id));
                if (!empty($result)) {
                    $data = array_merge($data, $result);
                }
            }
            $this->assigns(array(
                'data'     => $data,
                'types'    => $this->types,
                'mainTpl'  => 'api/app/item',
            ));
            $this->display();
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
        $result = Instance::table('api_app')->mysqlFiltSave($data);
        if (empty($result)) {
            $this->addMessage('信息保存失败', 'error');
        } else {
            $this->addMessage('信息保存成功', 'success');
        }
        Lib_Redirecter::redirectExit();
    }

    /**
     * 删除回复.
     */
    public function delete()
    {
        $id = Lib_Request::getGet('id', 0);
        if (!empty($id)) {
            Instance::table('api_app')->delete(array('id' => $id));
            $this->addMessage('删除成功', 'success');
        }
        Lib_Redirecter::redirectExit();
    }
}