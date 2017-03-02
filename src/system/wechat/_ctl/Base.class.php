<?php
if(!defined('PhpMe')){
    exit('Include Permission Denied!');
}

class Controller_Base extends BaseController
{
    public  $startSession = true;    // 是否开启session
    public  $sessionID    = null;    // 设置session id
    const   APPID_OPENID_REDIS_PREFIX_KEY = 'appid_openid_';

    /**
     * 构造函数.
     */
    public function __construct()
    {
        parent::__construct();
        $openid = '';


        /**
         * @todo 这个是测试用户数据，测试环境使用
         */
        if (DEPLOYMENT != 'prod') {
            unset($this->_session['user']);
            $this->_session['openid'] = 'oSQo3uFnQAdiBca972lxvnG4AqPo';
            $openid = $this->_getOpenidByAuth();
        }



        if (Lib_ClientAgent::getType() == 'weixin') {
            // 获取用户信息，初始化用户session
            $openid = $this->_getOpenidByAuth();
        } else {
            // 如果不是微信终端打开页面，并且没有任何用户信息，那么提示错误并停止执行
            if (empty($this->_session['user'])) {
                echo "请使用微信终端浏览该页面";
                exit();
            }
        }

        /**
         * 渠道来源商家
         */
        $fromShopId = Lib_Request::getGet('from_shop_id');
        if (!empty($fromShopId) && !empty($this->_session['user'])) {
            $uid = $this->_session['user']['uid'];
            if (!empty($uid)) {
                Module_Shop::instance()->updateShopUser($fromShopId, $uid, $openid);
            }
        }
    }
    /**
     * 获得分页的start
     *
     * @param  int    $perPage
     * @param  string $pageName
     * @return int
     */

    public function getStart($perPage, $pageName = 'page')
    {
        $curPage = isset($this->_get[$pageName]) ? intval($this->_get[$pageName]) : 0;
        if ($curPage > 1) {
            $start = ($curPage - 1)*$perPage;
        } else {
            $start = 0;
        }
        return $start;
    }

    /**
     * 获得OpenID，如果没有则让用户授权.
     *
     * @return string
     */
    protected function _getOpenidByAuth($localUrl = '')
    {
        if (empty($localUrl)) {
            $localUrl = Lib_Redirecter::getCurrentUrl();
        }

        $config = Config::get();
        $openid = empty($this->_session['openid']) ? '' : $this->_session['openid'];
        if (empty($openid)) {
            $openid = Instance::cookie()->get('openid');
        }

        /**
         * 检查openid是否合法(是否当前公众号所属)
         *
         * @todo 只有在多个公众号cookie共享的情况下需要判别，如果确定不会共享，那么可以不用
         */
        if (!empty($openid)) {
            $result = Instance::table('wechat_user')->getCount(array('openid=?', $openid));
            if (empty($result)) {
                $openid                   = '';
                $this->_session['openid'] = '';
            }
        }

        // 根据openid初始化session
        if (!empty($openid) && empty($this->_session['user'])) {
            Module_User::Instance()->initSessionByOpenid($openid);
        }
        // 重新授权获取微信用户信息
        if ((empty($openid) || empty($this->_session['user'])) && !empty($localUrl)) {
            $localUrl    = urlencode($localUrl);
            $callbackUrl = $config['Site']['url']."WebAuthCallback?url={$localUrl}";
            Module_WeChat_Api::Instance()->redirectToGetUserInfoByWebAuth($callbackUrl);
        }

        return $openid;
    }

    /**
     * 封装：MVC显示页面。
     *
     */
    public function display($tpl = 'index')
    {
        $config = Config::get();
        $this->assigns(array(
            'config'  => Config::get(),
            'session' => $this->_session,
            'system'  => '/system/default/',
            'version' => $config['StaticVersion'],
        ));
        parent::display($tpl);
    }

    /**
     * 按照标准的日志格式写入一条日志.
     *
     * @param string  $message  日志信息.
     * @param string  $category 日志目录(分类).
     * @param integer $level    日志级别(info:1, warning:2, error:3).
     *
     * @return void
     */
    public function log($message, $category = 'default', $level = Logger::INFO) {
        Logger::log($message, $category, $level);
    }

}