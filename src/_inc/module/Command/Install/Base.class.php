<?php
/**
 * Lge命令：Lge CLI工具安装 - 基础类。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Class Module_Command_Install_Base
 *
 * @package Lge
 */
class Module_Command_Install_Base extends BaseModule
{

    /**
     * 获取当前执行安装的系统类型(debian|rhel)
     *
     * @return string
     */
    protected function _getOsType()
    {
        $type  = '';
        $issue = file_get_contents('/etc/issue');
        if (stripos($issue, 'ubuntu') !== false || stripos($issue, 'debian') !== false) {
            $type = 'debian';
        } elseif (stripos($issue, 'centos') !== false
            || stripos($issue, 'redhat') !== false
            || stripos($issue, 'rhel') !== false) {
            $type = 'rhel';
        }
        return $type;
    }

    /**
     * 检查是否lge的自动化安装脚本支持当前系统
     *
     * @return void
     */
    protected function _checkSupportSystemForAutoInstallation()
    {
        $yumPath = Lib_Console::getBinPath('yum');
        $aptPath = Lib_Console::getBinPath('apt-get');
        if (empty($yumPath) && empty($aptPath)) {
            Lib_Console::perror("Unsupport system type or linux distribution!\n");
            exit();
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
    protected function _installPackagesForDebian($packages)
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
    protected function _installPackagesForRhel($packages)
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
    protected function _checkDebianPackageAvailable($name)
    {
        $result = shell_exec("apt-cache show {$name} 2>&1 | grep 'No packages found'");
        $result = trim($result);
        return empty($result);
    }

}
