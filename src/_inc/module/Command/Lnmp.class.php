<?php
/**
 * Lge命令：安装LNMP运行环境。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command_Lnmp
 *
 * @package Lge
 */
class Module_Command_Lnmp extends BaseModule
{

    /**
     * 获得实例.
     *
     * @return Module_Command_Lnmp
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
        if (Lib_Console::getBinPath('yum')) {
            $this->_installForRhel();
        } elseif (Lib_Console::getBinPath('apt-get')) {
            $this->_installForDebian();
        } else {
            echo "Unsupport linux distribution!\n";
            return;
        }
    }

    /**
     * 安装环境包，debian
     *
     * @return void
     */
    private function _installForDebian()
    {
        $phpPackages    = 'php-fpm php-mysql php-mbstring php-mcrypt php-memcache php-memcached php-mongodb php-redis php-soap php-ssh2';
        $nginxPackages  = 'nginx';
        $mysqlPackages  = 'mysql-server';
        $failedPackages = '';
        // 执行PHP包安装(依次判断PHP版本)
        $phpVersionArray   = array('php', 'php5', 'php7', 'php7.0');
        $phpPackageArray   = explode(' ', $phpPackages);

        foreach ($phpPackageArray as $package) {
            $installed = false;
            foreach ($phpVersionArray as $phpVersion) {
                $name = str_replace('php-', $phpVersion.'-', $package);
                if ($this->_checkDebianPackageAvailable($name)) {
                    echo Lib_Console::colorText("Installing {$name}\n", 'green');
                    $result = Lib_Console::execCommand("sudo apt-get install -y {$name}");
                    if (empty($result['stderr'])) {
                        $installed = true;
                        echo $result['stdout'];
                    } else {
                        echo Lib_Console::colorText($result['stderr']."\n", 'red');
                    }
                    break;
                }
            }
            if (!$installed) {
                $failedPackages .= "{$package} ";
            }
        }

        // 安装nginx
        echo Lib_Console::colorText("Installing {$nginxPackages}\n", 'green');
        $result = Lib_Console::execCommand("sudo apt-get install -y {$nginxPackages}");
        if (empty($result['stderr'])) {
            echo $result['stdout'];
        } else {
            $failedPackages .= "{$nginxPackages} ";
            echo Lib_Console::colorText($result['stderr']."\n", 'red');
        }

        // 安装mysql
        echo Lib_Console::colorText("Installing {$mysqlPackages}\n", 'green');
        $result = Lib_Console::execCommand("sudo apt-get install -y {$mysqlPackages}");
        if (empty($result['stderr'])) {
            echo $result['stdout'];
        } else {
            $failedPackages .= "{$mysqlPackages} ";
            echo Lib_Console::colorText($result['stderr']."\n", 'red');
        }

        // 显示安装失败的安装包
        if (empty($failedPackages)) {
            echo Lib_Console::colorText("Done!\n", 'green');
        } else {
            echo Lib_Console::highlight("Some packages are failed to install: {$failedPackages}\n", 'red');
        }
    }

    /**
     * 安装环境包，rhel
     *
     * @return void
     */
    private function _installForRhel()
    {
        $phpPackages   = 'php php-fpm php-mysql php-mbstring php-mcrypt php-memcache php-memcached php-mongodb php-redis php-soap php-ssh2';
        $nginxPackages = 'nginx';
        $mysqlPackages = 'mysql-server';
        echo Lib_Console::colorText("Installing {$phpPackages}...\n", 'green');
        system("yum install -y {$phpPackages}");
        echo Lib_Console::colorText("Installing {$nginxPackages}...\n", 'green');
        system("yum install -y {$nginxPackages}");
        echo Lib_Console::colorText("Installing {$mysqlPackages}...\n", 'green');
        system("yum install -y {$mysqlPackages}");
        echo Lib_Console::colorText("Done!\n", 'green');
    }

    /**
     * 判断给定的包在apt中是否存在
     *
     * @param string $name 包名
     *
     * @return boolean
     */
    private function _checkDebianPackageAvailable($name)
    {
        $result = shell_exec("apt-cache show {$name} 2>&1 | grep 'No packages found'");
        $result = trim($result);
        return empty($result);
    }

}
