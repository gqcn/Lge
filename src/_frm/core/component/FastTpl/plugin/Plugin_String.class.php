<?php
/**
 * 字符串插件.
 *
 * @author john
 */

namespace Lge;

/**
 * Class Plugin_String
 *
 * @package Lge
 */
class Plugin_String
{

    /**
     * 对数组进行json编码.
     *
     * @param array $array 数组.
     *
     * @return string
     */
    public function jsonEncode(array $array)
    {
        return json_encode($array);
    }
    
    /**
     * 对json字符串进行解码.
     *
     * @param string $jsonString Json字符串.
     *
     * @return array
     */
    public function jsonDecode($jsonString)
    {
        return json_decode($jsonString, true);
    }
    
    /**
     * 转义特殊的HTML字符.
     *
     * @param string $string HTML字符串.
     *
     * @return string
     */
    public function escape($string)
    {
        return htmlspecialchars($string);
    }

    /**
     * 字符串转换为小写.
     *
     * @param string $string 字符串.
     *
     * @return string
     */
    public function strtolower($string)
    {
        return strtolower($string);
    }

    /**
     * 字符串转换为大写.
     *
     * @param string $string 字符串.
     *
     * @return string
     */
    public function strtoupper($string)
    {
        return strtoupper($string);
    }
    
    /**
     * 高亮字符串.
     *
     * @param string $string 字符串.
     * @param string $key    关键字.
     * @param string $color  颜色.
     *
     * @return string
     */
    public function highlight($string, $key, $color = 'red')
    {
        $key = trim($key);
        if (!empty($key)) {
            $key = preg_quote($key);
            return preg_replace("/({$key})/i", "<span style=\"color:{$color}\">\\1</span>", $string);
        } else {
            return $string;
        }
    }
    
    /**
     * 字符串截取函数(UTF-8).
     *
     * @param string  $string 需要截取字符串.
     * @param integer $length 截取长度.
     * @param string  $addStr 附加到末尾的字符串.
     *
     * @return string
     */
    public function subStr($string, $length, $addStr = '...')
    {
        $strLength = 0;
        if (strlen($string) > $length) {
            // 将$length换算成实际UTF8格式编码下字符串的长度
            for ($i = 0; $i < $length; $i++) {
                if ($strLength >= strlen($string)) {
                    break;
                }
                // 当检测到一个中文字符时
                if ( ord($string[$strLength]) > 127 ) {
                    $strLength += 3;
                } else {
                    $strLength += 1;
                }
            }
            return substr($string, 0, $strLength).$addStr;
        } else {
            return $string;
        }
    }

    /**
     * 从字符串中间向两边隐藏字符(主要用于姓名、手机号、邮箱地址、身份证号等的隐藏)，支持utf-8中文，支持email格式。
     *
     * @param string  $str     需要隐藏的字符串。
     * @param integer $percent 中间隐藏的百分比。
     * @param string  $hide    使用的隐藏字符。
     *
     * @return mixed
     */
    public function hideStr($str, $percent = 50, $hide = '*')
    {
        if (strpos($str, '@')) {
            $email = explode('@', $str);
            $str   = $email[0];
        }
        $length     = mb_strlen($str, 'utf-8');
        $mid        = floor($length / 2);
        $hideLength = floor($length * ($percent / 100));
        $start      = (int)$mid - floor($hideLength / 2);
        $hideStr    = '';
        for ($i = 0; $i < $hideLength; $i++) {
            $hideStr .= $hide;
        }
        if (!empty($email[1])) {
            $str .= '@'.$email[1];
        }
        return substr_replace($str, $hideStr, $start, $hideLength);
    }

}