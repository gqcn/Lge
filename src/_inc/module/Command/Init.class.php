<?php
/**
 * Lge命令：Lge初始化空项目。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command_Init
 *
 * @package Lge
 */
class Module_Command_Init extends BaseModule
{

    /**
     * 获得实例.
     *
     * @return Module_Command_Init
     */
    public static function instance()
    {
        return self::_instanceInternal(__CLASS__);
    }

    /**
     * 初始化以lge为框架的空项目
     *
     * @return void
     */
    public function run()
    {
        if (preg_match('/phar:\/\/(.+\/lge.phar)/', L_ROOT_PATH, $match)) {
            $pharPath = $match[1];
            $homePath = Lib_ConsoleOption::instance()->getOption('d', getcwd());
            if (file_exists($homePath)) {
                $phar = new \Phar($pharPath);
                $phar->extractTo('/tmp/lge', null, true);
                $homePath = rtrim($homePath, '/').'/';
                $expPath  = "/tmp/lge/*";
                exec("cp -fr {$expPath} {$homePath}");
            } else {
                exception("Project path '{$homePath}' dose not exist!");
            }
        }
    }

}
