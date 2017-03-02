<?php
if(!defined('PhpMe')){
    exit('Include Permission Denied!');
}

/**
 * 带权限判断的后台控制器基类(主要是登录状态控制).
 */
class BaseControllerAdminAuth extends BaseControllerAdmin
{
    public $startSession   = true;    // 是否开启session
    public $sessionID      = null;    // 设置session id
    public $breadCrumbs    = array(); // 面包屑列表
    public $currentMenu    = array(); // 当前菜单，可设置

    /**
     * 初始化函数.
     */
    public function __init()
    {
        // 判断用户自动登录
        if (empty($this->_session['user'])) {
            $result = Module_User::instance()->checkAutoLoginByCookie();
            if (empty($result)) {
                Lib_Redirecter::redirectExit('/login');
            }
        }
        parent::__init();
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
        $sys      = Core::$sys;
        $gid      = empty($this->_session['user']['gid']) ? 0 : $this->_session['user']['gid'];
        $userAuth = Module_UserAuth::Instance();

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
                            if (!$userAuth->checkAuthByGid(implode('/', $subMenu2['acts'])."@{$sys}", $gid)) {
                                unset($menu['subs'][$subKey]['subs'][$subKey2]);
                                continue;
                            }
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
                        if (!$userAuth->checkAuthByGid(implode('/', $subMenu['acts'])."@{$sys}", $gid)) {
                            unset($menu['subs'][$subKey]);
                            continue;
                        }
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
            } else {
                if (!$userAuth->checkAuthByGid(implode('/', $menu['acts'])."@{$sys}", $gid)) {
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
            'menus'             => $this->_getMenus(),
        ));
        parent::display($tpl);
    }



    /**
     * 删除数据.
     */
    public function delete()
    {
        if (empty($this->bindTableName)) {
            $this->addMessage('请先设置控制器绑定的数据库表名', 'error');
        } else {
            $id = Lib_Request::getGet('id', 0);
            if (!empty($id)) {
                Instance::table($this->bindTableName)->delete(array('id' => $id));
                $this->addMessage('数据删除成功', 'success');
            }
        }
        Lib_Redirecter::redirectExit();
    }

}

