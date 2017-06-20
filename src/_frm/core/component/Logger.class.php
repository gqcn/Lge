<?php
/**
 * 日志操作封装类.
 *
 * @author John
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 日志操作封装类.
 *
 * @author John
 */
class Logger
{
    /**
     * 日志级别.
     */
    const EMERG   = 0x0001;  //   1 Emergency:     system is unusable
    const ALERT   = 0x0002;  //   2 Alert:         action must be taken immediately
    const CRIT    = 0x0004;  //   4 Critical:      critical conditions
    const ERROR   = 0x0008;  //   8 Error:         error conditions
    const WARNING = 0x0010;  //  16 Warning:       warning conditions
    const NOTICE  = 0x0020;  //  32 Notice:        normal but significant condition
    const DEBUG   = 0x0040;  //  64 Debug:         debug messages
    const INFO    = 0x0080;  // 128 Informational: informational messages
    const DATA    = 0x0100;  // 256 Data:          just data

    /**
     * 日志的记录级别(当级别满足以下条件时记录，不满足则过滤)
     */
    const LOG_LEVEL_ALL     = 0x0FFF; // self::EMERG | self::ALERT | self::CRIT | self::ERROR | self::WARNING | self::NOTICE | self::INFO | self::DEBUG;
    const LOG_LEVEL_PROD    = 0x0F8F; // LOG_LEVEL_ALL ^ self::WARNING ^ self::NOTICE ^ self::DEBUG;
    const LOG_LEVEL_STAGING = self::LOG_LEVEL_PROD;
    const LOG_LEVEL_DEV     = self::LOG_LEVEL_ALL;
    const LOG_LEVEL_NONE    = 0;

    /**
     * 日志适配选项.
     */
    const ADAPTER_FILE     = 0x0001;
    const ADAPTER_DATABASE = 0x0002;

    /**
     * PHP内置错误对应Logger错误编码.
     *
     * @var array
     */
    private static $_phpErrorNoMapping = array(
        E_ERROR             => self::ERROR,
        E_WARNING           => self::WARNING,
        E_PARSE             => self::ERROR,
        E_NOTICE            => self::NOTICE,
        E_CORE_ERROR        => self::ERROR,
        E_CORE_WARNING      => self::WARNING,
        E_COMPILE_ERROR     => self::ERROR,
        E_COMPILE_WARNING   => self::WARNING,
        E_USER_ERROR        => self::ERROR,
        E_USER_WARNING      => self::WARNING,
        E_USER_NOTICE       => self::NOTICE,
        E_STRICT            => self::WARNING,
        E_RECOVERABLE_ERROR => self::ERROR,
        E_DEPRECATED        => self::NOTICE,
        E_USER_DEPRECATED   => self::NOTICE,
    );

    /**
     * 日志过滤回调函数.
     *
     * @var function
     */
    private static $_filterCallback = null;

    /**
     * 日志配置选项.
     *
     * @var array
     */
    private static $_options = array();

    /**
     * 缓存的日志内容数组(缓存日志后一次性写入用以降低IO操作).
     *
     * @var array
     */
    private static $_messages = array();

    /**
     * 设置日志记录的目录路径.
     *
     * @param array $options 日志配置项.
     *
     * @return void
     */
    public static function setOptions(array $options)
    {
        self::$_options = $options;
        Core::registerShutdownFunction(array('\Lge\Logger', 'flushLogCache'));
    }

    /**
     * 设置日志文件的存放路径。
     *
     * @param string $path 日志路径
     *
     * @return void
     */
    public static function setAdapterFileLogPath($path)
    {
        self::initOptions(false);
        self::$_options['adapter_file_log_path'] = $path;
    }

    /**
     * 设置日志内容回调函数(用以过滤日志内容).
     *
     * @param mixed $callback 回调函数.
     *
     * @return void
     */
    public static function setFilterCallback($callback)
    {
        self::$_filterCallback = $callback;
    }

