<?php
/**
 * 全局变量数组定义(框架以及系统变量配置)。
 *
 * @author john
 */

return array(
    /*
     * 系统配置(可选)
     */
    'System' => array(
        'check_by_subdomain'         => false,   // 通过URL中的子级域名判断子网站(true|false, 如果为true，例如: admin.xxx.com 映射的子网站为admin)
        'check_by_subdomain_level'   => 2,       // 当check_by_subdomain为true时有效, 子域名级别
        // 当check_by_subdomain为true时有效, 表示子级域名与子站点目录的自定义映射数组，默认子站点名字与子域名相同
        'check_by_subdomain_mapping' => array(

        ),
    ),

    /*
     * 日志配置(可选)
     */
    'Logger' => array(
        /*
        'cache'                 => false,                      // 缓存写入的日志内容到内存，最后请求执行完毕后再真正写入到文件系统
        'adapter'               => \Lge\Logger::ADAPTER_FILE,  // 适配配置，默认写入文件
        'adapter_file_log_path' => L_ROOT_PATH.'../log/',      // 日志目录绝对路径
        'error_logging'         => false,                      // 是否将运行产生的日志自动使用logger进行记录，默认关闭
        'error_logging_levels'  => \Lge\Logger::LOG_LEVEL_ALL, // 当产生错误时的日志记录级别，对手动调用日志记录无用，使用 \Lge\Logger::LOG_LEVEL_NONE 来关闭错误日志记录
        */
    ),

    /*
     * 数据库配置项(可选)
     */
    'DataBase' => array(
        /*
        'default' => array(
            'host'     => '127.0.0.1', // 主机地址(使用IP防止DNS解析)
            'user'     => 'root',      // 账号
            'pass'     => '',          // 密码
            'port'     => '3306',      // 数据库端口
            'type'     => 'mysql',     // 数据库类型(mysql|pgsql|sqlite|oracle|mssql)
            'charset'  => 'utf8',      // 数据库编码
            'prefix'   => '',          // 表名前缀，这个时候缩略表名应当以'_'符号开头
            'database' => '',          // 数据库名称
            'linkinfo' => '',          // 可自定义PDO数据库连接信息
        ),
        */

        /*
         * 天然支持主从复制模式，当配置项中包含master和slave字段时，数据库操作自动切换为主从模式，不会读取该配置项内的其他配置.
         * 程序在执行数据库操作时会判断优先级，优先级计算方式：配置项值/总配置项值.
         */
        /*
        'master_slave' => array(
            'master'  => array(
                array(
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'pass'     => '',
                    'port'     => '3306',
                    'type'     => 'mysql',
                    'charset'  => 'utf8',
                    'prefix'   => '',
                    'database' => '',
                    'priority' => 100,
                    'linkinfo' => '',
                ),
                array(
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'pass'     => '',
                    'port'     => '3306',
                    'type'     => 'mysql',
                    'charset'  => 'utf8',
                    'prefix'   => '',
                    'database' => '',
                    'priority' => 100,
                    'linkinfo' => '',
                ),
            ),
            'slave'   => array(
                array(
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'pass'     => '',
                    'port'     => '3306',
                    'type'     => 'mysql',
                    'charset'  => 'utf8',
                    'prefix'   => '',
                    'database' => '',
                    'priority' => 100,
                    'linkinfo' => '',
                ),
                array(
                    'host'     => '127.0.0.1',
                    'user'     => 'root',
                    'pass'     => '',
                    'port'     => '3306',
                    'type'     => 'mysql',
                    'charset'  => 'utf8',
                    'prefix'   => '',
                    'database' => '',
                    'priority' => 100,
                    'linkinfo' => '',
                ),
            ),
        ),
        */
    ),

    /*
     * COOKIE配置项(可选)
     */
    'Cookie' => array(
        'path'    => '/',       // Cookie有效路径
        'domain'  => '',        // Cookie有效域名，如果为空，那么默认获取当前一级域名(注意“.xxx.com”和“xxx.com”格式的区别)
        'expire'  => 86400 * 7, // Cookie默认保存时间
        'authkey' => 'Lge',     // Cookie加密键值
    ),

    /*
     * SESSION配置项(可选)
     */
    'Session' => array(
        'storage'      => 'file',    // SESSION的存储方式，支持两种 file 和 memcache，默认是 file
        'memcache_key' => 'default', // 当storage设置为memache时有效，需要保证配置文件中的MemcacheServer配置项有值
    ),

    /*
     * Redis服务器(可选)
     */
    'RedisServer' => array(
        /*
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
            'db'       => 1,
        ),
        */
    ),
    
    /*
     * Memcache缓存服务器配置项(可选)
     * 注意：如果常量配置文件中设置的session的保存方式为memcache，那么该配置不能为空.
     */
    'MemcacheServer' => array(
        /*
        'default' => array(
            // IP、端口、权重
            array('127.0.0.1', 11211, 100),
            array('127.0.0.2', 11211, 100),
        ),
        */
    ),

);
