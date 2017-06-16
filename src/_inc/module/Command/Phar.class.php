<?php
/**
 * Lge命令：生成phar包。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command_Phar
 *
 * @package Lge
 */
class Module_Command_Phar extends BaseModule
{

    /**
     * 获得实例.
     *
     * @return Module_Command_Phar
     */
    public static function instance()
    {
        return self::_instanceInternal(__CLASS__);
    }

    /**
     * 生成框架phar包文件.
     *
     * @return void
     */
    public function run()
    {
        @ini_set('phar.readonly', 'off');

        $this->_makeLgePkpPhar();
        Lib_Console::psuccess("Making lge phar file done!\n");
    }

    /**
     * 生成Lge框架的phar执行文件，路经为项目根目录的/bin/lge.phar
     *
     * @return void
     */
    private function _makeLgePkpPhar()
    {
        $buildPath = L_ROOT_PATH;
        if (preg_match('/phar:\/\/(.+\/lge.phar)/', L_ROOT_PATH, $match)) {
            // 如果当前正在执行的是phar，那么需要找到对应源代码的目录路径
            $pharPath  = $match[1];
            $buildPath = dirname($pharPath).'/../src';
            $buildPath = realpath($buildPath);
            if (false == $buildPath) {
                Lib_Console::perror("You need src of Lge framework to make lge.phar\n");
                exit();
            }
        }
        $pharPath  = $buildPath.'/../dist/lge.phar';
        $phar = new \Phar($pharPath);
        $phar->buildFromDirectory($buildPath);
        $phar->compressFiles(\Phar::GZ);
        $phar->stopBuffering();
        $phar->setStub($phar->createDefaultStub('index.php'));
    }

}
