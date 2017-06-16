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
            $homePath = realpath($homePath);
            if ($homePath) {
                fwrite(STDOUT, "Sure to initialize an empty Lge project at '{$homePath}' ? (y/n): ");
                if (trim(fgets(STDIN)) == 'y') {
                    // 将lge.phar转换成lge.tar.gz并解压缩到指定临时目录
                    $tmp  = '/tmp/lge_exp';
                    $phar = new \Phar($pharPath);
                    $phar->convertToData(\Phar::TAR,\Phar::GZ)->extractTo($tmp, null, true);
                    // 删除临时的lge.tar.gz文件
                    $gzPath = dirname($pharPath).'/lge.tar.gz';
                    if (file_exists($gzPath)) {
                        unlink($gzPath);
                    }
                    // 复制空项目到指定目录
                    $homePath = rtrim($homePath, '/').'/';
                    $expPath  = "{$tmp}/_exp/*";
                    exec("cp -fr {$expPath} {$homePath}");

                    /*
                     * 内容替换
                     */
                    $constFilePath = $homePath.'src/_cfg/const.inc.php';
                    file_put_contents($constFilePath, str_replace('#L_PHAR_FILE_PATH#', $pharPath, file_get_contents($constFilePath)));
                    Lib_Console::psuccess("Project initialized done!\n");
                } else {
                     Lib_Console::perror("Cancelled.\n", 'red');
                }
            } else {
                Lib_Console::perror("Invalid project path '{$homePath}' specified!");
            }
        } else {
            Lib_Console::perror("It should be running in phar!");
        }
    }

}
