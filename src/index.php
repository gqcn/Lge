<?php
/**
 * Bootstrap.
 * 流程引导文件，可以被其他框架包含或者直接访问使用.
 * 
 * @author John
 */
// 常量定义
include(__DIR__.'/_cfg/const.inc.php');

// 框架引入
include(L_FRAME_PATH.'/common.inc.php');


// 框架初始化并执行
\Lge\Core::initController();
