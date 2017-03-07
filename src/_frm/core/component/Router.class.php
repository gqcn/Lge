<?php
namespace Lge;

if (!defined('LGE')) {
	exit('Include Permission Denied!');
}

/**
 * 框架路由控制类。
 */
class Router
{
    /**
     * 解析URI为QUERY字符串.
     * 
     * @param string $uri URI.
     * 
     * @return string
     */
    static public function dispatch($uri)
    {
        $rules    = self::_getRules();
        $uriArray = explode('?', $uri);
        foreach ($rules['uri'] as $rule => $replace) {
            if (preg_match($rule, $uriArray[0])) {
                $uriArray[0] = preg_replace($rule, $replace, $uriArray[0]);
                break;
            }
        }
        $newUri = implode('&', $uriArray);
        return $newUri;
    }
    
    /**
     * 解析内容中满足条件的QUERY字符串，转换为SEO连接.
     * 
     * @param string $content Content.
     * 
     * @return string
     */
    static public function patch($content)
    {
        $rules = self::_getRules();
        foreach ($rules['url'] as $rule => $replace) {
            $content = preg_replace($rule, $replace, $content);
        }
        return $content;
    }
    
    /**
     * 获取路由解析规则.
     *
     * @return array
     */
    static private function _getRules()
    {
        static $configs = array();
        if (!empty($configs[Core::$sys])) {
            return $configs[Core::$sys];
        }
        $config = array();
        // 全局路由配置
        $rootCfgPath = ROOT_PATH.'_cfg/router.inc.php';
        if (file_exists($rootCfgPath)) {
            $config = include($rootCfgPath);
        }
        // 分站路由配置(分站路由配置优先级高于全局路由配置)
        if (empty($config['uri'])) {
            $config['uri'] = array();
        }
        if (empty($config['url'])) {
            $config['url'] = array();
        }
        $extCfgPath = Core::$sysDir.'_cfg/router.inc.php';
        if (file_exists($extCfgPath)) {
            $tempConfig = include($extCfgPath);
            // 全局配置放到分站配置的后面，相同的键名将会被分站配置覆盖
            if (!empty($tempConfig['uri'])) {
                $config['uri'] = array_merge($tempConfig['uri'], $config['uri']);
            }
            if (!empty($tempConfig['url'])) {
                $config['url'] = array_merge($tempConfig['url'], $config['url']);
            }
        }
        $configs[Core::$sys] = $config;
        return $config;
    }
}