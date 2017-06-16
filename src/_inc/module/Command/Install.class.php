<?php
/**
 * Lge命令：Lge CLI工具安装。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command_Install
 *
 * @package Lge
 */
class Module_Command_Install extends BaseModule
{

    /**
     * 获得实例.
     *
     * @return Module_Command_Install
     */
    public static function instance()
    {
        return self::_instanceInternal(__CLASS__);
    }

    /**
     * 将lge.phar安装到系统可执行文件目录
     *
     * @return void
     */
    public function run()
    {
        $phpBinaryPath = $this->_getPhpBinaryPath();
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

    /**
     * 获得php执行文件的路径。
     *
     * @return string
     */
    private function _getPhpBinaryPath()
    {
        return trim(shell_exec('which php'));
    }

}
