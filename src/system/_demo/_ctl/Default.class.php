<?php
namespace Lge;

if (!defined('LGE')) {
	exit('Include Permission Denied!');
}

class Controller_Default extends Controller_Base
{
    
    /**
     * 测试域名函数.
     *
     * @return void
     */
    public function index()
    {
        var_dump(Lib_Utility::getProcessCount('--a=test'));
    }
}