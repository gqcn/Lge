<?php
/**
 * Bootstrap.
 * 流程引导文件，可以被其他框架包含或者直接访问使用.
 * @author John
 */

// 常量定义
include(__DIR__.'/_cfg/const.inc.php');

// 框架引入
include(__DIR__.'/_frm/common.inc.php');

/*
 * 执行流程初始化(控制器初始化)。
 * 注意：当被其他项目包含引用时，不会执行控制器初始化操作，仅作包含。
 */
if (strpos(L_ROOT_PATH, 'phar://') !== false || file_exists(L_ROOT_PATH.'_frm')) {
    \Lge\Core::initController();
}
