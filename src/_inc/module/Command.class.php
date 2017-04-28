<?php
/**
 * Lge命令处理。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command
 *
 * @package Lge
 */
class Module_Command extends BaseModule
{

    /**
     * 获得实例.
     *
     * @return Module_Command
     */
    public static function instance()
    {
        return self::_instanceInternal(__CLASS__);
    }

    /**
     * 命令行选项处理
     *
     * @param array $options 命令行选项
     *
     * @return void
     */
    public function checkOptions(array $options)
    {
        foreach ($options as $option => $v) {
            if ($v !== true) {
                continue;
            }
            switch ($option) {
                case 'i':
                case 'info':
                    $phpVersion = PHP_VERSION;
                    $lgeVersion = L_FRAME_VERSION;
                    echo "Lge version {$lgeVersion}, running in {$phpVersion}\n";
                    break;
            }
        }
    }

    /**
     * 命令行参数处理
     *
     * @param array $values 参数列表
     *
     * @return void
     */
    public function checkValues(array $values)
    {
        foreach ($values as $value) {
            switch ($value) {
                case 'install':
                    $this->_installLgeToSystemBinaryPath();
                    break;
            }
        }
    }

    /**
     * 将lge.phar安装到系统可执行文件目录
     *
     * @return void
     */
    private function _installLgeToSystemBinaryPath()
    {
        $phpBinaryPath = $this->_getPhpBinaryPath();
        if (empty($phpBinaryPath)) {
            echo "PHP binary not found, please install php cli first!\n";
        }

        if (preg_match('/phar:\/\/(.+\/lge.phar)/', L_ROOT_PATH, $match)) {
            $pharPath   = $match[1];
            $binaryDir  = '/usr/bin/';
            $binaryPath = $binaryDir.'lge';
            $content    = "#!/bin/bash\nphp {$pharPath} \$*\n";
            if (is_writable($binaryDir)) {
                file_put_contents($binaryPath, $content);
                @chmod($binaryPath, 0777);
                echo "Lge binary installation done!\n";
            } else {
                echo "Lge binary installation failed, please make sure you have permission to make this.\n";
            }
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
