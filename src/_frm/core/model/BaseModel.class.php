<?php


if (!defined('PhpMe')) {
	exit('Include Permission Denied!');
}

/**
 * 模型基类，主要是对于数据的封装，注意不仅仅是对于数据库的数据。
 *   
 * @version v0.1 2011-12-20
 */
class BaseModel extends Base
{
    public function __construct()
    {
        parent::__construct();
    }
}
