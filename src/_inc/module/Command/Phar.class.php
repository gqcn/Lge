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
        $this->_makeLgePkpPhar();
        echo "Done!\n";
    }

    /**
     * 生成Lge框架的phar执行文件，路经为项目根目录的/bin/lge.phar
     *
     * @return void
     */
    private function _makeLgePkpPhar()
    {
        $phar = new \Phar(L_ROOT_PATH.'/../dist/lge.phar');
        $phar->buildFromDirectory(L_ROOT_PATH);
        $phar->compressFiles(\Phar::GZ);
        $phar->stopBuffering();
        $phar->setStub($phar->createDefaultStub('index.php'));
    }

}
