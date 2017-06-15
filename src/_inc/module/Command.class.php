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
     * 命令行选项是带有 "-" 或者 "--" 开头的参数
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
                case '?':
                    $this->_showHelp();
                    break;

                case 'i':
                case 'v':
                case 'info':
                    $version = $this->_getVersionInfo();
                    echo "{$version}\n";
                    break;
            }
        }
    }

    /**
     * 命令行参数处理
     * 第一条value是命令，其他是命令所需的参数
     * 命令行参数是不带 "-" 或者 "--" 开头的参数
     *
     * @param array $values 参数列表
     *
     * @return void
     */
    public function checkValues(array $values)
    {
        $command = isset($values[0]) ? $values[0] : null;
        $command = trim($command);
        switch ($command) {
            case 'help':
                $this->_showHelp();
                break;

            case 'install':
            case 'phar':
            case 'init':
                $this->_runCommand($command);
                break;

            default:
                if (!empty($command)) {
                    echo "Unknown command: {$command}\n";
                }
                break;
        }
    }

    /**
     * 显示命令帮助。
     *
     * @return void
     */
    private function _showHelp()
    {
        $version = $this->_getVersionInfo();
        $usage   = Lib_Console::highlight("lge [command/option]");
        echo "{$version}\n";
        echo "Usage   : {$usage}\n";
        echo "Commands:\n";
        echo "    ".Lib_Console::highlight("-v,-i,-info")." : show version info\n";
        echo "    ".Lib_Console::highlight("-?,help")."     : this help\n";
        echo "    ".Lib_Console::highlight("install")."     : install lge binary to system\n";
        echo "    ".Lib_Console::highlight("init")."        : initialize current working folder as an empty PHP project using Lge framework\n";
        echo "\n";
    }

    /**
     * 执行命令。
     *
     * @param string $command 命令名称
     *
     * @return void
     */
    private function _runCommand($command)
    {
        $class  = 'Lge\Module_Command_'.ucfirst($command);
        $object = new $class();
        $object->run();
    }

    /**
     * 获得当前运行的PHP版本信息。
     *
     * @return string
     */
    private function _getVersionInfo()
    {
        $phpVersion = PHP_VERSION;
        $lgeVersion = L_FRAME_VERSION;
        return "Lge version {$lgeVersion}, running in {$phpVersion}";
    }

}
