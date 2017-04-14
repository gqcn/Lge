<?php
namespace Lge;

/**
 * 框架核心函数定义(为简化使用，并没有进行封装).
 * 
 * @author John
 */
if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 抛出一个异常(主要拿给框架进行后续处理).
 *
 * @param string $string 异常信息
 * @throws Exception
 */
function exception($string)
{
    throw new \Exception($string);
}

/**
 * 判断session是否已经开启.
 * 
 * @return boolean
 */
function sessionStarted()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? true : false;
        } else {
            return session_id() === '' ? false : true;
        }
    }
    return false;
}

/**
 * 转义GET、POST、COOKIE传递的值，判断魔法引用进行处理。
 *
 * @param  string $str
 * @return string
 */
function strAddSlashes($str)
{
    return get_magic_quotes_gpc() ? $str : addslashes($str);
}

/**
 * 转义GET、POST、COOKIE传递的值，判断魔法引用进行处理。
 *
 * @param  array   $array
 * @param  boolean $strict 强制转义，不管有没魔法引用
 * @return array
 */
function arrayAddSlashes($array, $strict = false)
{
    if(!get_magic_quotes_gpc() || $strict){
        foreach ($array as $k => $v) {
            if(is_array($v)){
                $array[$k] = arrayAddSlashes($v);
            }else{
                $array[$k] = addslashes($v);
            }
        }
    }
    return $array;
}

/**
 * 对数组中的元素执行trim处理,如果元素也是数组,那么递归处理.
 * 
 * @param array $array 数组.
 * 
 * @return array
 */
function arrayTrim($array)
{
    foreach ($array as $k => $v) {
        if(is_array($v)){
            $array[$k] = arrayTrim($v);
        }else{
            $array[$k] = isset($v) ? trim($v) : null;
        }
    }
    return $array;
}

/**
 * 对数组中的元素执行转义和trim处理.
 * 
 * @param array   $array 数组.
 * @param boolean $strictAddSlashes 是否强制添加slashes.
 * 
 * @return array
 */
function arrayTrimAndSlashes($array, $strictAddSlashes = false)
{
    static $magicQuotes = null;
    if (!isset($magicQuotes)) {
        $magicQuotes = get_magic_quotes_gpc();
    }
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            $array[$k] = arrayTrimAndSlashes($v, $strictAddSlashes);
        } else {
            $v = trim($v);
            if ($strictAddSlashes) {
                $array[$k] = addslashes($v);
            } else {
                $array[$k] = $magicQuotes ? $v : addslashes($v);
            }
        }
    }
    return $array;
}

/**
 * 去除GET、POST、COOKIE的转义，判断魔法引用进行处理。
 *
 * @param  string $str
 * @return string
 */
function strStripSlashes($str)
{
    return get_magic_quotes_gpc() ? stripslashes($str) : $str;
}

/**
 * 去除GET、POST、COOKIE的魔法引用转义，判断魔法引用进行处理。
 *
 * @param  array   $array
 * @param  boolean $strict 强制反转义，不管有没魔法引用
 * @return array
 */
function arrayStripSlashes($array, $strict = false)
{
    if(get_magic_quotes_gpc() || $strict){
        foreach ($array as $k => $v) {
            $array[$k] = stripslashes($v);
        }
    }
    return $array;
}