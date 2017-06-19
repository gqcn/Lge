<?php
/**
 * Lge命令：Lge CLI工具安装。
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
class Module_Command_Install extends BaseModule
{

    /**
     * 获得实例.
     *
     * @return Module_Command_Install
     */
    public static function instance()
    {
        return self::_instanceInternal(__CLASS__);
    }

    /**
     * 将lge.phar安装到系统可执行文件目录
     *
     * @return void
     */
    public function run()
    {
        $id = shell_exec("id -u");
        $id = trim($id);
        if ($id != "0") {
            Lib_Console::perror("This script must be running as root\n");
            exit();
        }
        $option = Lib_ConsoleOption::instance()->getOption('o', 'lge');
        switch ($option) {
            case 'lge':
                $this->_installLge();
                break;

            case 'php':
                $this->_installPhp();
                break;

//            case 'nginx':
//                $this->_installNginx();
//                break;
//
//            case 'mysql':
//                $this->_installMysql();
//                break;
//
//            case 'lnmp':
//                $this->_installLnmp();
//                break;

            default:
                Lib_Console::perror("Unknown install option:{$option}\n");
                break;
        }

    }

    /**
     * 安装lge命令
     *
     * @return void
     */
    private function _installLge()
    {
        if (!empty(Lib_Console::getBinPath('lge'))) {
            Lib_Console::psuccess("You've already installed lge!\n");
            exit();
        }
        $phpBinaryPath = Lib_Console::getBinPath('php');
        if (empty($phpBinaryPath)) {
            Lib_Console::perror("PHP binary not found, please install php cli first!\n");
        }

        if (preg_match('/phar:\/\/(.+\/lge.phar)/', L_ROOT_PATH, $match)) {
            $pharPath   = $match[1];
            $binaryDir  = '/usr/bin/';
            $binaryPath = $binaryDir.'lge';
            $content    = "#!/bin/bash\nphp {$pharPath} \$*\n";
            if (is_writable($binaryDir)) {
                file_put_contents($binaryPath, $content);
                @chmod($binaryPath, 0777);
                Lib_Console::psuccess("Lge binary installation done!\n");
            } else {
                Lib_Console::perror("Lge binary installation failed, please make sure you have permission to make this.\n");
            }
        } else {
            Lib_Console::perror("It should be running in phar!");
        }
    }

    /**
     * 安装lnmp环境
     *
     * @return void
     */
    private function _installLnmp()
    {
        $this->_checkSupportSystemForAutoInstallation();

        $this->_installPhp();
        $this->_installNginx();
        $this->_installMysql();
    }

    /**
     * 检查是否lge的自动化安装脚本支持当前系统
     *
     * @return void
     */
    private function _checkSupportSystemForAutoInstallation()
    {
        $yumPath = Lib_Console::getBinPath('yum');
        $aptPath = Lib_Console::getBinPath('apt-get');
        if (empty($yumPath) || empty($aptPath)) {
            Lib_Console::perror("Unsupport system type or linux distribution!\n");
            exit();
        }
    }

    /**
     * 获取当前执行安装的系统类型(debian|rhel)
     *
     * @return string
     */
    private function _getOsType()
    {
        $type  = '';
        $issue = file_get_contents('/etc/issue');
        if (   stripos($issue, 'ubuntu') !== false
            || stripos($issue, 'debian') !== false) {
            $type = 'debian';
        } elseif (
               stripos($issue, 'centos') !== false
            || stripos($issue, 'redhat') !== false
            || stripos($issue, 'rhel')   !== false) {
            $type = 'rhel';
        }
        return $type;
    }

    /**
     * 安装PHP运行环境以及主流扩展
     *
     * @return void
     */
    private function _installPhp()
    {
        $packages  = 'php-fpm php-mysql php-mbstring php-mcrypt ';
        $packages .= 'php-memcache php-memcached php-mongodb php-redis php-soap php-ssh2';
        switch ($this->_getOsType()) {
            case 'rhel':
                $this->_installPackagesForRhel($packages);
                break;

            case 'debian':
                // 需要依次判断PHP版本确定安装包名
                $phpVersionArray   = array('php', 'php5', 'php7', 'php7.0');
                $phpPackageArray   = explode(' ', $packages);
                $failedPackages    = '';
                foreach ($phpPackageArray as $package) {
                    $installed = false;
                    foreach ($phpVersionArray as $phpVersion) {
                        $name = str_replace('php-', $phpVersion.'-', $package);
                        if ($this->_checkDebianPackageAvailable($name)) {
                            Lib_Console::psuccess("Installing {$name}\n");
                            $result = Lib_Console::execCommand("sudo apt-get install -y {$name}");
                            if (empty($result['stderr'])) {
                                $installed = true;
                                echo $result['stdout'];
                            } else {
                                Lib_Console::perror($result['stderr']."\n");
                            }
                            break;
                        }
                    }
                    if (!$installed) {
                        $failedPackages .= "{$package} ";
                    }
                }
                // 显示安装失败的安装包
                if (empty($failedPackages)) {
                    Lib_Console::perror("Some packages are failed to install: {$failedPackages}\n");
                }
                break;
        }
    }

    /**
     * 安装Nginx
     *
     * @return void
     */
    private function _installNginx()
    {
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

    /**
     * 安装MySQL
     *
     * @return void
     */
    private function _installMysql()
    {
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

    /**
     * 安装软件包，debian
     * 当全部安装完成则返回true,部分或者全部安装失败，返回false
     *
     * @param string $packages 安装包名称，多个以空格分隔
     *
     * @return boolean
     */
    private function _installPackagesForDebian($packages)
    {
        $result         = true;
        $packageArray   = explode(' ', trim($packages));
        $failedPackages = '';
        foreach ($packageArray as $package) {
            $package = trim($package);
            if (empty($package)) {
                continue;
            }
            if ($this->_checkDebianPackageAvailable($package)) {
                Lib_Console::psuccess("Installing {$package}\n");
                $result = Lib_Console::execCommand("sudo apt-get install -y {$package}");
                if (empty($result['stderr'])) {
                    echo $result['stdout'];
                } else {
                    $result          = false;
                    $failedPackages .= "{$package} ";
                    Lib_Console::perror($result['stderr']."\n");
                }
                break;
            } else {
                $result          = false;
                $failedPackages .= "{$package} ";
            }
        }
        // 显示安装失败的安装包
        if (empty($failedPackages)) {
            Lib_Console::perror("Some packages are failed to install: {$failedPackages}\n");
        }
        return $result;
    }

    /**
     * 安装软件包，rhel
     * 当全部安装完成则返回true,部分或者全部安装失败，返回false
     *
     * @param string $packages 安装包名称，多个以空格分隔
     *
     * @return boolean
     */
    private function _installPackagesForRhel($packages)
    {
        system("yum install -y {$packages}");
        return true;
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