    /**
     * PHP内置错误转为Logger错误可识别的编码.
     *
     * @param integer $phpErrorNo PHP错误码.
     *
     * @return integer
     */
    public static function phpErrorNo2LoggerNo($phpErrorNo)
    {
        // 当前PHP错误码转换成Logger错误码
        $level = isset(self::$_phpErrorNoMapping[$phpErrorNo]) ? self::$_phpErrorNoMapping[$phpErrorNo] : 0;
        // 是否记录所有日志信息
        $logall = empty(self::$_options['error_logging_levels']) || in_array(self::$_options['error_logging_levels'], array(self::LOG_LEVEL_ALL, self::LOG_LEVEL_DEV));
        if (!empty($level) && !$logall) {
            // 强制使用 LOG_LEVEL_PROD 来处理PHP错误信息
            if (!($level & self::LOG_LEVEL_PROD)) {
                $level = 0;
            }
        }
        return $level;
    }

    /**
     * 写日志.
     *
     * @param string  $message  日志内容.
     * @param string  $category 日志类别.
     * @param integer $level    日志级别.
     * @param integer $adapter  适配选项.
     * @param boolean $cache    是否缓存执行.
     *
     * @return void
     */
    public static function log($message,
                               $category = '',
                               $level    = Logger::INFO,
                               $adapter  = null,
                               $cache    = null)
    {
        if (!self::initOptions(false)) {
            return;
        }
        if (!isset($adapter)) {
            $adapter = self::$_options['adapter'];
        }
        if (!isset($cache)) {
            $cache   = self::$_options['cache'];
        }
        if (empty($cache)) {
            // 文件
            if (self::ADAPTER_FILE & $adapter) {
                self::logToFile($message, $category, $level, $cache);
            }
            // 数据库
            if (self::ADAPTER_DATABASE & $adapter) {
                self::logToDatabase($message, $category, $level, $cache);
            }
        } else {
            self::_cacheLog($message, $category, $level);
        }
    }

    /**
     * 将缓存内容执行写入操作，并清空变量.
     *
     * @return void
     */
    public static function flushLogCache()
    {
        if (!empty(self::$_messages)) {
            // 文件
            if (self::ADAPTER_FILE & self::$_options['adapter']) {
                foreach (self::$_messages as $v) {
                    self::logToFile($v['message'], $v['category'], $v['level'], false, $v['create_time']);
                }
            }

            // 数据库
            if (self::ADAPTER_DATABASE & self::$_options['adapter']) {
                $list = array();
                $host = Lib_IpHandler::getServerIp();
                foreach (self::$_messages as $v) {
                    $v['host']    = $host;
                    $v['message'] = self::_filterLog($v['message']);
                    $list[]       = $v;
                }
                $db = Instance::database(self::$_options['setting']['database']['node']);
                $db->batchInsert(self::$_options['setting']['database']['table'], $list);
            }

            self::$_messages = array();
        }
    }

    /**
     * 日志写入数据库(固定数据库格式，不建议使用).
     *
     * @param string  $message  日志内容.
     * @param string  $category 日志类别.
     * @param integer $level    日志级别.
     * @param boolean $cache    是否缓存执行.
     * @param integer $time     日志时间.
     *
     * @return void
     */
    public static function logToDatabase($message,
                                         $category = 'default',
                                         $level    = Logger::INFO,
                                         $cache    = false,
                                         $time     = null
    )
    {
        if (!self::initOptions(false)) {
            return;
        }
        if (!isset($time)) {
            $time = time();
        }
        $cache = isset($cache) ? $cache : self::$_options['cache'];
        if (!empty($cache)) {
            self::_cacheLog($message, $category, $level);
            return;
        }
        $data = array(
            'level'       => $level,
            'category'    => $category,
            'message'     => self::_filterLog($message),
            'host'        => Lib_IpHandler::getServerIp(),
            'create_time' => $time,
        );

        $db = Instance::database(self::$_options['setting']['database']['node']);
        $db->setHalt(false);
        $db->insert(self::$_options['setting']['database']['table'], $data);
    }

