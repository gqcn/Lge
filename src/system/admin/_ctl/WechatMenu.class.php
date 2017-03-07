<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}

/**
 * 微信公众号 - 菜单管理
 */
class Controller_WechatMenu extends BaseControllerAdminAuth
{
    public $menus     = array();
    public $menuKey   = '';
    public $menuTypes = array(
        ''      => '一级菜单',
        'click' => '事件响应',
        'view'  => '跳转链接',
    );


    /**
     * 初始化，自动获取当前公众号的菜单列表.
     */
    public function __init()
    {
        $wechat        = Module_WeChat_Api::instance();
        $this->menuKey = "wechat_menu_{$wechat->appid}_{$wechat->unionid}";
        $this->menus   = Module_Setting::instance()->get($this->menuKey);
        if (empty($this->menus)) {
            $this->menus = array();
        }
        parent::__init();
    }

    /**
     * 菜单列表
     */
    public function index()
    {
        $tree       = new Lib_Tree($this->menus, array(
            'id'        => 'id',
            'parent_id' => 'pid',
            'name'      => 'name'
        ));
        $treeMenus = $tree->get_tree(0, '$spacer $name');
        $this->assigns(array(
        	'treeMenus' => $treeMenus,
        	'menuTypes' => $this->menuTypes,
        	'mainTpl'   => 'wechat/menu/index',
        ));
        $this->display();
    }

    /**
     * 异步将公众号菜单更新到本地.
     */
    public function ajaxUpdateMenu()
    {
        $wechat     = Module_WeChat_Api::instance();
        $result     = $wechat->menuList();
        $menus      = array();
        /**
         * 转换为当前系统平台识别的菜单模式，目前仅支持click和view的菜单类型。
         * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421141013&token=&lang=zh_CN
         */
        if (!empty($result) && !empty($result['selfmenu_info']['button'])) {
            foreach ($result['selfmenu_info']['button'] as $k => $v) {
                $menus[] = $this->_wechatButtonsToLocalMenus($v);
            }
        }
        /**
         * 构造树形的菜单列表.
         */
        $treeMenus = array();
        if (!empty($menus)) {
            $id  = 1;
            foreach ($menus as $menu) {
                $pid = 0;
                $menu['id']     = $id;
                $menu['pid']    = $pid;
                $treeMenus[$id] = $menu;
                if (isset($treeMenus[$id]['subs'])) {
                    unset($treeMenus[$id]['subs']);
                }
                $pid = $id;
                $id++;
                if (!empty($menu['subs'])) {
                    foreach ($menu['subs'] as $v) {
                        $v['id']        = $id;
                        $v['pid']       = $pid;
                        $treeMenus[$id] = $v;
                        if (isset($treeMenus[$id]['subs'])) {
                            unset($treeMenus[$id]['subs']);
                        }
                        $id++;
                    }

                }
            }
        }
        Module_Setting::instance()->set($this->menuKey, $treeMenus);

        $this->addMessage('菜单同步完成', 'success');
        Lib_Response::json(true);
    }

    /**
     * 异步将本地菜单部署到公众号.
     */
    public function ajaxDeployMenu()
    {
        if (empty($this->menus)) {
            $this->addMessage("菜单部署失败：请先添加菜单", 'error');
            Lib_Response::json(false);
        }
        $deployMenus = $this->_localMenusToDeployMenus();
        $api         = Module_WeChat_Api::instance();
        $result      = $api->menuCreate($api->jsonEncdoe($deployMenus));
        if (empty($result['errcode'])) {
            $this->addMessage('菜单部署完成', 'success');
        } else {
            $this->addMessage("菜单部署失败，错误代码：{$result['errcode']}， 错误信息：{$result['errmsg']}", 'error');
        }
        Lib_Response::json(true);
    }

    /**
     * 将微信返回的单个菜单按钮转换为当前系统的数据格式。
     *
     * @param array $button 微信按钮数据格式数组.
     *
     * @return array
     */
    private function _wechatButtonsToLocalMenus(array $button)
    {
        $menu = array(
            'name'  => $button['name'],
            'type'  => isset($button['type']) ? $button['type'] : '',
            'subs'  => array(),
            'value' => '',
            'order' => '99',
        );
        if (isset($button['key'])) {
            $menu['value'] = $button['key'];
        } else if (isset($button['url'])) {
            $menu['value'] = $button['url'];
        } else {
            // 其他类型的值暂不处理
        }
        // 类型名称
        if (isset($this->menuTypes[$menu['type']])) {
            $menu['typeName'] = $this->menuTypes[$menu['type']];
        } else {
            $menu['typeName'] = $menu['type'];
        }
        // 是否有子级菜单
        if (!empty($button['sub_button']) && !empty($button['sub_button']['list'])) {
            foreach ($button['sub_button']['list'] as $k => $v) {
                $menu['subs'][] = $this->_wechatButtonsToLocalMenus($v);
            }
        }
        return $menu;
    }

