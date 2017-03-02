<?php
if (!defined('PhpMe')) {
    exit('Include Permission Denied!');
}

/**
 * Ip处理类.
 *
 * @author Claymore 2013-03-06
 */
class Lib_IpHandler
{
    /**
     * 获取客户端的IP
     *
     * @return string
     */
    static public function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * 获取服务器IP,如果通过CLI方式执行，返回的IP为空。
     *
     * @return string
     */
    public static function getServerIp()
    {
        $serverIp = '';
        if (isset($_SERVER)) {
            if (!empty($_SERVER['SERVER_ADDR'])) {
                $serverIp = $_SERVER['SERVER_ADDR'];
            } elseif (!empty($_SERVER['LOCAL_ADDR'])) {
                $serverIp = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $serverIp = getenv('SERVER_ADDR');
        }

        if (empty($serverIp) && php_sapi_name() == 'cli') {
            $cmd      = "ifconfig | grep Bcast | awk -F: '{print $2}'|awk -F \" \" '{print $1}'";
            $serverIp = @shell_exec($cmd);
            $serverIp = trim($serverIp);
            if (empty($serverIp)) {
                @exec($cmd, $result);
                if (!empty($result)) {
                    $serverIp = implode("\n", $result);
                }
            }
        }

        return $serverIp;
    }
}