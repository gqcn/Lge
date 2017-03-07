<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

class Controller_Base extends BaseController
{
    public  $startSession = false;    // 是否开启session
    public  $sessionID    = null;    // 设置session id

    /**
     * 构造函数.
     */
    public function __construct()
    {
        parent::__construct();

//        /**
//         * 参数完整性判断
//         */
//        $appid     = Lib_Request::getRequest('appid');
//        $nonce     = Lib_Request::getRequest('nonce');
//        $timestamp = Lib_Request::getRequest('timestamp');
//        $signature = Lib_Request::getRequest('signature');
//        if (empty($appid) || empty($nonce) || empty($timestamp) || empty($signature)) {
//            $this->_response(false, '', 'incomplete params');
//        }
//        /**
//         * 接口数据校验
//         */
//        // 查询对应的appsecret
//        $appidConfig = Config::get('appid');
//        if (!isset($appidConfig[$appid])) {
//            $this->_response(false, '', "invalid appid");
//        }
//        // 执行签名校验
//        $appsecret = $appidConfig[$appid];
//        if (!$this->_checkSignature($appsecret)) {
//            $this->_response(false, '', "invalid signature");
//        }
    }

    /**
     * 获取密钥.
     *
     * @return string
     */
    private function _getSecret()
    {
        $appid       = Lib_Request::getRequest('appid');
        $appsecret   = null;
        $appidConfig = Config::get('appid');
        if (isset($appidConfig[$appid])) {
            $appsecret = $appidConfig[$appid];
        }
        return $appsecret;
    }

    /**
     * 检查签名.
     *
     * @param string $appsecret AppSecret.
     *
     * @return bool
     */
    private function _checkSignature($appsecret)
    {
        $data = Lib_Request::getRequestArray(array(
            'signature' => '',
            'timestamp' => '',
            'nonce'     => '',
        ));
        $signature  = $data['signature'];
        $timestamp  = $data['timestamp'];
        $nonce      = $data['nonce'];
        $signature2 = $this->_makeSignature($appsecret, $timestamp, $nonce);
        // var_dump($signature2);
        if ($signature2 == $signature) {
            $this->log("{$signature}|{$timestamp}|{$nonce}", 'signature_success');
            return true;
        } else {
            $this->log("{$signature}|{$timestamp}|{$nonce}", 'signature_failed');
            return false;
        }
    }

    /**
     * 生成签名.
     *
     * @param string $appsecret 秘钥.
     * @param string $timestamp 时间戳.
     * @param string $nonce     请求唯一字段.
     *
     * @return string
     */
    private function _makeSignature($appsecret, $timestamp, $nonce)
    {
        $tmpArr    = array($appsecret, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr    = implode($tmpArr);
        $tmpStr    = sha1($tmpStr);
        return $tmpStr;
    }

    /**
     * 生成32位的唯一字符串.
     *
     * @return string
     */
    private function _makeNonceStr()
    {
        return md5(microtime(true).rand(0, 9999));
    }

    /**
     * 数据返回.
     *
     * @param boolean $result  成功或者失败(0:失败, 1:成功)
     * @param array   $data    返回数据(成功时有效).
     * @param string  $message 返回消息.
     */
    protected function _response($result, $data = array(), $message = '')
    {
        $format = Lib_Request::getRequest('format');
        if ($format != 'xml') {
            $format = 'json';
        }
        $timestamp = time();
        $nonce     = $this->_makeNonceStr();
        $appsecret = $this->_getSecret();
        $signature = $this->_makeSignature($appsecret, $timestamp, $nonce);
        Lib_Response::$format(intval($result), $data, $message, array(
            'timestamp' => $timestamp,
            'nonce'     => $nonce,
            'signature' => $signature,
        ));
    }




    /**
     * 按照标准的日志格式写入一条日志.
     *
     * @param string  $message  日志信息.
     * @param string  $category 日志目录(分类).
     * @param integer $level    日志级别.
     * @param boolean $cache    是否缓存执行.
     *
     * @return void
     */
    public function log($message, $category, $level = Logger::INFO, $cache = false) {
        Logger::logToFile($message, "api/{$category}", $level, $cache);
    }

}