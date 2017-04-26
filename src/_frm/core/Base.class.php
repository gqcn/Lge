<?php
/**
 * 所有类的基础类，提供了常用的基础组件封装：
 *
 * @author John
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 所有类的基础类
 */
class Base
{
    /*
     * 单例模式.
     * 
     */
    protected static $_instances;

    /**
     * 构造函数
     */
    public function __construct()
    {    }
    
    /**
     * 获得实例.
     *
     * @return Base
     */
    public static function instance()
    {
        return self::_instanceInternal(__CLASS__);
    }
    
    /**
     * 初始化实例.
     *
     * @param mixed $className 初始化的实例的名称.
     * @param array $params    初始化的实例需要的参数数组(必须是一个数组，里面存放各项参数).
     *
     * @return Base
     */
    protected static function _instanceInternal($className, array $params = array())
    {
        if (!isset(self::$_instances[$className])) {
            if (empty($params)) {
                self::$_instances[$className] = new $className();
            } else {
                self::$_instances[$className] = new $className($params);
            }
        }
        return self::$_instances[$className];
    }
    
    /**
     * 魔术方法，不定义组件成员变量，需要的时候再初始化。
     * 注意：只要调用过一次，该类对象则含有了该成员属性，再次调用属性时不会进入该魔法方法。
     *
     * @param  string $name 变量名称.
     *
     * @return mixed
     */
    public function &__get($name)
    {
        $mapping = array(
            // PHP内置全局变量封装
            '_get'     => '_GET',
            '_post'    => '_POST',
            '_env'     => '_ENV',
            '_files'   => '_FILES',
            '_request' => '_REQUEST',
            '_input'   => '_INPUT',
            '_cookie'  => '_COOKIE',
            '_server'  => '_SERVER',
            '_globals' => '_GLOBALS',
            '_session' => '_SESSION',
        );
        if (isset($mapping[$name])) {
            $this->$name = &Data::get($mapping[$name]);
        } else {
            $this->$name = null;
        }
        return $this->$name;
    }

    /**
     * 按照标准的日志格式写入一条日志.
     *
     * @param string  $message  日志信息.
     * @param string  $category 日志目录(分类).
     * @param integer $level    日志级别(info:1, warning:2, error:3).
     *
     * @return void
     */
    public function log($message, $category, $level = Logger::INFO)
    {
        Logger::log($message, $category, $level);
    }

}
