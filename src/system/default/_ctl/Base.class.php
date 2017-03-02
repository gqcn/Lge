<?php
if(!defined('PhpMe')){
    exit('Include Permission Denied!');
}

class Controller_Base extends BaseController
{
    public  $startSession = true;    // 是否开启session
    public  $sessionID    = null;    // 设置session id

    /**
     * 构造函数.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获得分页的start
     *
     * @param  int    $perPage
     * @param  string $pageName
     * @return int
     */

    public function getStart($perPage, $pageName = 'page')
    {
        $curPage = isset($this->_get[$pageName]) ? intval($this->_get[$pageName]) : 0;
        if ($curPage > 1) {
            $start = ($curPage - 1)*$perPage;
        } else {
            $start = 0;
        }
        return $start;
    }

    /**
     * 封装：MVC显示页面。
     *
     */
    public function display($tpl = 'index')
    {
        $this->assigns(array(
            'config'  => Config::get(),
            'session' => $this->_session,
        ));
        parent::display($tpl);
    }

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