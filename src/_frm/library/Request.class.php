<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 接收客户端请求的封装类.
 * 
 * @author john
 */

class Lib_Request
{
    /**
     * 获取GET|POST|REQUEST方式传递的变量值.
     *
     * @param string  $keyName      参数名称.
     * @param mixed   $defaultValue 默认值.
     * @param string  $method       获取方式.
     * @param boolean $trim         是否过滤元素的前后空格.
     *
     * @return mixed
     */
    static public function get($keyName, $defaultValue = null, $method = 'get', $trim = true)
    {
        $data = self::getArray(array($keyName => $defaultValue), $method, $trim);
        return $data[$keyName];
    }

    /**
     * 获取GET方式传递的变量值.
     *
     * @param string  $keyName      参数名称.
     * @param mixed   $defaultValue 默认值.
     * @param boolean $trim         是否过滤元素的前后空格.
     *
     * @return mixed
     */
    static public function getGet($keyName, $defaultValue = null, $trim = true)
    {
        $data = self::getArray(array($keyName => $defaultValue), 'get', $trim);
        return $data[$keyName];
    }

    /**
     * 获取POST方式传递的变量值.
     *
     * @param string  $keyName      参数名称.
     * @param mixed   $defaultValue 默认值.
     * @param boolean $trim         是否过滤元素的前后空格.
     *
     * @return mixed
     */
    static public function getPost($keyName, $defaultValue = null, $trim = true)
    {
        $data = self::getArray(array($keyName => $defaultValue), 'post', $trim);
        return $data[$keyName];
    }

    /**
     * 获取get或者post方式提交的参数(get方式优先).
     *
     * @param string  $keyName      参数名称.
     * @param mixed   $defaultValue 默认值.
     * @param boolean $trim         是否过滤元素的前后空格.
     *
     * @return mixed
     */
    static public function getRequest($keyName, $defaultValue = null, $trim = true)
    {
        $value = self::getGet($keyName, $defaultValue, $trim);
        if (!isset($value)) {
            $value = self::getPost($keyName, $defaultValue, $trim);
        }
        return $value;
    }

    /**
     * 获取post请求数组.
     *
     * @param array   $array                   带需要过滤的key和默认值的数组.
     * @param boolean $returnFullRequestParams 返回完整的请求关联数组，相当于第一个参数数组只是为没有提交的参数设置默认值.
     * @param boolean $trim                    是否过滤元素的前后空格.
     *
     * @return array
     */
    static public function getGetArray(array $array = array(), $returnFullRequestParams = false, $trim = true)
    {
        return self::getArray($array, 'get', $returnFullRequestParams, $trim);
    }

    /**
     * 获取post请求数组.
     *
     * @param array   $array                   带需要过滤的key和默认值的数组.
     * @param boolean $returnFullRequestParams 返回完整的请求关联数组，相当于第一个参数数组只是为没有提交的参数设置默认值.
     * @param boolean $trim                    是否过滤元素的前后空格.
     *
     * @return array
     */
    static public function getPostArray(array $array = array(), $returnFullRequestParams = false, $trim = true)
    {
        return self::getArray($array, 'post', $returnFullRequestParams, $trim);
    }

    /**
     * 获取get或者post方式提交的参数数组(get方式优先)..
     *
     * @param array   $array                   带需要过滤的key和默认值的数组.
     * @param boolean $returnFullRequestParams 返回完整的请求关联数组，相当于第一个参数数组只是为没有提交的参数设置默认值.
     * @param boolean $trim                    是否过滤元素的前后空格.
     *
     * @return array
     */
    static public function getRequestArray(array $array = array(), $returnFullRequestParams = false, $trim = true)
    {
        return self::getArray($array, 'request', $returnFullRequestParams, $trim);
    }

    /**
     * 根据条件筛选request过来的数据, 并对数据执行 arrayTrimAndSlashes 处理，内部会对魔法引用做判断处理.
     * 
     * @param array   $array                   带需要过滤的key和默认值的数组.
     * @param string  $method                  指定请求(|post|get|request).
     * @param boolean $returnFullRequestParams 返回完整的请求关联数组，相当于第一个参数数组只是为没有提交的参数设置默认值.
     * @param boolean $trim                    是否过滤元素的前后空格.
     * 
     * @return array
     */
    static public function getArray(array $array = array(), $method = 'get', $returnFullRequestParams = false, $trim = true)
    {
        $act     = strtolower($method);
        $request = null;
        switch ($act) {
            case 'get':
                $request = &Data::get('_GET');
                break;

            case 'post':
                $request = &Data::get('_POST');
                break;

            case 'request':
                $dataGet  = self::getArray($array, 'get',  $returnFullRequestParams, $trim);
                $dataPost = self::getArray($array, 'post', $returnFullRequestParams, $trim);
                return array_merge($dataPost, $dataGet);
                break;
        }
        $data = array();
        if (empty($array)) {
            $data = $request;
        } elseif ($returnFullRequestParams) {
            $data = $request;
            foreach ($array as $k => $v) {
                $data[$k] = isset($request[$k]) ? $request[$k] : $v;
            }
        } else {
            if (isset($array[0])) {
                foreach ($array as $k => $v) {
                    $data[$v] = isset($request[$v]) ? $request[$v] : null;
                }
            } else {
                foreach ($array as $k => $v) {
                    $data[$k] = isset($request[$k]) ? $request[$k] : $v;
                }
            }
        }
        if ($trim) {
            $data = arrayTrim($data);
        }
        return $data;
    }

    /**
     * 获取当前客户端的请求方式。
     *
     * @return string
     */
    public static function getMethod()
    {
        $globalServer = &Data::get('_SERVER');
        $method       = isset($globalServer['REQUEST_METHOD']) ? $globalServer['REQUEST_METHOD'] : '';
        return $method;
    }

    /**
     * 判断是不是POST请求.
     *
     * @return boolean
     */
    public static function isRequestMethodPost ()
    {
        $globalServer = &Data::get('_SERVER');
        return ($globalServer['REQUEST_METHOD'] == 'POST');
    }
}