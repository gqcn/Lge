<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 返回客户端请求封装方法类.
 *
 * @author john
 */
class Lib_Response
{

    /**
     * 固定格式返回json数据.
     *
     * @param boolean $result  结果(0:失败，1:成功).
     * @param mixed   $data    数据.
     * @param string  $message 提示信息.
     * @param mixed   $extra   额外参数关联数组.
     * @param boolean $exit    是否停止执行.
     *
     * @return void
     */
    public static function json($result = true, $data = array(), $message = '', $extra = array(), $exit = true)
    {
        $result = array(
            'result'  => $result,
            'message' => $message,
            'data'    => $data,
        );
        if (!empty($extra)) {
            $result = array_merge($result, $extra);
        }
        header('Content-type: application/json');
        echo json_encode($result);
        if ($exit) {
            exception('exit');
        }
    }

    /**
     * 固定格式返回jsonp请求，需要在GET请求中带callback方法字段.
     *
     * @param boolean $result  结果(0:失败，1:成功).
     * @param mixed   $data    数据.
     * @param string  $message 提示信息.
     * @param mixed   $extra   额外参数关联数组.
     * @param boolean $exit    是否停止执行.
     *
     * @return void
     */
    public static function jsonp($result = true, $data = array(), $message = '', $extra = array(), $exit = true)
    {
        $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
        if (empty($callback)) {
            self::json($result, $data, $message, $extra, $exit);
        } else {
            echo $callback.'(';
            self::json($result, $data, $message, $extra, false);
            echo ');';
        }
        if ($exit) {
            exception('exit');
        }
    }

    /**
     * 固定格式返回xml数据.
     *
     * @param boolean $result  结果(0:失败，1:成功).
     * @param mixed   $data    数据.
     * @param string  $message 提示信息.
     * @param mixed   $extra   额外参数关联数组.
     * @param boolean $exit    是否停止执行.
     *
     * @return void
     */
    public static function xml($result = true, $data = array(), $message = '', $extra = array(), $exit = true)
    {
        $result = array(
            'result'  => $result,
            'message' => $message,
            'data'    => $data,
        );
        if (!empty($extra)) {
            $result = array_merge($result, $extra);
        }
        header("Content-type: text/xml");
        echo Lib_XmlParser::array2Xml(
            array(
                'response' => $result
            )
        );
        if ($exit) {
            exception('exit');
        }
    }

    /**
     * 允许指定的来源地址跨域请求。
     *
     * @param string  $allowOrigin  允许跨域请求的来源地址
     * @param string  $allowMethods 允许的跨域请求方式
     * @param integer $maxAge       在多少秒内，不需要再发送预检验请求，可以缓存该结果
     *
     * @return void
     */
    public static function allowCrossDomainRequest($allowOrigin = '*', $allowMethods = 'GET, POST, PUT, DELETE', $maxAge = 3628800)
    {
        header("Access-Control-Allow-Origin: {$allowOrigin}");
        header("Access-Control-Allow-Methods: {$allowMethods}");
        header("Access-Control-Max-Age: {$maxAge}");
    }

}
