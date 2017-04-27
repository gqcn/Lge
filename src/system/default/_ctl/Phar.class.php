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
        $this->_makeLgeLibPhar();
        // $this->_makeLgeBinPhar();
        echo "Done!\n";
        exception('exit');
    }

    /**
     * 生成Lge框架的phar包含文件，路经为项目根目录的/lib/lge.phar
     *
     * @return void
     */
    private function _makeLgeLibPhar()
    {
        $phar = new \Phar(L_ROOT_PATH.'/../lib/lge.phar');
        $phar->buildFromDirectory(L_ROOT_PATH.'_frm');
        $phar->compressFiles(\Phar::BZ2);
        $phar->stopBuffering();
        $phar->setStub($phar->createDefaultStub('common.inc.php'));
    }

    /**
     * 生成Lge框架的phar执行文件，路经为项目根目录的/bin/lge.phar
     *
     * @return void
     */
    private function _makeLgeBinPhar()
    {
        $phar = new \Phar(L_ROOT_PATH.'/../bin/lge.phar');
        $phar->buildFromDirectory(L_ROOT_PATH);
        $phar->compressFiles(\Phar::GZ);
        $phar->stopBuffering();
        $phar->setStub($phar->createDefaultStub('index.php'));
    }

}
