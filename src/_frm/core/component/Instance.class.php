<?php
namespace Lge;

if (!defined('LGE')) {
	exit('Include Permission Denied!');
}

/**
 * 单例/单态模式，单例对象生成器。
 * 主要功能，生成框架所支持的相关组件实例对象。
 * 目前内置进入框架所支持的组件有：(根据项目需求可不断完善追加支持组件)
 * 数据库
 * 缓存服务器
 * 模板引擎
 * Cookie对象
 * 
 *  John
 */
class Instance
{

    /**
     * 根据DB配置项名称获得单例对象.
     *
     * @param string $name   数据库配置项名称.
     * @param array  $dbConf 数据库配置数组(可自定义传递配置信息获取数据库操作实例).
     *
     * @return Database
     */
    public static function database($name = 'default', array $dbConf = array())
    {
        $key  = '_OBJ_DATABASE_'.$name;
        $obj  = &Data::get($key);
        if (empty($obj)) {
            $conf = &Config::get();
            if (isset($conf['DataBase'][$name])) {
                $config = $conf['DataBase'][$name];
                if (is_array($config)) {
                    require_once(__DIR__.'/Database.class.php');
                    $obj = new Database($config);
                    $obj->setDebug(DEBUG);
                    Data::set($key, $obj);
                }
            }
        }
        return $obj;
    }
    
    /**
     * 根据Memcached配置项名称获得单例对象.
     * 文档参考：http://php.net/manual/en/book.memcached.php
     *
     * @param string $name 配置项名称.
     *
     * @return Memcached
     */
    public static function memcached($name = 'default')
    {
        $key  = "_OBJ_MEMCACHED_{$name}";
        $obj  = &Data::get($key);
        if (empty($obj)) {
            $conf = &Config::get();
            if (isset($conf['MemcacheServer'][$name])) {
                $config = $conf['MemcacheServer'][$name];
                $obj    = new Memcached();
                $obj->addServers($config);
                Data::set($key, $obj);
            } else {
                exception("Memcache Server configuration for '{$name}' not found!");
            }
        }
        return $obj;
    }

    
    /**
     * 根据Redis配置项名称获得单例对象.
     *
     * @param string $name 配置项名称.
     *
     * @return MemcacheServer
     */
    public static function redis($name = 'default')
    {
        if (!class_exists('Redis')) {
            exception("Class 'Redis' not found!");
        } else {
            $key  = "_OBJ_REDIS_{$name}";
            $obj  = &Data::get($key);
            if (empty($obj)) {
                $conf = &Config::get();
                if (isset($conf['RedisServer'][$name])) {
                    $config       = $conf['RedisServer'][$name];
                    $obj          = new Redis();
                    $obj->open($config['host'], $config['port'], 0);
                    $obj->select($config['db']);
                    Data::set($key, $obj);
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
        $key  = "_OBJ_TEMPLATE";
        $obj  = &Data::get($key);
        if (empty($obj)) {
            require_once(__DIR__.'/../view/Template.class.php');
            $obj = new Template();
            Data::set($key, $obj);
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
        $key  = "_OBJ_COOKIE";
        $obj  = &Data::get($key);
        if (empty($obj)) {
            $conf = &Config::get();
            if (isset($conf['Cookie'])) {
                require_once(__DIR__.'/Cookie.class.php');
                $config = $conf['Cookie'];
                $obj    = new Cookie($config['path'], $config['domain'], $config['expire'], $config['authkey']);
                Data::set($key, $obj);
            } else {
                exception("Cookie configuration not found!");
            }
        }
        return $obj;
    }
    
    /**
     * 获得Gearman单例对象.
     * 
     * @param string $name 配置项名称.
     * 
     * @return GearmanClient
     */
    public static function gearman($name = 'default')
    {
        if (class_exists('GearmanClient')) {
            exception("Class 'GearmanClient' not found!");
        } else {
            $key  = "_OBJ_GEARMAN_{$name}";
            $obj  = &Data::get($key);
            if (empty($obj)) {
                $conf = &Config::get();
                if (isset($conf['Gearman'][$name])) {
                    $obj = new GearmanClient();
                    $obj->addServer($conf['Gearman'][$name]['host'], $conf['Gearman'][$name]['port']);
                } else {
                    exception("RedisServer configuration '{$name}' not found!");
                }
            }
            return $obj;
        }
    }
    
    /**
     * 获得对象的方法，请使用该方法获得对象.
     *
     * @param string $table        表名称.
     * @param string $dbConfigName 数据库配置名称.
     *
     * @return BaseModelTable
     */
    public static function table($table, $dbConfigName = '')
    {
        return BaseModelTable::getInstance($table, $dbConfigName);
    }
}
