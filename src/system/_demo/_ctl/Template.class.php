<?php
if (!defined('PhpMe')) {
	exit('Include Permission Denied!');
}

class Controller_Template extends Controller_Base
{
    
    /**
     * 默认入口函数.
     *
     * @return void
     */
    public function index()
    {
        $users   = Model_Demo_User::instance()->getAll();
        $company = Model_Demo_Company::instance()->getAll();
        $this->assigns(array(
            'list' => $users,
        ));
        $this->display('index');
    }
}