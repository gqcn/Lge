<?php
if (!defined('PhpMe')) {
	exit('Include Permission Denied!');
}

class Controller_Router extends Controller_Base
{
    
    /**
     * 默认入口函数.
     *
     * @return void
     */
    public function index()
    {

    }

    public function url()
    {
        echo "http://xxx.xxx.xxx/admin.php?__c=user&__m=list&type=1&page=2\n";
        echo "http://xxx.xxx.xxx/admin.php?__m=list&type=1&page=2\n";
    }
}