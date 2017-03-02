<?php
if(!defined('PhpMe')){
	exit('Include Permission Denied!');
}


class Controller_Test extends Controller_Base
{
    public function index()
    {
        Logger::log('test', 'test', Logger::DATA);
    }
}
