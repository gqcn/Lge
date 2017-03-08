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
     * 获得关键字的进程数量.
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

    /**
     * 验证给定的邮箱地址是否是一个规范的邮箱地址.
     *
     * @param string $value 邮箱地址.
     *
     * @return bool
     */
    public static function validateEmail($value)
    {
        $pattern1 = "/^([0-9A-Za-z\\-\\_\\.]+)@([0-9A-Za-z\\-\\_\\.]+)$/i";
        $pattern2 = "/@([0-9A-Za-z\\-\\_]+)\.([0-9A-Za-z\\-\\_]+)/i";
        $result1 = preg_match($pattern1, $value);
        $result2 = preg_match($pattern2, $value);
        if (empty($result1) || empty($result2)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 验证所给手机号码是否符合手机号的格式.
     * 移动：134、135、136、137、138、139、150、151、152、157、158、159、182、183、184、187、188、178(4G)、147(上网卡)；
     * 联通：130、131、132、155、156、185、186、176(4G)、145(上网卡)；
     * 电信：133、153、180、181、189 、177(4G)；
     * 卫星通信：  1349
     * 虚拟运营商：170
     *
     * @param string $mobile 手机号.
     * @return bool
     */
    public static function validateMobile($mobile) {
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }
}