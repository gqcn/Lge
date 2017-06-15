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
        echo Lib_Console::highlight("Done!\n", 'green');
    }

    /**
     * 安装环境包，debian
     *
     * @return void
     */
    private function _installForDebian()
    {
        $phpPackages   = 'php-fpm php-mysql php-mbstring php-mcrypt php-memcache php-memcached php-mongodb php-redis php-soap php-ssh2';
        $nginxPackages = 'nginx';
        $mysqlPackages = 'mysql-server';
        // 检查PHP包情况
        $phpBaseName   = 'php';
        if (!$this->_checkDebianPackage($phpBaseName)) {
            $phpBaseName = 'php5';
            if (!$this->_checkDebianPackage($phpBaseName)) {
                $phpBaseName = 'php7';
                if (!$this->_checkDebianPackage($phpBaseName)) {
                    echo Lib_Console::highlight("Could not find php package, exit installation!\n");
                    return;
                }
            }
        }
        // 执行PHP包安装
        $phpPackageArray = explode(' ', $phpPackages);
        foreach ($phpPackageArray as $package) {
            $name = str_replace('php-', $phpBaseName.'-', $package);
            if ($this->_checkDebianPackage($name)) {
                echo Lib_Console::highlight("Installing {$name}...\n", 'green');
                system("sudo apt-get install -y {$name}");
                echo "\n";
            }
        }
        // 安装nginx
        echo Lib_Console::highlight("Installing {$nginxPackages}...\n", 'green');
        system("sudo apt-get install -y {$nginxPackages}");
        // 安装mysql
        echo Lib_Console::highlight("Installing {$mysqlPackages}...\n", 'green');
        system("sudo apt-get install -y {$mysqlPackages}");
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
        echo Lib_Console::highlight("Installing {$phpPackages}...\n", 'green');
        system("yum install -y {$phpPackages}");
        echo Lib_Console::highlight("Installing {$nginxPackages}...\n", 'green');
        system("yum install -y {$nginxPackages}");
        echo Lib_Console::highlight("Installing {$mysqlPackages}...\n", 'green');
        system("yum install -y {$mysqlPackages}");
    }

    /**
     * 判断给定的包在apt中是否存在
     *
     * @param string $name 包名
     *
     * @return boolean
     */
    private function _checkDebianPackage($name)
    {
        $result = shell_exec("apt-cache show {$name} 2>&1 | grep 'No packages found'");
        $result = trim($result);
        return empty($result);
    }

}
