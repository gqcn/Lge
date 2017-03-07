<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}

class BaseControllerAdmin extends BaseController
{
    public $menus          = array(); // 菜单列表
    public $startSession   = true;    // 是否开启session
    public $sessionID      = null;    // 设置session id
    public $breadCrumbs    = array(); // 面包屑列表
    public $currentMenu    = array(); // 当前菜单，可设置
    public $bindTableName  = '';      // 当前控制器绑定的数据库表名称

    /**
     * 初始化函数.
     */
    public function __init()
    {
        // 操作日志记录
        Module_OperationLog::instance()->checkAndAddLogToDatabase();
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
     * 获得分页内容。
     *
     * @param  int    $totalSize  总内容数
     * @param  int    $perPage    每页数
     * @param  int    $type       分页类型(可在类里面修改增加)
     * @param  string $ajaxAction 使用AJAX分页并指定AJAX操作函数 $ajaxAction
     * @return string
     */
    public function getPage($totalSize, $perPage = 20, $type = 3, $ajaxAction = null)
    {
        $page = '';
    	if($totalSize){
    		$page = new Lib_Page(array('total' => $totalSize, 'perpage' => $perPage));
    		if ($ajaxAction) {
    			$page->open_ajax($ajaxAction);
    		}
    		$page = $page->show($type);
    	}


    	$page = str_ireplace(array(
    		'</a>',
    	    '<a',
    	    '<span>',
    	    '<span class="">',
    	    '<span class="current">',
    	    '</span>',
    	), array(
    		'</a></li>',
    	    '<li><a',
    	    '<li class="disabled"><a href="#">',
    	    '<li class="disabled"><a href="#">',
    	    '<li class="active"><a href="#">',
    	    '</a></li>',
    	), $page);


    	return $page;
    }

    /**
     * 添加提示信息.
     * 
     * @param string $message 提示信息.
     * @param string $type    提示类型(success|error|info).
     * @param string $align   提示位置(right|center).
     * 
     * @return void
     */
    public function addMessage($message, $type = 'success', $align = 'right')
    {
        if ($align != 'center') {
            $align = 'right';
        }
        $this->_session['message'][] = array(
            'message' => addslashes($message),
            'align'   => $align,
            'type'    => $type,
        );
    }

    /**
     * 页面跳转.
     *
     * @param string $url 跳转连接.
     */
    public function redirectExit($url = null)
    {
        if (empty($url) && isset($_SERVER['HTTP_REFERER'])) {
            $url = $_SERVER['HTTP_REFERER'];
        }
        if (!empty($url)) {
            header("location:{$url}");
        }
        exception('exit');
    }

    /**
     * 设置面包屑.
     *
     * @param array $breadCrumbs 面包屑.
     *
     * @return void
     */
    public function setBreadCrumbs(array $breadCrumbs)
    {
        $this->breadCrumbs = $breadCrumbs;
    }

    /**
     * 设置当前菜单.
     * 
     * @param string $ctl ctl.
     * @param string $act act.
     * 
     * @return void
     */
    public function setCurrentMenu($ctl, $act)
    {
        $this->currentMenu = array(
            'ctl' => $ctl,
            'act' => $act,
        );
    }

    /**
     * 动态设置菜单.
     *
     * @param array $menus 后台显示的菜单数组，具体格式请参考menu.inc.php.
     */
    public function setMenus(array $menus)
    {
        $this->menus = $menus;
    }

    /**
     * (non-PHPdoc)
     * @see BaseApp::display()
     */
    public function display($tpl = 'index')
    {
        $this->assigns(array(
            'system'            => Core::$sys,
            'config'            => Config::get(),
            'session'           => $this->_session,
            'breadCrumbs'       => $this->breadCrumbs,
            'sysurl'            => '/system/admin/template',
            'limits'            => array(10, 30, 50, 100),
        ));

        // 是否只展示内容，不展示页面框架
        $onlyContent = Lib_Request::get('__content');
        if ($onlyContent) {
            $tpl = Instance::template()->getVar('mainTpl');
        }

        parent::display($tpl);

        // 清除提示信息
        if (!empty($this->_session['message'])) {
            $this->_session['message'] = array();
        }
    }
}

