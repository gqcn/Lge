<?php
/**
 * 全局变量数组定义(框架以及系统变量配置)。
 * 
 * 
 */
return array(
    /**
     * 日志配置(可选).
     */
    'Logger' => array(
        // 缓存写入的日志内容，最后请求执行完毕后再真正写入
        'cache'        => false,
        // 适配配置
        'adapter'      => \Lge\Logger::ADAPTER_FILE,
        // 日志记录级别
        'levels'       => \Lge\Logger::LOG_LEVEL_ALL,
        // 对应适配配置
        'setting'      => array(
            // 日志文件配置
            'file' => array(
                // 日志目录存放位置
                'path' => ROOT_PATH.'../log/',
            ),
            // 日志数据库服务器配置
            'database' => array(
                // 数据库配置对应节点名称(配置文件中需要有该节点的配置)
                'node'  => 'default',
                'table' => 'log',
            ),
        ),
    ),

    /**
     * 数据库配置项(可选)
     */
    'DataBase' => array(
        /*
        'default' => array(
            'host'     => '127.0.0.1',        //主机地址(使用IP防止DNS解析)
            'user'     => 'root',             //账号
            'pass'     => '8692651',          //密码
            'port'     => '3306',             //数据库端口
            'type'     => 'mysql',            //数据库类型 mysql|pgsql|sqlite
            'prefix'   => '',                 //表名前缀
            'charset'  => 'utf8',             //数据库编码
    		'database' => 'smiling_goat',     //数据库名称
        ),
        */
        'sqlite_demo' => array(
            'host'     => '',
            'user'     => '',
            'pass'     => '',
            'port'     => '',
            'type'     => 'sqlite',
            'prefix'   => '',
            'charset'  => 'utf8',
    		'database' => ROOT_PATH.'cache/data/test.sqlite3',
        ),

        /**
         * 天然支持主从复制模式，当配置项中包含master和slave字段时，数据库操作自动切换为主从模式，不会读取该配置项内的其他配置.
         * 程序在执行数据库操作时会判断优先级，优先级计算方式：配置项值/总配置项值.
         */
        /*
        'master_slave' => array(
            'master'  => array(
                array(
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'pass'     => '8692651',
                    'port'     => '3306',
                    'type'     => 'mysql',
                    'charset'  => 'utf8',
                    'database' => 'test',
                    'priority' => 100,
                    'linkinfo' => '',
                ),
                array(
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'pass'     => '8692651',
                    'port'     => '3306',
                    'type'     => 'mysql',
                    'charset'  => 'utf8',
                    'database' => 'test',
                    'priority' => 100,
                    'linkinfo' => '',
                ),
            ),
            'slave'   => array(
                array(
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'pass'     => '8692651',
                    'port'     => '3306',
                    'type'     => 'mysql',
                    'charset'  => 'utf8',
                    'database' => 'test',
                    'priority' => 100,
                    'linkinfo' => '',
                ),
                array(
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'pass'     => '8692651',
                    'port'     => '3306',
                    'type'     => 'mysql',
                    'charset'  => 'utf8',
                    'database' => 'test',
                    'priority' => 100,
                    'linkinfo' => '',
                ),
            ),
        ),
        */
    ),

    /**
     * Redis服务器(可选)
     */
    'RedisServer' => array(
        // 物理redis
        'default' => array(
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'db'       => 0,
        ),
        // 缓存redis
        'cache' => array(
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'db'       => 0,
        ),
    ),
    
    /**
     * Memcache缓存服务器配置项(可选)
     * 注意：如果常量配置文件中设置的session的保存方式为memcache，那么该配置不能为空.
     */
    'MemcacheServer' => array(
        'default' => array(
            // IP、端口、权重
            array('127.0.0.1', 11211, 100),
        ),
    ),
    
    /**
     * Gearman配置(可选)
     */
    'Gearman' => array (
        'default' => array(
            'host' => '127.0.0.1',
            'port' => '4730',
        ),
    ),
    
    /**
     * COOKIE配置项(可选)
     */
    'Cookie' => array(
        'path'    => '/',
        'domain'  => '.johnx.cn',  // 格式为“.xxx.com”，如果为空，那么默认获取当前一级域名
        'expire'  => 86400 * 7,
        'authkey' => 'PhpMe',
    ),

    /**
     * 微信配置(可选)
     */
    'WeChat' => array(
        'appid'     => 'wx83d21dcf8a76bc82',
        'unionid'   => '',
        'appsecret' => '4b74e385d939239ba353363569408514',
        'token'     => 'hello_world',
        'mch_key'   => '99b1ff8f11781541f7f89f9bd41c4a17',
        'mch_id'    => '1291508701',
        'cert_path' => __DIR__.'/cert/picc/',
    ),

    /**
     * 网站配置(可选)
     */
    'System' => array(
        // 前台
        'default' => array(
            'name' => '前台页面',
        ),
        // 后台
        'admin' => array(
            'name' => '管理后台',
        ),
    ),
);
