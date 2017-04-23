<?php
/**
 * 单例/单态模式，单例对象生成器。
 * 主要功能：
 * 1、封装框架核心组件单例实例对象；
 * 2、支持注册/获取自定义单例对象(依赖注入)；
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 单例对象工厂.
 */
class Instance
{

    /**
     * 向工厂注册单例对象。
     *
     * @param string $key 对象注册名称.
     * @param mixed  $obj 注册对象实例.
     *
     * @return void
     */
    public static function set($key, $obj)
    {
        Data::set(self::_formatKey($key), $obj);
    }

    /**
     * 获得注册的单例对象。
     *
     * @param string $key 对象注册名称.
     *
     * @return mixed
     */
    public static function &get($key)
    {
        return Data::get(self::_formatKey($key));
    }

    /**
     * 格式化键值名称.
     *
     * @param string $key 对象注册名称.
     *
     * @return string
     */
    private static function _formatKey($key)
    {
        return "_lge_instance_{$key}";
    }

    /**
     * 根据DB配置项名称获得单例对象，同一个数据库配置在同一请求进程中只保留一个单例对象(只保留一个数据库链接).
     *
     * @param string $name 数据库配置项名称.
     *
     * @return Database
     */
    public static function database($name = 'default')
    {
        $key = "lge_database_{$name}";
        $obj = &self::get($key);
        if (empty($obj)) {
            $config = Config::getValue("DataBase.{$name}");
            if (!empty($config) && is_array($config)) {
                require_once(__DIR__.'/Database.class.php');
                $obj = new Database($config);
                $obj->setDebug(L_DEBUG);
                self::set($key, $obj);
            } else {
                exception("Database configuration for '{$name}' not found!");
            }
        }
        return $obj;
    }
    
    /**
     * 根据Memcached配置项名称获得单例对象.
     * 文档参考：http://php.net/manual/en/book.memcached.php。
     *
     * @param string $name 配置项名称.
     *
     * @return \Memcached
     */
    public static function memcached($name = 'default')
    {
        if (!class_exists('Memcached')) {
            exception("Class 'Memcached' not found!");
        } else {
            $key = "lge_memcached_{$name}";
            $obj = &self::get($key);
            if (empty($obj)) {
                $config = Config::getValue("MemcacheServer.{$name}");
                if (!empty($config)) {
                    $obj = new \Memcached();
                    $obj->addServers($config);
                    self::set($key, $obj);
                } else {
                    exception("Memcache Server configuration for '{$name}' not found!");
                }
            }
            return $obj;
        }
    }
    
    /**
     * 根据Redis配置项名称获得单例对象.
     *
     * @param string $name 配置项名称.
     *
     * @return \Redis
     */
    public static function redis($name = 'default')
    {
        if (!class_exists('Redis')) {
            exception("Class 'Redis' not found!");
        } else {
            $key = "lge_redis_{$name}";
            $obj = &self::get($key);
            if (empty($obj)) {
                $config = Config::getValue("RedisServer.{$name}");
                if (!empty($config)) {
                    $obj = new \Redis();
                    $obj->open($config['host'], $config['port'], 0);
                    $obj->select($config['db']);
                    self::set($key, $obj);
                } else {
                    exception("Redis Server configuration for '{$name}' not found!");
                }
            }
            return $obj;
        }
    }
    
    /**
     * 获得模板引擎单例对象.
     *
     * @return Template
     */
    public static function template()
    {
        $key = "lge_template";
        $obj = &self::get($key);
        if (empty($obj)) {
            require_once(__DIR__.'/../view/Template.class.php');
            $obj = new Template();
            self::set($key, $obj);
        }
        return $obj;
    }
    
    /**
     * 获得Cookie操作单例对象.
     *
     * @return Cookie
     */
    public static function cookie()
    {
        $key = "lge_cookie";
        $obj = &self::get($key);
        if (empty($obj)) {
            $config = Config::getValue('Cookie');
            if (!empty($config)) {
                require_once(__DIR__.'/Cookie.class.php');
                $obj = new Cookie($config['path'], $config['domain'], $config['expire'], $config['authkey']);
                self::set($key, $obj);
            } else {
                exception("Cookie configuration not found!");
            }
        }
        return $obj;
    }

    /**
     * 获得对象的方法，请使用该方法获得对象.
     *
     * @param string $table        表名称.
     * @param string $dbConfigName 数据库配置名称.
     *
     * @return BaseModelTable
     */
    public static function table($table, $dbConfigName = 'default')
    {
        return BaseModelTable::getInstance($table, $dbConfigName);
    }

}
