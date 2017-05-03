<?php
/**
 * 一些系统的常量配置（常量是在程序部署时将完全确定的数值，后续运行将不再或者极少进行修改）。
 *
 * @author John<john@johng.cn>
 */

define('L_DEBUG',                 1);                                           // 是否开启调试模式，判断当前运行环境是否允许调试
define('L_ERROR_LEVEL_FOR_DEBUG', E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED); // 错误提示级别，当 L_DEBUG 为1时有效
define('L_ROOT_PATH',             __DIR__.'/../');                              // 系统根目录文件系统绝对路径
define('L_DEFAULT_TIME_ZONE',     'Asia/Shanghai');                             // 时区设置(默认为中国上海时区)
define('L_PHAR_FILE_PATH',        '#L_PHAR_FILE_PATH#');                        // lge框架phar文件地址
