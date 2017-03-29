<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 页面跳转类.
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
            $url = Lib_Url::getReferer();
        }
        header("location:{$url}");
    }
}