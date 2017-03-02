<?php
if(!defined('PhpMe')){
    exit('Include Permission Denied!');
}

class Controller_Base extends BaseController
{
    public $menus          = array(); // 菜单列表
    public $startSession   = true;    // 是否开启session
    public $sessionID      = null;    // 设置session id
    public $breadCrumbs    = array(); // 面包屑列表
    public $currentMenu    = array(); // 当前菜单，可设置
    public $bindTableName  = '';      // 当前控制器绑定的数据库表名称


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
     * 获取菜单列表，并进行菜单权限判断.
     *
     * @return array
     */
    private function _getMenus()
    {
        if (empty($this->currentMenu)) {
            $ctl = Core::$ctl;
            $act = Core::$act;
        } else {
            $ctl = $this->currentMenu['ctl'];
            $act = $this->currentMenu['act'];
        }

        /**
         * 菜单最多三级，因此这里遍历的时候也只遍历三级
         */
        if (empty($this->menus)) {
            $menus = include(ROOT_PATH_EX.'/_cfg/menu.inc.php');
        } else {
            $menus = $this->menus;
        }

        // 一级菜单遍历
        foreach ($menus as $menuKey => $menu) {
            $menu['active'] = false;
            if (!empty($menu['acts']) && strcasecmp($ctl, $menu['acts'][0]) == 0
                && strcasecmp($act, $menu['acts'][1]) == 0) {
                $menu['active'] = true;
            }
            if (!empty($menu['subs'])) {
                // 二级菜单遍历
                foreach ($menu['subs'] as $subKey => $subMenu) {
                    if (!empty($subMenu['subs'])) {
                        // 三级菜单遍历(三级封顶)
                        foreach ($subMenu['subs'] as $subKey2 => $subMenu2) {
                            $subMenu2['active'] = false;
                            if (strcasecmp($ctl, $subMenu2['acts'][0]) == 0
                                && strcasecmp($act, $subMenu2['acts'][1]) == 0) {
                                $subMenu['active']  = true;
                                $subMenu2['active'] = true;
                                $menu['active']     = true;
                            }
                            $menu['subs'][$subKey]                   = $subMenu;
                            $menu['subs'][$subKey]['subs'][$subKey2] = $subMenu2;
                        }
                    } else {
                        $subMenu['active'] = false;
                        if (strcasecmp($ctl, $subMenu['acts'][0]) == 0
                            && strcasecmp($act, $subMenu['acts'][1]) == 0) {
                            $subMenu['active'] = true;
                            $menu['active']    = true;
                        }
                        $menu['subs'][$subKey] = $subMenu;
                    }

                }
                if (empty($menu['subs'])) {
                    unset($menus[$menuKey]);
                    continue;
                }
            }

            $menus[$menuKey] = $menu;
        }

        return $menus;
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
            'menus'             => $this->_getMenus(),
        ));
        // 是否只展示内容，不展示页面框架
        $onlyContent = Lib_Request::get('__content');
        if ($onlyContent) {
            $tpl = Instance::template()->getVar('mainTpl');
        }
        parent::display($tpl);
    }
}

