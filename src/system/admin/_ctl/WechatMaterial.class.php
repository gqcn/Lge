<?php
if(!defined('PhpMe')){
	exit('Include Permission Denied!');
}

/**
 * 微信公众号 - 素材管理
 */
class Controller_WechatMaterial extends BaseControllerAdminAuth
{
    /**
     * 素材列表
     */
    public function index()
    {
        $this->assigns(array(
            'mainTpl' => 'wechat/material/index',
        ));
        $this->display();
    }
}