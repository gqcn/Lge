<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 用于XML解析处理的类.
 * 
 * @author john
 */

class Lib_XmlParser
{

    /**
     * 判断所给字符串是否为XML格式
     *
     * @param string $content 字符串.
     *
     * @return array
     */
    public static function isXml($content)
    {
        return (@simplexml_load_string($content) === false) ? false : true;
    }

    /**
     * XML转换为数组，需要simplexml扩展支持
     *
     * @param string $xmlStr XML字符串.
     * @return array
     */
    public static function xml2Array($xmlStr)
    {
        $xmlStr = preg_replace("/<\?xml[^>]*>/i", '', $xmlStr);
        $array  = json_decode(json_encode(simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA)), 1);
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                if(empty($v)){
                    $array[$k] = '';
                } else {
                    $array[$k] = self::_xml2Array($v);
                }
            } else {
                $array[$k] = $v;
            }
        }
        return $array;
    }
    public static function _xml2Array($array)
    {
        if (is_array($array) && empty($array)) {
            $array = '';
        } else {
            foreach ($array as $k => $v){
                if (is_array($v)) {
                    if (empty($v)) {
                        $array[$k] = '';
                    } else {
                        $array[$k] = self::_xml2Array($v);
                    }
                }
            }
        }
        
        return $array;
    }
    
    /**
     * 数组转换为XML，不能以数字作为标签，如果数组含有数字键名，那么使用父标签作为标签名。
     * 当$strict为false时，和xml2Array为互反的函数。
     * @todo 只能生成XML，没有对特殊字符进行处理。
     *
     * @param  array   $array    数组。
     * @param  boolean $strict   是否完全按照键名生成XML标签，否则只能判断标签不使用数字标签(如果为true, 那么数组中包含数字键名，将会直接成为<0></0>这样的形式)。
     * @param  string  $encoding 编码。
     * @param  boolean $noHead   是不不需要主动添加<?xml version="1.0" encoding="'.$encoding.'"?>标签。
     * @return string  XML字符串
     */
    public static function array2Xml($array, $strict = false, $encoding='utf-8', $noHead = false)
    {
        $xmlStr  = $noHead ? "" : '<?xml version="1.0" encoding="'.$encoding.'"?>';
        $xmlStr .= $strict ? self::_array2XmlStrtict($array) : self::_array2Xml($array);
        return $xmlStr;
    }
    public static function _array2Xml($array, $ptag = null)
    {
        $xmlStr = '';
        foreach($array as $key => $val) {
            $tag = is_numeric($key) ? $ptag : $key;
            if(is_array($val)){
                // 获得第一项的关联数组
                list($fkey, $fval) = each($val);
                // 判断是否为数字，如果为数字则使用父级键作为标签
                if(is_numeric($fkey)){
                    $xmlStr .= self::_array2Xml($val, $tag);
                }else{
                    $attribute = '';
                    if(isset($val['@attribute'])){
                        foreach ($val['@attribute'] as $k => $v){
                            $attribute .= " {$k}=\"{$v}\"";
                        }
                        unset($val['@attribute']);
                    }
                    $xmlStr .= "<{$tag}{$attribute}>".self::_array2Xml($val, $tag)."</{$tag}>";
                }
            }else{
                $xmlStr .= "<{$tag}>{$val}</{$tag}>";
            }
        }
        return $xmlStr;
    }
    public static function _array2XmlStrtict($array) 
    {
        $xmlStr = '';
        foreach($array as $key => $val) {
            $xmlStr .= "<{$key}>";
            $xmlStr .= is_array($val) ? self::_array2XmlStrtict($val) : $val;
            $xmlStr .= "</{$key}>";
        }
        return $xmlStr;
    }
}