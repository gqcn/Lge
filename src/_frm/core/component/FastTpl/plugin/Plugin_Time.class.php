<?php
/**
 * 时间插件.
 * 
 * @author john
 * @version v0.1 2014-03-06
 */
namespace Lge;

class Plugin_Time
{
    /**
     * 格式化显示时间戳.
     * 
     * @param integer $timestamp 时间戳.
     * @param string  $format    格式化字符串，和date一样.
     * 
     * @return string
     */
    public function format($timestamp, $format = 'Y-m-d H:i:s')
    {
        return date($format, $timestamp);
    }
    
    /**
     * 获取当前时间戳.
     * 
     * @return integer
     */
    public function time()
    {
        return time();
    }
}
