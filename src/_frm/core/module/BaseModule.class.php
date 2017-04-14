<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 模块基类，主要是对于数据的封装，注意不仅仅是对于数据库的数据。
 *
 * @author John
 */
class BaseModule extends Base
{
    public function __construct()
    {
        parent::__construct();
    }
}
