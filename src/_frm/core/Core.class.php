<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 框架执行流程引导类。
 * 主要功能，获得控制器名称以及调用方法并初始化控制器，调用控制器相关方法。
 * 注意文件路径区分大小写(必须使用绝对路径)，PHP类名称和函数名称不区分大小写。
 * 
 * @author John
 */
class Core
{
    /**
     * 执行的分站
     * @var string
     */
    public static $sys           = 'default';

    /**
     * 执行的controller
     * @var tring
     */
    public static $ctl           = 'default';
    
    /**
     * 执行的act
     * @var string
     */
    public static $act           = 'index';

    /**
     * 分站变量名称
     * @var string
     */
    public static $sysName       = '__s';

    /**
     * 控制器变量名称
     * @var string
     */
    public static $ctlName       = '__c';

    /**
     * 执行方法变量名称
     * @var string
     */
    public static $actName       = '__a';

    /**
     * 分站变量名称(CLI)
     * @var string
     */
    public static $sysNameCli    = 's';

    /**
     * 控制器变量名称(CLI)
     * @var string
     */
    public static $ctlNameCli    = 'c';

    /**
     * 执行方法变量名称(CLI)
     * @var string
     */
    public static $actNameCli    = 'a';

    /**
     * 配置目录.
     *
     * @var string
     */
    public static $cfgDir        = '';

    /**
     * 模型、模块、自定义类文件目录.
     *
     * @var string
     */
    public static $incDir        = '';
    
    /**
     * 子系统根目录.
     *
     * @var string
     */
    public static $sysDir        = '';

    /**
     * 使用缓冲处理.
     *
     * @var boolean
     */
    public static $useOb         = false;

    /**
     * 控制器文件的绝对路径.
     *
     * @var string
     */
    public static $ctlPath       = '';

    /**
     * 此次流程中执行业务处理的控制器对象.
     *
     * @var Object
     */
    public static $ctlObj        = null;
    
    /**
     * 模板引擎参数选项
     *
     * @var array
     */
    public static $tplOptions    = array();

    /**
     * 类自动加载搜索目录(根据类名中的'_'符号进行分级查找).
     *
     * @var array
     */
    public static $classSearchPaths = array();

    /**
     * 设置back trace的回调处理函数.
     *
     * @var mixed
     */
    public static $backTraceCallback = null;

    /**
     * 框架运行需要的扩展库(只需要拿其中一个函数做判断即可).
     *
     * @var array
     */
    public static $necessaryFunctions = array(
        'curl_init'    => 'CURL',
        'getimagesize' => 'GD',
    );

    /**
     * 框架运行需要的扩展库(面向对象封装的类).
     *
     * @var array
     */
    public static $necessaryClasses   = array(
        'PDO'          => 'PDO',
        'Redis'        => 'Redis',
    );

    /**
     * 框架初始化(所有变量都使用默认值，需要有改动的变量请单独赋值).
     *
     * @return void
     */
    public static function init()
    {
        // 时区设置
        if (!defined('L_DEFAULT_TIME_ZONE')) {
            define('L_DEFAULT_TIME_ZONE', 'Asia/Shanghai');
        }
        ini_set("date.timezone", L_DEFAULT_TIME_ZONE);

        // 注册页面执行结束的回调函数
        self::registerShutdownFunction(array('\Lge\Core', 'shutDownFunction'));

        // 关闭默认错误显示,使用自定义错误控制
        if (L_DEBUG === 1) {
            ini_set('display_errors', 'on');
            error_reporting(L_ERROR_LEVEL_FOR_DEBUG);
        } else {
            error_reporting(false);
        }

        // 默认控制器和执行方法判断
        if (php_sapi_name() == 'cli') {
            $option      = Lib_ConsoleOption::instance();
            self::$sys   = $option->getOption(self::$sysNameCli, self::$sys);
            self::$ctl   = $option->getOption(self::$ctlNameCli, self::$ctl);
            self::$act   = $option->getOption(self::$actNameCli, self::$act);
            self::$useOb = false;
        } else {
            self::$sys   = Lib_Request::get(self::$sysName, self::$sys);
            self::$ctl   = Lib_Request::get(self::$ctlName, self::$ctl);
            self::$act   = Lib_Request::get(self::$actName, self::$act);
            self::$useOb = true;
        }

        if (empty(self::$cfgDir)) {
            self::$cfgDir = L_ROOT_PATH.'_cfg/';
        }
        if (empty(self::$incDir)) {
            self::$incDir = L_ROOT_PATH.'_inc/';
        }
        if (empty(self::$sysDir)) {
            self::$sysDir = L_ROOT_PATH.'system/'.self::$sys.'/';
        }

        // 类自动加载搜索目录.
        self::addClassSearchPath(L_FRAME_PATH.'core/component/');
        self::addClassSearchPath(self::$incDir.'class/');
        self::addClassSearchPath(self::$incDir.'library/');

        // SESSION缓存管理(使用memcache缓存控制，不处理默认采用文件存储SESSION)
        if (defined('L_SESSION_STORAGE') && L_SESSION_STORAGE == 'memcache') {
            $config      = Config::get();
            $memcacheKey = L_SESSION_MEMCACHE_KEY;
            if (!empty($config['MemcacheServer'][$memcacheKey])) {
                $sessionSavePath = '';
                foreach ($config['MemcacheServer'][$memcacheKey] as $v) {
                    $sessionSavePath .= "{$v[0]}:{$v[1]},";
                }
                if (!empty($sessionSavePath)) {
                    $sessionSavePath = rtrim($sessionSavePath, ',');
                    ini_set("session.save_handler", "memcached");
                    ini_set("session.save_path",    $sessionSavePath);
                }
            }
        }
    }

