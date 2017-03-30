<?php
/**
 * 全局包含文件，负责以下工作: 
 * 1、基础配置文件的包含;
 * 2、全局定义函数的包含;
 * 3、单例生成器以及数据封装器的包含;
 * 
 * 注意：
 * 1、为了测试的需要,包含类以及函数定义文件请使用require_once，防止重复定义(配置文件包含无特定要求，建议使用include);
 * 2、该包含文件可以独立于框架，其他独立的系统如果想要引用框架变量可以包含该文件，并使用相关常量、方法以及静态类即可;
 * 
 * @author john
 */
// 用于判断包含标识
if (!defined('LGE')) {
    define('LGE',       1);
}
// 是否开启调试模式
if (!defined('L_DEBUG')) {
    define('L_DEBUG',     1);
}
// 系统根目录文件系统绝对路径
if (!defined('L_ROOT_PATH')) {
    define('L_ROOT_PATH', realpath(__DIR__.'/..').'/');
}

// 系统根目录文件系统绝对路径
if (!defined('L_FRAME_PATH')) {
    define('L_FRAME_PATH', __DIR__.'/');
}

define('FRAME_VERSION', 'Lge v2.8');

// 加载框架
include(L_FRAME_PATH.'/core/Core.inc.php');
