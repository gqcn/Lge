<?php
/**
 * Lge命令：Lge CLI工具安装 - lge。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command_Install_Lge
 *
 * @package Lge
 */
class Module_Command_Install_Lge extends Module_Command_Install_Base
{

    /**
     * 获得实例.
     *
     * @return Module_Command_Install_Lge
     */
    public static function instance()
    {
        return self::_instanceInternal(__CLASS__);
    }

    /**
     * 入口函数
     *
     * @return void
     */
    public function run()
    {
        if (!empty(Lib_Console::getBinPath('lge'))) {
            Lib_Console::psuccess("You've already installed lge!\n");
            exit();
        }
        $phpBinaryPath = Lib_Console::getBinPath('php');
        if (empty($phpBinaryPath)) {
            Lib_Console::perror("PHP binary not found, please install php cli first!\n");
        }

        if (preg_match('/phar:\/\/(.+\/lge.phar)/', L_ROOT_PATH, $match)) {
            $pharPath   = $match[1];
            $binaryDir  = '/usr/bin/';
            $binaryPath = $binaryDir.'lge';
            $content    = "#!/bin/bash\nphp {$pharPath} \$*\n";
            if (is_writable($binaryDir)) {
                file_put_contents($binaryPath, $content);
                @chmod($binaryPath, 0777);
                Lib_Console::psuccess("Lge binary installation done!\n");
            } else {
                Lib_Console::perror("Lge binary installation failed, please make sure you have permission to make this.\n");
            }
        } else {
            Lib_Console::perror("It should be running in phar!");
        }
    }

}
