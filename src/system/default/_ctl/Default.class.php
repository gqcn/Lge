<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}

/**
 * 这是框架的默认加载类，一般什么都不做，用于被包含时不做任何逻辑处理。
 * @package Lge
 */
class Controller_Default extends BaseController
{
    public function index()
    { }
}