    /**
     * 初始化app对象操作，文件名大小非敏感查找.
     *
     * @throws Exception 异常.
     *
     * @return void
     */
    public static function initController()
    {
        // 缓冲设置
        if (self::$useOb) {
            ob_start();
        }

        // 默认控制器和执行方法判断
        if (php_sapi_name() != 'cli') {
            /**
             * 子域名判断子分站
             */
            if (L_SYSTEM_BY_SUBDOMAIN === true) {
                $level     = L_SYSTEM_BY_SUBDOMAIN_LEVEL;
                $subDomain = Lib_Url::getSubdomain($level);
                if (!empty($subDomain)) {
                    $sys     = $subDomain;
                    $mapping = json_decode(L_SYSTEM_BY_SUBDOMAIN_MAPPING, true);
                    if (isset($mapping[$subDomain])) {
                        $sys = $mapping[$subDomain];
                    }
                    self::$sys    = $sys;
                    self::$sysDir = L_ROOT_PATH.'system/'.self::$sys.'/';
                }
            }
            // GET全局变量引用
            $globalGet = &Data::get('_GET');

            // 路由解析处理
            if ((php_sapi_name() != 'cli') && !empty($_SERVER['REQUEST_URI'])) {
                $query = Router::dispatch($_SERVER['REQUEST_URI']);
                if (!empty($query)) {
                    $query = ltrim($query, '/?');
                    parse_str($query, $vars);
                    if (!empty($vars)) {
                        foreach ($vars as $k => $v) {
                            // GET参数将会享有较高优先级，特别是针对URL重写特别重要
                            if (!isset($globalGet[$k])) {
                                $globalGet[$k] = $v;
                            }
                        }
                    }
                }
            }

            // 控制器和执行方法判断
            if (!empty($globalGet[self::$sysName])) {
                self::$sys = trim($globalGet[self::$sysName]);
            }
            if (!empty($globalGet[self::$ctlName])) {
                self::$ctl = trim($globalGet[self::$ctlName]);
            }
            if (!empty($globalGet[self::$actName])) {
                self::$act = trim($globalGet[self::$actName]);
            }

            // 根据请求重置分站目录
            self::$sysDir = L_ROOT_PATH.'system/'.self::$sys.'/';
        }

        // 添加分站的类搜索目录
        self::addClassSearchPath(self::$sysDir.'_inc/class/');
        self::addClassSearchPath(self::$sysDir.'_inc/library/');

        // 固定模板参数设置(模板引擎只对请求有效)
        self::$tplOptions   = array(
            'tpl_ext'       => 'tpl',
            "tpl_dir"       => L_ROOT_PATH.'system/'.self::$sys.'/template/',
            "cache_dir"     => L_ROOT_PATH.'cache/compile/'.self::$sys.'/',
            "debug"         => (L_DEBUG == 1),
            'check_update'  => (L_DEBUG == 1),
            'totally_php'   => false,
            'php_enabled'   => true,
        );

        // 包含扩展应用的加载文件
        if (file_exists(self::$sysDir.'_inc/common.inc.php')) {
            include(self::$sysDir.'_inc/common.inc.php');
        }
        // 控制器文件加载
        $ctlName   = '';
        $fileName  = '';
        $fileDir   = self::$sysDir.'_ctl/';
        $nameArray = explode('.', trim(self::$ctl));
        foreach ($nameArray as $k => $v) {
            $checkingFile = empty($nameArray[$k + 1]);
            $tempArray    = explode('-', $v);
            foreach ($tempArray as $tk => $tv) {
                $name     = ucfirst($tv);
                $ctlName .= $name;
                if ($checkingFile) {
                    $fileName .= $name;
                } else {
                    $fileDir  .= $name.'/';
                }
            }
            if (!$checkingFile) {
                $ctlName .= '_';
            }
        }
        $fileName = $fileName.'.class.php';
        $filePath = $fileDir.$fileName;
        if (file_exists($filePath)) {
            self::$ctlPath = $filePath;

            // 加载控制器文件
            include $filePath;

            // 生成类对象(控制器必须在框架的命名空间下)
            $ctlClass     = '\Lge\\Controller_'.$ctlName;
            self::$ctlObj = new $ctlClass();
            // 使用__init回调方法在控制器对象初始化之后立即调用，相当于初始化函数
            if (method_exists(self::$ctlObj, '__init')) {
                self::$ctlObj->__init();
            }

            // 使用run()方法作为对象入口函数
            self::$ctlObj->run();
        } else {
            $sys = self::$sys;
            $ctl = self::$ctl;
            if ((php_sapi_name() == 'cli')) {
                echo "Error: It could not find controller file for '{$sys}::{$ctl}'".PHP_EOL;
            } else {
                header("status: 404 Cannot find controller file for '{$sys}::{$ctl}'");
            }
            exception("exit");
        }
    }

