<?php
/**
 * 一些系统的常量配置（常量是在程序部署时将完全确定的数值，后续运行将不再或者极少进行修改）。
 *
 * @author John<john@johnx.cn>
 */
// 是否开启调试模式(如果关闭，那么程序错误将不会显示在界面上，但是依旧会写入错误日志中)
define('DEBUG',                       1);
// 错误提示级别(当DEBUG为1时启用，用于设定程序开发时的错误信息显示级别)
define('ERROR_LEVEL_FOR_DEBUG',       E_ALL);
// 当前部署环境(该变量为用户自定义，用于用户自定义程序的处理，一般为dev, staging, product)
define('DEPLOYMENT',                  'dev');
// 项目根目录
define('ROOT_PATH',                   __DIR__.'/../');
// 指向使用的框架目录
define('FRAME_PATH',                  __DIR__.'/../_frm/');
// SESSION的存储方式，支持两种 file 和 memcache，默认是 file
define('SESSION_STORAGE',             'memcache');
// 当 SESSION_STORAGE 值为 memache 时有效，设置config中用于session存储的memcache配置项名称
define('SESSION_MEMCACHE_KEY',        'default');
// 允许通过二级域名判断子网站(true|false, 如果为true，那么例如 admin.xxx.com 映射的子网站为admin)
define('SYSTEM_BY_SUBDOMAIN',         true);
// 当 SYSTEM_BY_SUBDOMAIN=true 时有效, 子域名级别
define('SYSTEM_BY_SUBDOMAIN_LEVEL',   2);
// 当 SYSTEM_BY_SUBDOMAIN=true 时有效，表示子级域名与子站点目录的映射数组，默认子站点名字与子域名相同
define('SYSTEM_BY_SUBDOMAIN_MAPPING', json_encode(array()));
// 时区设置(默认为上海时区)
define('DEFAULT_TIME_ZONE',           'Asia/Shanghai');