    /**
     * 将本地菜单数据格式转换为可以部署的菜单数组.
     *
     * @return array
     */
    private function _localMenusToDeployMenus()
    {
        $deployMenus = array(
            'button' => array()
        );
        foreach ($this->menus as $k => $v) {
            if (!empty($v['pid'])) {
                continue;
            }
            if (empty($v['type'])) {
                $subMenus   = $this->_getSubLocalMenus($v['id']);
                $deployMenu = array(
                    'name'       => $v['name'],
                    'sub_button' => array()
                );
                foreach ($subMenus as $subMenu) {
                    $deployMenu['sub_button'][] = $this->_localMenuItemToDeployMenuItem($subMenu);
                }
            } else {
                $deployMenu = $this->_localMenuItemToDeployMenuItem($v);
            }
            $deployMenus['button'][] = $deployMenu;
        }
        return $deployMenus;
    }

    /**
     * 转换本地子级菜单为可部署的子级菜单数据结构.
     *
     * @param array $menu 子级菜单项.
     *
     * @return array
     */
    private function _localMenuItemToDeployMenuItem(array $menu)
    {
        $item = array(
            'name' => $menu['name'],
            'type' => $menu['type'],
        );
        switch ($menu['type']) {
            case 'click':
                $item['key'] = $menu['value'];
                break;
            case 'view':
                $item['url'] = $menu['value'];
                break;
        }
        return $item;
    }

    /**
     * 根据id获取它的下一级子菜单列表.
     *
     * @param integer $pid 菜单ID.
     *
     * @return array
     */
    private function _getSubLocalMenus($pid)
    {
        $menus = array();
        foreach ($this->menus as $k => $v) {
            if ($v['pid'] != $pid) {
                continue;
            }
            $menus[] = $v;
        }
        return $menus;
    }

    /**
     * 菜单排序.
     * 
     * @return void
     */
    public function sort()
    {
        $orders = Lib_Request::getPost('orders', array());
        foreach ($orders as $id => $order) {
            if (isset($this->menus[$id])) {
                $this->menus[$id]['order'] = $order;
            }
        }
        $usortFunction = function($a, $b) {
            if ($a['order'] == $b['order']) {
                // 如果排序值相等，那么ID值小的排序靠前
                return ($a['id'] < $b['id']) ? -1 : 1;
            }
            return ($a['order'] < $b['order']) ? -1 : 1;
        };
        uasort($this->menus , $usortFunction);
        Module_Setting::instance()->set($this->menuKey, $this->menus);
        $this->addMessage('菜单重新排序完成', 'success');
        Lib_Redirecter::redirectExit();
    }
    
    /**
     * 执行添加/修改.
     *
     * @return void
     */
    public function edit()
    {
        $data = Lib_Request::getPostArray(array(
            'id'    => 0,
            'pid'   => 0,
            'name'  => '',
            'type'  => '',
            'value' => '',
            'order' => '99',
        ));
        if (empty($data['name'])) {
            $this->addMessage("参数不完整", 'error');
        } else if (!empty($data['type']) && empty($data['value'])) {
            $this->addMessage("参数内容不能为空", 'error');
        } else {
            // 微信菜单层级最多两级，两级以上无效
            if (!empty($data['pid'])) {
                if (isset($this->menus[$data['pid']]) && !empty($this->menus[$data['pid']]['pid'])) {
                    $this->addMessage("操作失败：微信菜单层级最多两级，无法添加三级菜单", 'error');
                    Lib_Redirecter::redirectExit();
                }
            }
            if (empty($data['id'])) {
                $data['id']     = empty($this->menus) ? 1 : max(array_keys($this->menus)) + 1;
                $successMessage = '菜单添加成功';
            } else {
                $successMessage = '菜单修改成功';
            }
            $data['typeName'] = $this->menuTypes[$data['type']];
            $this->menus[$data['id']] = $data;
            // 一级菜单最多三个，三个以上无效
            $levelOneMenuCount = 0;
            foreach ($this->menus as $v) {
                if (empty($v['pid'])) {
                    $levelOneMenuCount++;
                    // 二级菜单最多五个，五个以上无效
                    $levelTwoMenuCount = 0;
                    foreach ($this->menus as $v2) {
                        if ($v2['pid'] == $v['id']) {
                            $levelTwoMenuCount++;
                        }
                    }
                    if ($levelTwoMenuCount > 5) {
                        $this->addMessage("操作失败：微信二级菜单最多五个，无法添加更多二级菜单", 'error');
                        Lib_Redirecter::redirectExit();
                    }
                }
            }
            if ($levelOneMenuCount > 3) {
                $this->addMessage("操作失败：微信一级菜单最多三个，无法添加更多一级菜单", 'error');
                Lib_Redirecter::redirectExit();
            }
            Module_Setting::instance()->set($this->menuKey, $this->menus);
            $this->addMessage($successMessage, 'success');
        }
        Lib_Redirecter::redirectExit();
    }

    /**
     * 执行删除.
     */
    public function delete()
    {
        $id = Lib_Request::get('id');
        if (empty($id)) {
            $this->addMessage('请选择需要删除的菜单', 'error');
        } else {
            $id = intval($id);
            foreach ($this->menus as $k => $v) {
                if ($v['id'] == $id || $v['pid'] == $id) {
                    unset($this->menus[$k]);
                }
            }
            Module_Setting::instance()->set($this->menuKey, $this->menus);
            $this->addMessage('菜单删除成功', 'success');
            Lib_Redirecter::redirectExit();
        }
    }

}