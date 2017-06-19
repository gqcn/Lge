<?php
/**
 * Lge命令：Lge CLI工具安装 - php环境及主流扩展。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command_Install
 *
 * @package Lge
 */
class Module_Command_Install_Php extends Module_Command_Install_Base
{
    /**
     * 需要安装的扩展列表。
     *
     * @var array
     */
    public $necessaryExtensions = array(
        'curl',
        'gd',
        'mbstring',
        'memcached',
        'pdo_mysql',
        'redis',
        'soap',
        'ssh2',
    );

    /**
     * 获得实例.
     *
     * @return Module_Command_Install_Php
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
        // 首先检测环境是否已经安装完整
        $uninstalledExtensions = $this->_checkUninstalledExtensions();
        if (empty($uninstalledExtensions)) {
            Lib_Console::psuccess("You've already installed all necessary PHP extensions!\n");
            return;
        }

        // 可能的安装包名称，以便后续判断
        $packages  = 'php-fpm ';
        $packages .= 'php-gd php-curl php-mbstring php-mcrypt php-soap php-ssh2 ';
        $packages .= 'php-memcached php-redis ';
        $packages .= 'php-mysql php-pdo ';
        $packages  = trim($packages);
        switch ($this->_getOsType()) {
            case 'rhel':
                $this->_installPackagesForRhel($packages);
                break;

            case 'debian':
                system("apt-get update");
                // 需要依次判断PHP版本确定安装包名
                $phpVersionArray   = array('php', 'php5', 'php7', 'php7.0');
                $phpPackageArray   = explode(' ', $packages);
                foreach ($phpPackageArray as $package) {
                    foreach ($phpVersionArray as $phpVersion) {
                        $name = str_replace('php-', $phpVersion.'-', $package);
                        if ($this->_checkDebianPackageAvailable($name)) {
                            Lib_Console::psuccess("Installing {$name}\n");
                            $result = Lib_Console::execCommand("sudo apt-get install -y {$name}");
                            $result['stdout'] = rtrim($result['stdout']);
                            echo $result['stdout']."\n";
                            break;
                        }
                    }
                }
                break;
        }
        $uninstalledExtensions = $this->_checkUninstalledExtensions();
        if (!empty($uninstalledExtensions)) {
            Lib_Console::perror("Some php extensions are failed to install: {$uninstalledExtensions}\n");
        } else {
            Lib_Console::psuccess("PHP installation done!\n");
        }
    }

    /**
     * 检查是否有未安装成功的扩展，如果已经安装完整，那么返回为空字符串
     *
     * @return string
     */
    private function _checkUninstalledExtensions()
    {
        $uninstalledExtensions = '';
        $installedExtensions   = explode("\n", trim(shell_exec("php -m")));
        foreach ($this->necessaryExtensions as $extension) {
            if (!in_array($extension, $installedExtensions)) {
                $uninstalledExtensions .= "{$extension} ";
            }
        }
        return $uninstalledExtensions;
    }

}
