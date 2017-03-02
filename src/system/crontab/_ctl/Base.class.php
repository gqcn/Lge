<?php
if (!defined('PhpMe')) {
	exit('Include Permission Denied!');
}

class Controller_Base extends BaseController
{
    public  $startSession = false;   // 是否开启session
    public  $sessionID    = null;    // 设置session id

    /**
     * 初始化函数.
     */
    public function __init()
    {

    }
}