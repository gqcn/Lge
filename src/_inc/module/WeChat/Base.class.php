<?php
/**
 * WeChat模块基类.
 *
 * @author john
 */
class Module_WeChat_Base extends BaseModule
{
    /**
     * 按照标准的日志格式写入一条日志.
     *
     * @param string  $message  日志信息.
     * @param string  $category 日志目录(分类).
     * @param integer $level    日志级别(info:1, warning:2, error:3).
     *
     * @return void
     */
    public function log($message, $category = 'default', $level = Logger::INFO) {
        Logger::log($message, $category, $level);
    }
}
