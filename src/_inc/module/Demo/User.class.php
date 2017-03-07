<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}
/**
 * 用户模型。
 *
 * @author John
 */
class Model_Demo_User extends BaseModelTable
{
    public $table        = 'user';
    public $dbConfigName = 'sqlite_demo';

    /**
     * 获得对象的方法，请使用该方法获得对象.
     *
     * @return Model_Demo_User
     */
    public static function instance ()
    {
        return self::instanceInternal(__CLASS__);
    }
}