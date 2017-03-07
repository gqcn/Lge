<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}

/**
 * 注销管理.
 *
 */
class Controller_Logout extends BaseControllerAdminAuth
{

    /**
     * 执行注销
     */
    public function index()
    {
        $this->_session['user'] = array();
        Instance::cookie()->drop('user_info');
        Lib_Redirecter::redirectExit('/login/index');
    }
}
