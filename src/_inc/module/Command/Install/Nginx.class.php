<?php
/**
 * Lge命令：Lge CLI工具安装 - nginx。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command_Install_Nginx
 *
 * @package Lge
 */
class Module_Command_Install_Nginx extends Module_Command_Install_Base
{

    /**
     * 获得实例.
     *
     * @return Module_Command_Install_Nginx
     */
    public static function instance()
    {
        return self::_instanceInternal(__CLASS__);
    }

    /**
     * 入口函数
     *
     * @return void
     */
    public function run()
    {
        $this->_checkSupportSystemForAutoInstallation();

        $packages = 'nginx';
        switch ($this->_getOsType()) {
            case 'rhel':
                $this->_installPackagesForRhel($packages);
                break;

            case 'debian':
                $this->_installPackagesForDebian($packages);
                break;
        }
    }

}