    /**
     * 按照标准的日志格式写入一条日志.
     *
     * @param string  $message  日志信息.
     * @param string  $category 日志目录.
     * @param integer $level    日志级别.
     * @param boolean $cache    是否缓存.
     * @param integer $time     日志时间.
     *
     * @return void
     */
    public static function logToFile($message,
                                     $category = '',
                                     $level    = Logger::INFO,
                                     $cache    = false,
                                     $time     = null
    )
    {
        if (!self::initOptions(false)) {
            return;
        }
        if (!isset($time)) {
            $time = time();
        }

        $logDirPath = self::$_options['adapter_file_log_path'];
        if (empty($logDirPath)) {
            exception("Log dir path not set!");
        } else {
            $cache = isset($cache) ? $cache : self::$_options['cache'];
            if (!empty($cache)) {
                self::_cacheLog($message, $category, $level);
                return;
            }
            $levelStr = self::levelNo2String($level);
            if (!empty($category)) {
                $path = $logDirPath.'/'.$category;
            } else {
                $path = $logDirPath;
            }
            if (!file_exists($path)) {
                if (empty(@mkdir($path, 0777, true))) {
                    die('Log folder not writable: '.$path);
                    exit();
                }
                chmod($path, 0777);
            }
            $fileName = date('Ymd', $time);
            $filePath = $path . "/{$fileName}.log";
            $message  = self::_filterLog($message);
            $datetime = date('Y-m-d H:i:s', $time);
            $message  = empty($levelStr) ? "{$datetime}\t{$message}".PHP_EOL : "{$datetime}\t{$levelStr}\t{$message}".PHP_EOL;
            // 初始化文件的时候改变文件的权限，以便每个用户都可以写入
            if (!file_exists($filePath)) {
                touch($filePath);
                chmod($filePath, 0777);
            }
            // 写入内容
            file_put_contents($filePath, $message, FILE_APPEND);
        }
    }

    /**
     * 将错误码转换为可识别的字符串.
     *
     * @param integer $level 错误码.
     *
     * @return string
     * @throws \Exception 异常
     */
    public static function levelNo2String($level)
    {
        $levelStr = '';
        switch ($level) {
            case self::EMERG:
                $levelStr = '[emergency]';
                break;
            case self::ALERT:
                $levelStr = '[alert]';
                break;
            case self::CRIT:
                $levelStr = '[critical]';
                break;
            case self::ERROR:
                $levelStr = '[error]';
                break;
            case self::WARNING:
                $levelStr = '[warning]';
                break;
            case self::NOTICE:
                $levelStr = '[notice]';
                break;
            case self::INFO:
                $levelStr = '[info]';
                break;
            case self::DEBUG:
                $levelStr = '[debug]';
                break;
            case self::DATA:
                $levelStr = '';
                break;
            default:
                exception("Invalid logging level {$level}!");
                break;
        }
        return $levelStr;
    }

    /**
     * 缓存日志内容.
     *
     * @param string  $message  日志内容.
     * @param string  $category 日志类别.
     * @param integer $level    日志级别.
     *
     * @return void
     */
    private static function _cacheLog($message, $category, $level)
    {
        $message = array(
            'level'       => $level,
            'category'    => $category,
            'message'     => $message,
            'create_time' => time(),
        );
        $key = sha1(json_encode($message));
        self::$_messages[$key] = $message;
    }

    /**
     * 过滤日志内容.
     *
     * @param string $content 日志内容.
     *
     * @return mixed
     */
    private static function _filterLog($content)
    {
        if (isset(self::$_filterCallback)) {
            $callback       = null;
            $filterCallback = self::$_filterCallback;
            if (is_string($filterCallback)) {
                $content = $filterCallback($content);
            } elseif (is_array($filterCallback)) {
                if (is_string($filterCallback[0])) {
                    $content = $filterCallback[0]::$filterCallback[1]($content);
                } else {
                    $content = $filterCallback[0]->$filterCallback[1]($content);
                }
            }
        }
        return $content;
    }
    
    /**
     * 检查Logger是否有配置.
     *
     * @param boolean|true $throwException 当配置文件中没有日志项配置时是否抛异常.
     *
     * @return boolean
     * @throws \Exception 异常
     */
    public static function initOptions($throwException = true)
    {
        $result = false;
        if (empty(self::$_options)) {
            $loggerConfig = Config::getValue('Logger');
            if (!empty($loggerConfig)) {
                if (!empty($loggerConfig['enabled'])) {
                    self::setOptions($loggerConfig);
                    $result = true;
                }
            } elseif ($throwException) {
                exception('No configuration for Logger is found!');
            }
        } else {
            $result = true;
        }
        return $result;
    }

}
