<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 工具类，封装各种实用但是无法分类的方法.
 *
 * @author John
 */
class Lib_Utility
{
    /**
     * Linux下获得关键字的进程数量.
     *
     * @param string $key 关键字.
     *
     * @return integer
     */
    public static function getProcessCount($key)
    {
        $count  = 0;
        $result = shell_exec("ps aux | grep '{$key}'");
        $array  = explode("\n", trim($result));
        foreach ($array as $k => $v) {
            if (!empty($v) && stripos($v, 'grep') === false) {
                ++$count;
            }
        }
        return $count;
    }
}