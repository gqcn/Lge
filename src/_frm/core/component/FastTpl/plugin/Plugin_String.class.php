<?php
/**
 * 字符串插件.
 * 
 * @author john
 * @version v0.1 2014-03-06
 */

class Plugin_String
{

    /**
     * 对数组进行json编码.
     * 
     * @param array  $array 数组.
     * 
     * @return string
     */
    public function jsonEncode($array)
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
    public function subStr($string, $length, $addStr = '...') {
       $strcut = '';
       $strLength = 0;
       if(strlen($string) > $length) {
           //将$length换算成实际UTF8格式编码下字符串的长度
           for($i = 0; $i < $length; $i++) {
               if ( $strLength >= strlen($string) ) {
                   break;
               }
               //当检测到一个中文字符时
               if( ord($string[$strLength]) > 127 ) {
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

}