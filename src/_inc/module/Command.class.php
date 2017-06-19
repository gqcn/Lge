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
     * 入口函数.
     * values  : 终端传入的命令参数
     * options : 对应的是'-'或者'--'开头的选项
     *
     * @return void
     */
    public function run()
    {
        $coption = Lib_ConsoleOption::instance();
        $values  = $coption->getValues();
        if (empty($values)) {
            $options = $coption->getOptions();
            $this->_checkOptionsWithoutValues($options);
        } else {
            $this->_checkValues($values);
        }
    }

    /**
     * 当命令行传入的参数为空时，默认的选项处理
     * 命令行选项是带有 "-" 或者 "--" 开头的参数
     *
     * @param array $options 命令行选项
     *
     * @return void
     */
    private function _checkOptionsWithoutValues(array $options)
    {
        foreach ($options as $option => $v) {
            switch ($option) {
                case '?':
                case 'h':
                    $this->_showHelp();
                    break;

                case 'i':
                case 'v':
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
    private function _checkValues(array $values)
    {
        $command = isset($values[0]) ? $values[0] : null;
        $command = trim($command);
        switch ($command) {
            case '?':
            case 'help':
                $this->_showHelp();
                break;

            case 'info':
                $version = $this->_getVersionInfo();
                echo "{$version}\n";
                break;

            case 'init':
            case 'install':
            case 'phar':
                $this->_runCommand($command);
                break;

            default:
                if (!empty($command)) {
                    Lib_Console::perror("Unknown command: {$command}\n");
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
        echo <<<MM
{$version}
Usage   : lge [command/option] [option]
Commands:
    ?,-?,-h,help      : this help
    -v,-i,info        : show version info
    init [path]       : initialize current working folder as an empty PHP project using Lge framework
        path          : initialize the folder path(relative or absolute) as an empty PHP project using Lge framework
    install [lge/php] : install lge binary to system
        lge           : install lge binary to system(default)
        php           : install basic PHP extensions


MM;
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
        return "Lge version {$lgeVersion}, running in PHP{$phpVersion}";
    }

}
