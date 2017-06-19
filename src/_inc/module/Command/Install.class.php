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
        $id = shell_exec("id -u");
        $id = trim($id);
        if ($id != "0") {
            Lib_Console::perror("This script must be running as root\n");
            exit();
        }
        $option = Lib_ConsoleOption::instance()->getValue(1, 'lge');
        switch ($option) {
            case 'lge':
                Module_Command_Install_Lge::instance()->run();
                break;

            case 'php':
                Module_Command_Install_Php::instance()->run();
                break;

            default:
                Lib_Console::perror("Unknown install option:{$option}\n");
                break;
        }

    }

}
