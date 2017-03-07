<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}
/**
 *
 * 用户组管理
 * @author john
 *
 */
class Controller_UserGroup extends BaseControllerAdminAuth
{
    /**
     * 列表管理.
     */
    public function index()
    {
        $this->assigns(array(
            'list'    => Instance::table('user_group')->getAll('*', 1, null, '`order` asc, id asc'),
            'mainTpl' => 'user-group/index',
        ));
        $this->display();
    }

    /**
     * 执行排序.
     * 
     * @return void
     */
    public function sort()
    {
        $data     = Lib_Request::getArray(array(
            'orders' => array()
        ), 'post');
        $orders   = $data['orders'];
        foreach ($orders as $gid => $order) {
            $gid   = intval($gid);
            $order = intval($order);
            Instance::table('user_group')->update(array('order' => $order), array('id=?', $gid));
        }
        $this->addMessage("用户组重新排序完成", 'success');
        Lib_Redirecter::redirectExit();
    }

    /**
     * 显示添加/修改.
     *
     * @return void
     */
    public function item()
    {
        $id   = Lib_Request::get('id',   '');
        $type = Lib_Request::get('type', '');
        $data = Lib_Request::getArray(array(
            'order'       => '',
            'name'        => '',
            'brief'       => '',
            'group_key'   => '',
            'create_time' => time(),
            'auths'       => array(),
        ));
        if ($id !== '') {
            $group = Instance::table('user_group')->getOne('*', array('id' => $id));
            $data  = array_merge($data, $group);
        }

        $auths = Module_UserAuth::Instance()->getAuthListBySystemCtl(Core::$sys);

        /**
         * 将权限按照名称长度从小到大进行排列
         */
        $usortFunction = function($a, $b) {
            $lengthA = strlen($a['name']);
            $lengthB = strlen($b['name']);
            if ($lengthA == $lengthB) {
                return 0;
            } else {
                return ($lengthA < $lengthB) ? -1 : 1;
            }
        };
        uasort($auths, $usortFunction);

        $userAuth = Module_UserAuth::Instance();

        /**
         * 功能权限树形结构
         */
        $treeArray = array();
        foreach ($auths as $k => $auth) {
            $auth['pId']     = $auth['pid'];
            // $auth['name']    = $auth['name']."({$auth['key']})";
            $auth['key']    .= '@'.$auth['system'];
            $auth['open']    = ($auth['value'] !== 'ctl');
            $auth['checked'] = $userAuth->checkAuthByGid($auth['key'], $id);
            $treeArray[]     = $auth;
        }

        /**
         * 自定义权限
         */
        $customAuths        = array();
        $definedCustomAuths = Config::get('auth');
        foreach ($definedCustomAuths as $v) {
            $checked = ($id !== '') ? $userAuth->checkAuthByGid($v[0], $id)    : false;
            $value   = ($id !== '') ? $userAuth->getAuthValueByGid($v[0], $id) : null;
            $customAuths[] = array(
                'key'     => $v[0],
                'name'    => $v[1],
                'value'   => isset($value) ? $value : $v[2],
                'checked' => $checked,
            );
        }

        // 判断是否是复制操作
        if ($type == 'copy') {
            $data = array();
        }
        $this->assigns(array(
            'data'        => $data,
            'treeJson'    => json_encode($treeArray),
            'customAuths' => $customAuths,
            'mainTpl'     => 'user-group/item',
        ));
        $this->display();
    }

    /**
     * 执行添加/修改.
     * 
     * @return void
     */
    public function edit()
    {
        $data = Lib_Request::getArray(array(
            'order'     => '',
            'name'      => '',
            'brief'     => '',
            'group_key' => '',
            'create_time' => time(),
        ), 'post');
        $id = Lib_Request::getPost('id');
        if ($id === '') {
            $id = Instance::table('user_group')->insert($data);
            if ($id) {
                $this->addMessage("用户组添加成功", 'success');
            } else {
                $this->addMessage("用户组添加失败", 'error');
            }
        } else {
            if (Instance::table('user_group')->update($data, array('id' => $id))) {
                $this->addMessage("用户组信息修改成功", 'success');
            } else {
                $this->addMessage("用户组信息修改失败", 'error');
            }
        }

        // 用户组权限
        if ($id !== '') {
            // 功能权限
            $keys  = Lib_Request::getPost('keys');
            $keys  = explode(',', $keys);
            $auths = array();
            foreach ($keys as $key) {
                $auths[] = array(
                    'gid'   => $id,
                    'key'   => $key,
                    'value' => '',
                    'brief' => '',
                );
            }
            // 自定义权限
            $customKeys   = Lib_Request::getPost('custom_auth_keys');
            $customValues = Lib_Request::getPost('custom_auth_values');
            foreach ($customKeys as $k => $v) {
                $auths[] = array(
                    'gid'   => $id,
                    'key'   => $v,
                    'value' => $customValues[$k],
                    'brief' => '',
                );
            }
            Instance::table('user_group_auth')->delete(array('gid' => $id));
            Instance::table('user_group_auth')->batchInsert($auths);
        }
        Lib_Redirecter::redirectExit();
    }
    
    /**
     * 执行删除.
     */
    public function delete()
    {
        $id = Lib_Request::get('id');
        if (!empty($id) && $id > 1) {
            if (Instance::table('user_group')->delete(array('id' => $id))) {
                $this->addMessage("用户组删除成功", 'success');
            } else {
                $this->addMessage("用户组删除失败", 'error');
            }
            Lib_Redirecter::redirectExit();
        }
    }
}

