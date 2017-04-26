<?php
/**
 * 配置管理类，主要管理配置文件(_cfg以及exten下的_cfg目录下*.inc.php规则的配置文件)。
 *
 * @author John
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 配置管理类
 */
class Config
{

    /**
     * 读取配置项内容.
     *
     * @param string $key    配置项名称，使用‘.’号表示层级关系.
     * @param string $name   配置文件名称(不包含'.inc.php'，支持目录结构).
     * @param mixed  $system 获取子站点的配置(如果是true，那么获取当前请求的子站点，否则获取指定的子站点配置).
     *
     * @return mixed
     */
    public static function get($key, $name = 'config', $system = null)
    {
        return self::getValue($key, $name, $system);
    }

    /**
     * 读取配置项内容.
     *
     * @param string $key    配置项名称，使用‘.’号表示层级关系.
     * @param string $name   配置文件名称(不包含'.inc.php'，支持目录结构).
     * @param mixed  $system 获取子站点的配置(如果是true，那么获取当前请求的子站点，否则获取指定的子站点配置).
     *
     * @return mixed
     */
    public static function getValue($key, $name = 'config', $system = null)
    {
        $config    = self::getFile($name, $system);
        $keyArray  = explode('.', $key);
        $result    = $config;
        foreach ($keyArray as $v) {
            if (is_array($result) && isset($result[$v])) {
                $result = $result[$v];
            } else {
                $result = null;
            }
        }
        return $result;
    }

    /**
     * 读取配置文件.
     *
     * @param string $name   配置文件名称(不包含'.inc.php'，支持目录结构).
     * @param mixed  $system 获取子站点的配置(如果是true，那么获取当前请求的子站点，否则获取指定的子站点配置).
     *
     * @return mixed
     */
    public static function &getFile($name = 'config', $system = null)
    {
        if (!empty($system)) {
            if ($system === true) {
                $system = Core::$sys;
            }
        }
        $dataKey  = self::_getCacheKey($name, $system);
        $config   = &Data::get($dataKey);
        if (empty($config)) {
            $cfgDir   = empty($system) ? Core::$cfgDir : L_ROOT_PATH.'system/'.Core::$sys.'/_cfg/';
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
     * 写入自定义的配置文件内容.
     *
     * @param array  $config 配置文件数据数组。
     * @param string $name   配置文件名称
     * @param string $system 子站点名称
     *
     * @return void
     */
    public static function set(array $config, $name = 'config', $system = null)
    {
        $dataKey = self::_getCacheKey($name, $system);
        Data::set($dataKey, $config);
    }

    /**
     * 获取缓存的Key。
     *
     * @param string $name   配置文件名称
     * @param string $system 子站点名称
     *
     * @return string
     */
    private static function _getCacheKey($name = 'config', $system = null)
    {
        $dataKey  = "lge_configuration_{$name}_{$system}";
        return $dataKey;
    }

}
