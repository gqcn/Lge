<?php
if(!defined('PhpMe')){
	exit('Include Permission Denied!');
}

/**
 * 后台首页
 */
class Controller_Default extends BaseControllerAdminAuth
{
    /**
     * 首页展示
     */
    public function index()
    {
    	$this->assigns(array(
        	'mainTpl' => 'default/index'
        ));
        $this->display('index');
    }
}
