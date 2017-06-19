<?php
/**
 * Lge命令：Lge CLI工具安装 - mysql。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command_Install_Mysql
 *
 * @package Lge
 */
class Module_Command_Install_Mysql extends Module_Command_Install_Base
{

    /**
     * 获得实例.
     *
     * @return Module_Command_Install_Mysql
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

        $packages = 'mysql-server';
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
