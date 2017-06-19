<?php
/**
 * Linux终端命令处理类，封装常用命令功能。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 终端命令处理
 */
class Lib_Console
{
    /**
     * 终端字体颜色与英文关联数组
     *
     * @var array
     */
    public static $fontColors = array(
        'black'  => 30, // 黑色
        'red'    => 31, // 红色
        'green'  => 32, // 绿色
        'yellow' => 33, // 黄色
        'blue'   => 34, // 蓝色
        'purple' => 35, // 紫红色
        'cyan'   => 36, // 青蓝色
        'white'  => 37, // 白色
    );

    /**
     * 终端字体背景颜色与英文关联数组
     *
     * @var array
     */
    public static $backgroundColors = array(
        'black'  => 40, // 黑色
        'red'    => 41, // 红色
        'green'  => 42, // 绿色
        'yellow' => 43, // 黄色
        'blue'   => 44, // 蓝色
        'purple' => 45, // 紫红色
        'cyan'   => 46, // 青蓝色
        'white'  => 47, // 白色
    );

    /**
     * 字体样式与英文关联数组
     *
     * @var array
     */
    public static $fontStyles = array(
        'default'   => 0, // 终端默认设置
        'bold'      => 1, // 高亮显示
        'underline' => 4, // 使用下划线
        'blink'     => 5, // 闪烁
        'invert'    => 7, // 反白显示
        'hide'      => 8, // 不可见
    );

    /**
     * 返回能够将字符串按照指定颜色在shell中高亮的字符串。
     * 依靠Linux的echo -e命令实现，字符串格式：\033[显示方式;前景色;背景色m字符串\033[0m
     *
     * @param string $string          字符串
     * @param string $fontColor       文字颜色
     * @param string $fontStyle       字体样式(背景色与字体样式只能保留其一)
     * @param string $backgroundColor 字背景颜色(背景色与字体样式只能保留其一)
     *
     * @return string
     */
    public static function colorText($string, $fontColor, $fontStyle = '', $backgroundColor = '')
    {
        if (isset(self::$fontColors[$fontColor])) {
            $fontColor = self::$fontColors[$fontColor];
        }
        if (isset(self::$fontStyles[$fontStyle])) {
            $fontStyle = self::$fontStyles[$fontStyle];
            $fontStyle = "{$fontStyle};";
        }
        if (isset(self::$backgroundColors[$backgroundColor])) {
            $backgroundColor = self::$backgroundColors[$backgroundColor];
            $backgroundColor = "{$backgroundColor};";
        }
        return "\033[{$fontStyle}{$backgroundColor}{$fontColor}m{$string}\033[0m";
    }

    /**
     * 返回能够将字符串按照指定颜色在shell中高亮的字符串。
     * 依靠Linux的echo -e命令实现，字符串格式：\033[显示方式;前景色;背景色m字符串\033[0m
     *
     * @param string $string          字符串
     * @param string $fontColor       文字颜色
     * @param string $fontStyle       字体样式(背景色与字体样式只能保留其一)
     * @param string $backgroundColor 字背景颜色(背景色与字体样式只能保留其一)
     *
     * @return string
     */
    public static function highlight($string, $fontColor = 'white', $fontStyle = 'bold', $backgroundColor = '')
    {
        return self::colorText($string, $fontColor, $fontStyle, $backgroundColor);
    }

    /**
     * 执行shell命令，不直接向 标准输出(包括标准错误) 输出任何信息，只会将输出信息构造成数组返回。
     *
     * @param string $command 执行的shell命令
     *
     * @return array
     */
    public static function execCommand($command)
    {
        $descripts = array(
            // 0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w"),
        );
        $process = proc_open($command, $descripts, $pipes);
        $result  = array(
            'stdout' => stream_get_contents($pipes[1]),
            'stderr' => stream_get_contents($pipes[2]),
        );
        // fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        return $result;
    }

    /**
     * 向标准输出输出高亮的成功信息
     *
     * @param string $string 错误信息
     *
     * @return void
     */
    public static function psuccess($string)
    {
        echo self::colorText($string, 'green');
    }

    /**
     * 向标准输出输出高亮的错误信息
     *
     * @param string $string 错误信息
     *
     * @return void
     */
    public static function perror($string)
    {
        echo self::colorText($string, 'red');
    }

    /**
     * 查找可执行文件的绝对路径
     *
     * @param string $bin 可执行文件名称，例如：php,nginx,mysql...
     *
     * @return string
     */
    public static function getBinPath($bin)
    {
        return trim(shell_exec("which {$bin}"));
    }

    /**
     * 获取当前系统的Linux发布版本信息
     * array(
     *     'os' => 主版本,
     *     'distr' => 主版本,
     * )
     *
     * @return array
     */
    public function getLinuxDistributionInfo()
    {

    }

}
