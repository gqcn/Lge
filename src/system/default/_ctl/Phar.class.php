<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}

/**
 * 这是框架的默认加载类。
 * @package Lge
 */
class Controller_Phar extends BaseController
{
    /**
     * 生成框架phar包，目录为/lib/lge.phar.
     */
    public function index() {
        $phar = new \Phar('lge.phar');
        $phar->buildFromDirectory(L_ROOT_PATH.'_frm');
        $phar->compressFiles(\Phar::GZ);
        $phar->stopBuffering();
        $phar->setStub($phar->createDefaultStub('common.inc.php'));
        echo "Done!\n";
        exception('exit');
    }
}
