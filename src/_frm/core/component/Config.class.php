<?php
namespace Lge;

if (!defined('LGE')) {
	exit('Include Permission Denied!');
}

/**
 * 配置管理类，主要管理配置文件(_cfg以及exten下的_cfg目录下*.inc.php规则的配置文件)。
 *
 * @author John
 */
class Config
{
    /**
     * 读取配置文件.
     *
     * @param string  $name     配置文件名称(不包含'.inc.php'，支持目录结构).
     * @param boolean $isSystem 是否获取子站点的配置.
     * @return mixed
     */
    static public function &get($name = 'config', $isSystem = false)
    {
        $dataKey  = self::_getCacheKey($name, $isSystem);
        $config   = &Data::get($dataKey);
        if (empty($config)) {
            $cfgDir   = empty($isSystem) ? Core::$cfgDir : Core::$sysDir.'_cfg/';
            $fileName = "{$name}.inc.php";
            $cfgPath  = $cfgDir.$fileName;
            if (file_exists($cfgPath)) {
                Data::set($dataKey, include($cfgPath));
                $config = &Data::get($dataKey);
            }
        }
        return $config;
    }

    /**
     * 写入特定的配置文件内容.
     * @param array   $config
     * @param string  $name
     * @param boolean $isSystem
     */
    static public function set(array $config, $name = 'config', $isSystem = false)
    {
        $dataKey = self::_getCacheKey($name, $isSystem);
        Data::set($dataKey, $config);
    }

    /**
     * 获取缓存的Key。
     *
     * @param string  $name
     * @param boolean $isSystem
     * @return string
     */
    static private function _getCacheKey($name = 'config', $isSystem = false)
    {
        $isSystem = intval($isSystem);
        $dataKey  = "_Configure_{$name}_{$isSystem}";
        return $dataKey;
    }
}