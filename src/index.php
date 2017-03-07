<?php
/**
 * Bootstrap.
 * 
 * @author john
 */
// 常量定义
include(__DIR__.'/_cfg/const.inc.php');

// 框架引入
include(FRAME_PATH.'common.inc.php');


// 框架初始化并执行
\Lge\Core::initController();
