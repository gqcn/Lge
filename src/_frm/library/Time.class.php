<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 时间处理类.
 *
 * @author John
 */
class Lib_Time
{
    /**
     * 时区转换.
     *
     * @param string $src          来源时间字符串(eg:2016-12-01 12:00:00)
     * @param string $fromTimeZone 来源时区.
     * @param string $toTimeZone   转换成时区.
     * @param string $format       转换后的时间格式化.
     *
     * @return string
     */
    public static function convertTimeZone($src,  
                                           $fromTimeZone = 'America/Denver', 
                                           $toTimeZone   = 'Asia/Shanghai', 
                                           $format       = 'Y-m-d H:i:s') {
        $datetime = new \DateTime($src, new \DateTimeZone($fromTimeZone));
        $datetime->setTimezone(new \DateTimeZone($toTimeZone));
        return $datetime->format($format);
    }
}