<?php
if(!defined('PhpMe')){
	exit('Include Permission Denied!');
}

/**
 * 微信公众号 - 第三方平台
 */
class Controller_WechatThirdparty extends BaseControllerAdminAuth
{
    /**
     * 微信公众平台展示
     */
    public function wechat()
    {
        $this->assigns(array(
            'url'     => 'https://mp.weixin.qq.com',
            'mainTpl' => 'wechat/thirdparty/index',
        ));
        $this->display();
    }

    /**
     * 微信商家平台展示
     */
    public function wechatPay()
    {
        $this->assigns(array(
            'url'     => 'https://pay.weixin.qq.com',
            'mainTpl' => 'wechat/thirdparty/index',
        ));
        $this->display();
    }

    /**
     * 微信测试平台展示
     */
    public function wechatTest()
    {
        $this->assigns(array(
            'url'     => 'http://mp.weixin.qq.com/debug/cgi-bin/sandbox?t=sandbox/login',
            'mainTpl' => 'wechat/thirdparty/index',
        ));
        $this->display();
    }

}