<?php
/**
 * Bootstrap.
 * 流程引导文件，可以被其他框架包含或者直接访问使用.
 * @author John
 */

// 常量定义
include(__DIR__.'/_cfg/const.inc.php');

// 框架文件引入
if (file_exists(L_PHAR_FILE_PATH)) {
    include(L_PHAR_FILE_PATH);
} else {
    die("Lge framework phar could not found!\n");
}

// 框架初始化并执行
\Lge\Core::initController();
