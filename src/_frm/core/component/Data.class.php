<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 框架数据封装器，注意该静态类主要是用在 同一次请求/同一进程 中的数据封装。
 * 主要功能：对全局变量以及对象，程序在运行中的重复变量进行单一初始化处理。
 * 相同的数据只保留一份，下一次调用的时候是上一次变量的引用。
 * 取代global关键字的用途。
 *
 * @author John
 */
class Data
{
    /**
     * 全局变量静态存储数组
     * @var array
     */
    private static $_data = array();
    
    /**
     * 根据键名获得数据项(返回成员变量引用)。
     * 内部对框架的一些组件进行了封装以及初始化。
     * 
     * @param  string $key
     * @return mixed 没有找到对应的变量值则返回null
     */
    public static function &get($key)
    {
        if(!isset(self::$_data[$key])){
            //框架组件判断
            switch ($key){
                /**
                 * PHP内置全局变量封装
                 */
                case '_GET':     self::$_data[$key] = &$_GET;                           break;
                case '_ENV':     self::$_data[$key] = &$_ENV;                           break;
                case '_POST':    self::$_data[$key] = &$_POST;                          break;
                case '_INPUT':   self::$_data[$key] = file_get_contents('php://input'); break;
                case '_REQUEST': self::$_data[$key] = &$_REQUEST;                       break;
                case '_FILES':   self::$_data[$key] = &$_FILES;                         break;
                case '_COOKIE':  self::$_data[$key] = &$_COOKIE;                        break;
                case '_SERVER':  self::$_data[$key] = &$_SERVER;                        break;
                case '_GLOBALS': self::$_data[$key] = &$_GLOBALS;                       break;
                case '_SESSION': 
                    /**
                     * 对于SESSION的封装比较特殊，因为必须在脚本最开始使用session_start初始化，这里需进行判断
                     */
                    if (sessionStarted() || php_sapi_name() == 'cli') {
                        self::$_data[$key] = &$_SESSION; 
                    } else {
                        exception('Session not started!');
                    }
                    break;
            }
        }
        if(!isset(self::$_data[$key])){
            self::$_data[$key] = null;
        }
        return self::$_data[$key];
    }
    
    /**
     * 设置数据项(值复制)。
     * 注意：函数的引用参数在PHP5.3以后已被取消掉，因此每一次set的调用都是完整的赋值操作。
     * @param string $key
     * @param mixed  $value
     */
    public static function set($key, $value)
    {
        self::$_data[$key] = $value;
    }
    
    /**
     * 为了测试性的需要，清空所有保存的进程变量(全局变量、单例对象)。
     */
    public static function reset()
    {
        self::$_data = array();
    }
    
    /**
     * 返回所有存放的全局变量数据
     * @return array
     */
    public static function &getAll()
    {
        return self::$_data;
    }
}
