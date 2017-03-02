<?php
if (!defined('PhpMe')) {
	exit('Include Permission Denied!');
}

/**
 * 业务层处理APP的基类，主要执行一些基本的APP操作，特别是一些通用的APP处理。
 * 
 * @author john
*/
class BaseController extends Base
{
    public $startSession = true;    // 在控制器中是否默认开启session
    public $sessionID    = null;    // 手动设置sessionid
	public $actMap       = array(); // 参数传递的act与真实需要执行的act映射数组(主要用于缩短传递参数，并避免PHP关键字的情况)

	/**
	 * 派生类可覆盖以增强构造函数功能.
	 */
    public function __construct() {
        parent::__construct();
        // 将参数act转换为真实act
        if (isset($this->actMap[Core::$act])) {
            Core::$act = $this->actMap[Core::$act];
        }
        // 判断是否开启session
        if($this->startSession){
            $this->startSession();
        }
    }

	/**
	 * 开启SESSION。
	 * 注意如果要使用缓存的话。
	 * 派生类可覆盖以实现自定义的session处理功能.
	 */
	public function startSession() {
	    if(isset($this->sessionID)){
	        session_id($this->sessionID);
	    }
	    if (!sessionStarted()) {
	        session_start();
	    }
	}
    
    /**
	 * 对象入口函数
	 *
	 */
	public function run()
	{
	    $ctl   = Core::$ctl;
	    $act   = Core::$act;
        $error = '';
		try {
			$reflection = new ReflectionMethod($this, $act);
		} catch (Exception $e) {
            $error = "No act '{$act}' found for controller '{$ctl}'\n";
		}
        if (empty($error) && $reflection->isPublic() && $reflection->getNumberOfParameters() == 0) {
            $this->$act();
        } else {
            $error = "Act '{$act}' is not callable for controller '{$ctl}'\n";
        }
        if (!empty($error)) {
            exception($error);
        }
	}

	/**
	 * 封装：MVC页面赋值。
	 *
	 * @param array $array 模板变量数组.
     *
     * @return void
	 */
	public function assigns(array $array)
	{
        Instance::template()->assigns($array);
	}

	/**
	 * 封装：MVC页面赋值。
	 *
	 * @param string $name  变量名称.
	 * @param mixed  $value 变量内容.
     *
     * @return void
	 */
	public function assign($name, $value)
	{
        Instance::template()->assign($name, $value);
	}

	/**
	 * 封装：MVC显示页面。
	 *
     * @param string $tpl 模板名称.
     *
     * @return void
	 */
	public function display($tpl = 'index')
	{
        Instance::template()->display($tpl);
	}

	/**
	 * 模板变量赋值并且显示模板.
     *
	 * @param array  $assigns 模板变量数组.
	 * @param string $tpl     模板名称.
     *
     * @return void
	 */
	public function assignAndDisplay(array $assigns, $tpl = 'index')
	{
		Instance::template()->assigns($assigns);
		Instance::template()->display($tpl);
	}
}
