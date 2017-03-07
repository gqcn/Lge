<?php
namespace Lge;
if(!defined('LGE')){
    exit('Include Permission Denied!');
}

/**
 * 登录管理.
 */
class Controller_Login extends BaseControllerAdmin
{

    /**
     * 展示登录
     */
    public function index()
    {
        if (!$this->_checkBrowserSupported()) {
            $this->display('browser');
        } else {
            if (empty($this->_session['user'])) {
                $this->display('login');
            } else {
                Lib_Redirecter::redirectExit('/default/index');
            }
        }
    }

    /**
     * 执行登录.
     */
    public function doLogin()
    {
        $data = Lib_Request::getArray(array(
            'passport'     => 0,
            'password'     => 0,
            'auto'         => 1,
        ), 'post');

        if (empty($data['passport']) || empty($data['password'])) {
            $this->addMessage('参数不完整！', 'error');
        } else {
            $result = Module_User::instance()->doLogin($data['passport'], $data['password'], $data['auto']);
            if (empty($result)) {
                $this->addMessage('帐号或密码不正确！', 'error');
            }
        }
        $this->redirectExit();
    }

    /**
     * 检测当前用户的浏览器是否满足访问的要求.
     *
     * @return boolean
     */
    private function _checkBrowserSupported()
    {
        $result        = true;
        $httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match("/MSIE\s(\d)/i", $httpUserAgent, $match)) {
            if (isset($match[1]) && $match[1] < 9) {
                $result = false;
            }
        }
        return $result;
    }
}
