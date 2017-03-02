<?php
if(!defined('PhpMe')){
    exit('Include Permission Denied!');
}
/**
 * 操作日志管理模块。
 *
 */
class Module_OperationLog extends BaseModule
{
    /**
     * 敏感单词数组，用于判断方法中是否带有敏感词汇，如果是，那么该方法的操作会记录到操作日志中.
     *
     * @var array
     */
    public $sensitiveWords = array(
        'delete'
    );

    /**
     * 获得实例.
     *
     * @return Module_OperationLog
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * 检查并添加当前请求到操作日志中.
     *
     * @return void
     */
    public function checkAndAddLogToDatabase()
    {
        if ($this->_checkCurrentRequestForLog()) {
            $sys = Core::$sys;
            $ctl = Core::$ctl;
            $act = Core::$act;
            $checkKey = strtolower("{$sys}::{$ctl}::{$act}");
            // 可自定义判断某种操作，根据操作不同写入的日志内容会不同
            switch ($checkKey) {
                default:
                    $config   = Config::get();
                    $sysBrief = isset($config['System'][$sys]['name']) ? $config['System'][$sys]['name'] : '';
                    $actBrief = Module_UserAuth::instance()->getActBriefByAct(Core::$act, Core::$ctlPath);
                    $data     = array(
                        'uid'     => isset($this->_session['user']['uid']) ? $this->_session['user']['uid'] : 0,
                        'system'  => strtolower(Core::$sys),
                        'ctl'     => strtolower(Core::$ctl),
                        'act'     => strtolower(Core::$act),
                        'ip'      => Lib_IpHandler::getClientIp(),
                        'brief'   => empty($sysBrief) ? $actBrief : "{$sysBrief}::{$actBrief}",
                        'content' => json_encode(array(
                            '_get'     => $this->_get,
                            '_post'    => $this->_post,
                            '_cookie'  => $this->_cookie,
                            '_session' => $this->_session,
                        )),
                        'create_time' => time(),
                    );
                    Instance::table('operation_log')->insert($data);
                    break;
            }
        }
    }

    /**
     * 判断当前的请求是否应当记录到操作日志中.
     *
     * @return boolean
     */
    private function _checkCurrentRequestForLog()
    {
        $result = false;
        if (Lib_Request::isRequestMethodPost()) {
            $result = true;
        } else {
            foreach ($this->sensitiveWords as $word) {
                if (stripos(Core::$act, $word) !== false) {
                    $result = true;
                }
            }
        }
        return $result;
    }

}
