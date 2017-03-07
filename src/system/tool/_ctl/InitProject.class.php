<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}

/**
 * 使用 /doc/init 下的数据初始化项目，可反复初始化，多次执行会使项目变更为初始化数据.
 */
class Controller_InitProject extends Controller_Base
{
    public  $startSession = false;    // 是否开启session
    public  $sessionID    = null;    // 设置session id

    public function index()
    {
        Lib_Utility::initSqlByPath(ROOT_PATH.'../doc/init/');
        echo "Done!".PHP_EOL;
    }

}
