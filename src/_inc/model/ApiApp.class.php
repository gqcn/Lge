<?php
if(!defined('PhpMe')){
    exit('Include Permission Denied!');
}
/**
 * 云服务 - 云服务应用管理模型。
 *
 */
class Model_ApiApp extends BaseModelTable
{
    public $table = 'api_app';

    /**
     * 获得实例.
     *
     * @return Model_ApiApp
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * 获取当前我可管理的应用列表.
     *
     * @return array
     */
    public function getMyApps()
    {
        return $this->getAll(
            '*',
            array('uid' => $this->_session['user']['uid']),
            null,
            '`order` asc, id asc'
        );
    }
}