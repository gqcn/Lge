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
     * 第一条value是命令，其他是命令所需的参数
     *
     * @param array $values 参数列表
     *
     * @return void
     */
    public function checkValues(array $values)
    {
        $command = isset($values[0]) ? $values[0] : null;
        switch ($command) {
            case 'install':
                Module_Command_Install::instance()->run();
                break;

            case 'phar':
                Module_Command_Phar::instance()->run();
                break;

            case 'init':
                Module_Command_Init::instance()->run();
                break;
        }
    }

}
