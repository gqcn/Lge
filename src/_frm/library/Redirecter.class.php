<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Redirect the page jumping.
 * 
 * @author john
 */

class Lib_Redirecter
{
    /**
     * Redirect to url and exit the process execution.
     * 
     * @param string $url Url.
     * 
     * @return void
     */
    static public function redirectExit($url = null)
    {
        self::redirect($url);
        exit();
    }
    
    /**
     * Redirect to url.
     * 
     * @param string $url Url.
     * 
     * @return void
     */
    static public function redirect($url = null)
    {
        if (empty($url)) {
            $url = self::getReferer();
        }
        header("location:{$url}");
    }

    /**
     * 获取子级域名.
     *
     * @param integer $level    几级子域名(子级域名默认从2开始).
     * @param string  $httpHost 给定需要解析的域名域名.

     * @return string
     */
    public static function getSubdomain($level = 2, $httpHost = '')
    {
        $subDomain = '';
        if (empty($httpHost)) {
            $httpHost = empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST'];
        }
        if (!empty($httpHost)) {
            $array      = explode('.', $httpHost);
            $levelCount = count($array);
            if ($levelCount == 2) {
                $subDomain = '';
            } else {
                // 判断域名中是否带有地区标识，例如： xxx.cn.com, xxx.hk.com
                if (isset($array[$levelCount - 2]) && strlen($array[$levelCount - 2]) == 2) {
                    $index = $levelCount - 2 - $level;
                } else {
                    $index = $levelCount - 1 - $level;
                }
                if (isset($array[$index])) {
                    $subDomain = $array[$index];
                }
            }
        }
        return $subDomain;
    }

    /**
     * 获取主域名(xxx.xxx，或者带有地区标识的 xxx.xx.xxx).
     *
     * @return string
     */
    public static function getMaindomain()
    {
        $mainDomain = '';
        $httpHost   = empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST'];
        if (!empty($httpHost)) {
            $array  = explode('.', $httpHost);
            $length = count($array);
            if ($length == 1) {
                $mainDomain = $httpHost;
            } else {
                // 判断域名中是否带有地区标识，例如： xxx.cn.com, xxx.hk.com
                if (isset($array[$length - 2]) && strlen($array[$length - 2]) == 2) {
                    $mainDomain = implode('.', array($array[$length - 3], $array[$length - 2], $array[$length - 1]));
                } else {
                    $mainDomain = implode('.', array($array[$length - 2], $array[$length - 1]));
                }
            }
        }
        return $mainDomain;
    }

    /**
     * 获取主域名(xxx.xxx 或者 www.xxx.com).
     *
     * @return string
     */
    public static function getDefaultdomain()
    {
        return self::getMaindomain();
    }

    /**
     * 获取当前处理请求的URL(不包含URI)。
     * @param string $protocal
     * @return string
     */
    static public function getCurrentUrlWithoutUri($protocal = 'http')
    {
        return self::getCurrentUrl($protocal, false);
    }

    /**
     * 获得当前处理请求的URL(可以选择是否不获取URI)
     * @param string  $protocal
     * @param boolean $withUri
     * @return string
     */
    static public function getCurrentUrl($protocal = 'http', $withUri = true)
    {
        $url = '';
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
            if ($withUri) {
                $uri  = $_SERVER['REQUEST_URI'];
                $url  = "{$protocal}://{$host}{$uri}";
            } else {
                $url  = "{$protocal}://{$host}/";
            }
        }
        return $url;
    }

    /**
     * 获取请求来源URL。
     * @return string
     */
    static public function getReferer()
    {
        return empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
    }
}