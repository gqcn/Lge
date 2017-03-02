<?php
if (!defined('PhpMe')) {
	exit('Include Permission Denied!');
}

class Controller_Logger extends Controller_Base
{
    
    /**
     * 默认入口函数.
     *
     * @return void
     */
    public function index()
    {
        var_dump(11111);
    echo $a;
        //$this->_test('test');
        var_dump(22222);
    }

    private function _test($params)
    {
        exception('test');
    }
}