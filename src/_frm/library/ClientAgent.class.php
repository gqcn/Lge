<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 客户端判断类.
 *
 * @author John
 */
class Lib_ClientAgent
{
    /**
     * 获取客户端类型.
     *
     * @return string
     */
    public static function getType()
    {
        /**
         * 注意判断的顺序很重要.
         */
        $agent = $_SERVER["HTTP_USER_AGENT"];
        if(strpos($agent, "MicroMessenger")) {
            $type = 'weixin';
        } else if(strpos($agent, "MSIE")) {
            $type = 'ie';
        } else if(strpos($agent, "Firefox")) {
            $type = 'firefox';
        } else if(strpos($agent, "Chrome")) {
            $type = 'chrome';
        } else if(strpos($agent, "Safari")) {
            $type = 'safari';
        } else if(strpos($agent, "Opera")) {
            $type = 'opera';
        } else {
            $type = $agent;
        }
        return $type;
    }
}