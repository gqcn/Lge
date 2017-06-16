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

define('LGE',             1);         // 用于判断包含标识
define('L_FRAME_VERSION', 'v3.2.10'); // 当前Lge框架版本
defined('L_DEBUG')                 || define('L_DEBUG',   1); // 是否开启调试模式，判断当前运行环境是否允许调试
defined('L_ERROR_LEVEL_FOR_DEBUG') || define('L_ERROR_LEVEL_FOR_DEBUG', E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED); // 当 L_DEBUG 为1时有效
defined('L_ROOT_PATH')             || define('L_ROOT_PATH',             realpath(__DIR__.'/..').'/'); // 系统根目录文件系统绝对路径
defined('L_FRAME_PATH')            || define('L_FRAME_PATH',            __DIR__.'/');                 // 框架根目录文件系统绝对路径

// 加载框架
include(L_FRAME_PATH.'/core/Core.inc.php');
