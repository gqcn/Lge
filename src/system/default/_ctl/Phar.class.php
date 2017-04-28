<?php
/**
 * 这是框架的默认加载类。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Controller_Phar
 */
class Controller_Phar extends BaseController
{

    /**
     * 生成框架phar包文件.
     *
     * @return void
     */
    public function index()
    {
        $this->_makeLgePkpPhar();
        echo "Done!\n";
        exception('exit');
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