    /**
     * 注册进程执行结束回调函数.
     *
     * @param mixed $function 回调函数.
     *
     * @return void
     */
    public static function registerShutdownFunction($function)
    {
        register_shutdown_function($function);
    }

    /**
     * 类自动加载函数.
     *
     * @param string $className 类名称.
     *
     * @return void
     */
    public static function classAutoloader($className)
    {
        $namespaceArray = explode('\\', $className);
        $classWordArray = explode('_', $namespaceArray[count($namespaceArray) - 1]);
        $firstWord      = strtolower($classWordArray[0]);
        switch ($firstWord) {
            // 控制器自动加载
            case 'controller':
                unset($classWordArray[0]);
                $dir  = self::$sysDir.'_ctl/';
                $name = implode('/', $classWordArray);
                $path = "{$dir}{$name}.class.php";
                if (file_exists($path)) {
                    require_once($path);
                } else {
                    exception("Controller '{$className}' dose not exist!");
                }
                break;

            // 模型类自动加载
            case 'model':
                unset($classWordArray[0]);
                $dir  = self::$incDir;
                if (empty($dir)) {
                    $dir = L_ROOT_PATH.'_inc/';
                }
                $name = implode('/', $classWordArray);
                $path = "{$dir}model/{$name}.class.php";
                if (file_exists($path)) {
                    require_once($path);
                } else {
                    exception("Model '{$className}' dose not exist!");
                }
                break;

            // 模块类自动加载
            case 'module':
                unset($classWordArray[0]);
                $dir  = self::$incDir;
                if (empty($dir)) {
                    $dir = L_ROOT_PATH.'_inc/';
                }
                $name = implode('/', $classWordArray);
                $path = "{$dir}module/{$name}.class.php";
                if (file_exists($path)) {
                    require_once($path);
                } else {
                    exception("Module '{$className}' dose not exist!");
                }
                break;

            // 框架类库自动加载
            case 'lib':
                unset($classWordArray[0]);
                $name = implode('/', $classWordArray);
                $path = L_FRAME_PATH.'library/'.$name.'.class.php';
                if (file_exists($path)) {
                    require_once($path);
                } else {
                    exception("Library '{$className}' dose not exist!");
                }
                break;

            // 用户自定义类自动加载
            default:
                $name     = implode('/', $classWordArray);
                $included = false;
                foreach (self::$classSearchPaths as $searchPath) {
                    $path = $searchPath.$name.'.class.php';
                    if (file_exists($path)) {
                        require_once($path);
                        $included = true;
                        break;
                    }
                }
                if (!$included) {
                    // exception("Class '{$className}' dose not exist!");
                }
                break;
        }
    }

