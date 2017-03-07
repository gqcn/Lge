<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}
/**
 * 设置模型。
 *
 */
class Module_Setting extends BaseModule
{
    /**
     * 获得实例.
     *
     * @return Module_Setting
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * 获取.
     * @return array
     */
    public function get($k)
    {
        return json_decode(Instance::table('setting')->getValue('v', array('k=?', $k)), true);
    }

    /**
     * 设置.
     * @param string $k
     * @param mixed  $v
     * @return boolean
     */
    public function set($k, $v)
    {
        return Instance::table('setting')->insert(array('k' => $k,'v' => json_encode($v)), 'replace', false);
    }

    /**
     * 删除.
     * @param string $k
     * @return boolean
     */
    public function drop($k)
    {
        return Instance::table('setting')->delete(array('k=?', $k));
    }
}