    /**
     * 添加类自动加载搜索目录.
     *
     * @param string $searchPath 搜索目录.
     *
     * @return void
     */
    public static function addClassSearchPath($searchPath)
    {
        if ($searchPath[strlen($searchPath) - 1] != '/') {
            $searchPath .= '/';
        }
        if (!in_array($searchPath, self::$classSearchPaths)) {
            self::$classSearchPaths[] = $searchPath;
        }
    }

    /**
     * 检查所需扩展再当前运行环境的安装情况.
     *
     * @param array $functions 通过函数来判断扩展(格式: array('函数名' => '扩展名')).
     * @param array $classes   通过类名来判断扩展(格式: array('类名称' => '扩展名')).
     *
     * @return array
     */
    public static function checkExtensions($functions = array(), $classes = array())
    {
        $result    = array(
            'installed'   => array(),
            'uninstalled' => array(),
        );
        // 通过函数检查
        $functions = array_merge(self::$necessaryFunctions, $functions);
        foreach ($functions as $function => $extension) {
            if (function_exists($function)) {
                $result['installed'][]   = $extension;
            } else {
                $result['uninstalled'][] = $extension;
            }
        }
        // 通过类名检查
        $classes = array_merge(self::$necessaryClasses, $classes);
        foreach ($classes as $class => $extension) {
            if (class_exists($class)) {
                $result['installed'][]   = $extension;
            } else {
                $result['uninstalled'][] = $extension;
            }
        }
        return $result;
    }

    /**
     * 注册脚本执行结束时的回调函数，主要用于处理产生的错误信息捕获.
     *
     * @return void
     */
    public static function shutDownFunction()
    {
        if (!empty(self::$ctlObj)) {
            self::_onControllerShutDown();
        }
        /**
         * 捕获最后一次错误.
         * 由于实现了 defaultErrorHandler 方法，所有的错误都会被自动处理，因此没必要再记录最后一条错误，会造成报错重复。
         */
        /*
        $error = error_get_last();
        if (!empty($error)) {
            $errorStr  = "{$error['message']} in {$error['file']}({$error['line']})";
            $backtrace = self::_getBacktraceString();
            if (!empty($backtrace)) {
                $errorStr .= PHP_EOL.$backtrace;
            }
            Logger::log($errorStr, 'error', Logger::phpErrorNo2LoggerNo($error['type']));
        }
        */
    }

    /**
     * 用户自定义错误处理函数.
     *
     * @param integer $errorNo   错误编码.
     * @param string  $errorStr  错误提示.
     * @param string  $errorFile 错误文件地址.
     * @param integer $errorLine 错误文件所在行.
     *
     * @return void
     */
    public static function defaultErrorHandler($errorNo, $errorStr, $errorFile, $errorLine)
    {
        // 获取当前的error_reporting设置，根据配置的报错级别来写错误日志
        $errorReporting = ini_get('error_reporting');
        if (!($errorNo & $errorReporting)) {
            return false;
        }

        if (Logger::initOptions(false)) {
            $levelNo   = Logger::phpErrorNo2LoggerNo($errorNo);
            $errorStr  = "{$errorStr} in {$errorFile}({$errorLine})";
            $backtrace = self::_getBacktraceString();
            if (!empty($backtrace)) {
                $errorStr .= PHP_EOL.$backtrace;
            }
            Logger::log($errorStr, 'error', $levelNo);
            // 如果是调试模式，那么产生错误后将错误显示出来
            if (L_DEBUG == 1) {
                $levelStr = Logger::levelNo2String($levelNo);
                echo "{$levelStr}\t{$errorStr}".PHP_EOL;
            }
        } else {
            // 返回false，则PHP执行器将会调用默认的错误处理函数进行处理
            return false;
        }
    }

    /**
     * 默认异常处理回调函数.
     * 
     * @param Exception $e 异常信息.
     * 
     * @return void
     */
    public static function defaultExceptionHandler(\Exception $e)
    {
        $message = $e->getMessage();
        switch ($message) {
            // 直接退出执行
            case 'exit':
                exit();
                break;
                
            default:
                if (!headers_sent()) {
                    header("Content-Type: text/html; charset=utf-8");
                }
                try {
                    $backtrace = self::formatBacktrace($e->getTrace());
                    if (!empty($backtrace)) {
                        $message .= PHP_EOL.$backtrace;
                    }
                    Logger::log($message, 'exception', Logger::ERROR);
                } catch (\Exception $e) {
                    // 什么都不做，防止基础组件错误
                }
                // 显示换行控制
                if (php_sapi_name() != 'cli') {
                    $message = nl2br($message);
                }//
                echo $message."\n";
                exit();
                break;
        }
    }

    /**
     * 控制器执行结束.
     *
     * @return void
     */
    private static function _onControllerShutDown()
    {
        // 与控制器的__init回调方法对应的__shut回调方法，在所有业务逻辑执行完毕后自动调用。可以用于处理一些结尾工作，比如cookie或者session处理.
        if (method_exists(self::$ctlObj, '__shut')) {
            self::$ctlObj->__shut();
        }

        // 缓冲处理，先判断整个请求流程执行完毕后是否开启了缓冲区
        if (ob_get_level() && ob_get_length()) {
            $content = ob_get_contents();
            ob_end_clean();
        }

        // COOKIE输出判断，没有任何header输出的时候才输出cookie，否则会和header起冲突
        if (!headers_sent() && php_sapi_name() != 'cli') {
            Instance::cookie()->output();
        }

        // 内容输出
        if (!empty($content)) {
            $content = Router::patch($content);
            echo $content;
        }
    }

    /**
     *
     * 设置back trace 回调函数(用于做日志过滤).
     *
     * @param mixed $callback 回调函数.
     *
     * @return void
     */
    public static function setBackTraceCallback($callback)
    {
        self::$backTraceCallback = $callback;
    }

    /**
     * 获取backtrace内容.
     *
     * @return string
     */
    private static function _getBacktraceString()
    {
        $return    = '';
        $backtrace = array_slice(debug_backtrace(), 2);
        if (!empty($backtrace)) {
            $return = self::formatBacktrace($backtrace);
        }
        return $return;
    }

    /**
     * 格式化backtrace数组为字符串.
     *
     * @param array $backtrace Backtrace.
     *
     * @return string
     */
    public static function formatBacktrace(array $backtrace)
    {
        // 回调函数处理
        if (isset(self::$backTraceCallback)) {
            $callback          = null;
            $backTraceCallback = self::$backTraceCallback;
            if (is_string($backTraceCallback)) {
                $backtrace = $backTraceCallback($backtrace);
            } else if (is_array($backTraceCallback)) {
                if (is_string($backTraceCallback[0])) {
                    $backtrace = $backTraceCallback[0]::$backTraceCallback[1]($backtrace);
                } else {
                    $backtrace = $backTraceCallback[0]->$backTraceCallback[1]($backtrace);
                }
            }
        }
        // 默认内容输出
        $output = 'Trace:' . PHP_EOL;
        $index  = 0;
        foreach ($backtrace as $index => $stack) {
            $args = array();
            if (!empty($stack['args'])) {
                foreach ($stack['args'] as $argIndex => $argValue) {
                    if (is_object($argValue)) {
                        $args[$argIndex] = get_class($argValue);
                    } else if (is_array($argValue)) {
                        $args[$argIndex] = 'Array'; //substr(print_r($argValue, true), 0, 32);
                        // $args[$argIndex] = var_export($argValue, true);
                    } else if (is_string($argValue)) {
                        $args[$argIndex] = "'".substr($argValue, 0, 32).(strlen($argValue) > 32 ? '...' : '')."'";
                        // $args[$argIndex] = var_export($argValue, true);
                    } else {
                        $args[$argIndex] = var_export($argValue, true);
                    }
                }
            }
            // 格式化内容
            $output .= sprintf(
                '#%1$d %2$s(%3$d): %4$s%5$s%6$s(%7$s)',
                $index,
                (!empty($stack['file']) ? $stack['file'] : '(unknown file)'),
                (!empty($stack['line']) ? $stack['line'] : '(unknown line)'),
                (!empty($stack['class']) ? $stack['class'] : ''),
                (!empty($stack['type']) ? $stack['type'] : ''),
                $stack['function'],
                join(', ', $args)
            );
            $output .= PHP_EOL;
        }

        // 添加{main} trace
        $output .= sprintf('#%1$d {main}', $index + 1);
        return $output.PHP_EOL;
    }
}
