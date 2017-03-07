<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}

/**
 * 用户管理
 */
class Controller_User extends BaseControllerAdminAuth
{
    /**
     * 列表管理.
     */
    public function index()
    {
        $data = Lib_Request::getArray(array(
            'limit'  => 10,
            'from'   => '',
            'uid'    => 0,
            'gid'    => '',
            'key'    => '',
        ));
        // $condition = "passport IS NOT NULL";
        $condition = "1";
        if (!empty($data['uid'])) {
            $uid        = intval($data['uid']);
            $condition .= " AND `uid`={$uid}";
        }
        if (!empty($data['from'])) {
            $condition .= " AND `from`='{$data['from']}'";
        }
        if ($data['gid'] !== '') {
            $gid        = intval($data['gid']);
            $condition .= " AND `gid`={$gid}";
        }
        if (!empty($data['key'])) {
            $condition .= " AND `nickname` LIKE '%{$data['key']}%'";
        }
        $limit          = $data['limit'] > 100 ? 100 : $data['limit'];
        $start          = $this->getStart($limit);
        $list           = Instance::table('user')->getAll("*", $condition, null, "`uid` asc", $start, $limit);
        $count          = Instance::table('user')->getCount($condition);
        $groups         = Instance::table('user_group')->getAll("*", 1, null, '`order` asc,id asc', 0, 0, 'id');
        $userModule     = Module_User::instance();
        foreach ($list as $k => $v) {
            $v['from_name']   = isset($userModule->fromArray[$v['from']]) ? $userModule->fromArray[$v['from']] : '';
            $v['group_name']  = isset($groups[$v['gid']]) ? $groups[$v['gid']]['name'] : '';
            $list[$k]         = $v;
        }
        $this->assigns(array(
            'list'     => $list,
            'page'     => $this->getPage($count, $limit),
            'froms'    => $userModule->fromArray,
            'groups'   => $groups,
            'mainTpl'  => 'user/index'
        ));
        $this->display();
    }

    /**
     * 批量操作.
     */
    public function batch()
    {
        $uids = Lib_Request::getPost('batch_uids');
        $type = Lib_Request::getPost('batch_type');
        switch ($type) {
            case 'group':
                $gid = Lib_Request::getPost('batch_gid');
                Instance::table('user')->update(
                    array('gid' => $gid),
                    "gid != 1 and uid in({$uids})"
                );
                $this->addMessage('用户组批量修改成功', 'success');
                break;

            case 'status':
                $status = Lib_Request::getPost('batch_status');
                Instance::table('user')->update(
                    array('status' => $status),
                    "gid != 1 and uid in({$uids})"
                );
                $this->addMessage('用户状态批量修改成功', 'success');
                break;

            default:
                $this->addMessage('无法识别的批量操作', 'error');
                break;
        }
        Lib_Redirecter::redirectExit();
    }

    /**
     * 显示添加/修改
     */
    public function item()
    {
        $uid      = Lib_Request::get('uid',     0);
        $groupKey = Lib_Request::get('group_key');
        $data = array(
            'uid'             => 0,
            'gid'             => 0,
            'passport'        => '',
            'password'        => '',
            'nickname'        => '',
            'gender'          => 1,
            'status'          => 1,
            'email'           => '',
            'mobile'          => '',
            'telephone'       => '',
            'qq'              => '',
            'address'         => '',
            'brief'           => '',
            'avatar'          => '',
            'city'            => '',
            'create_time'     => time(),
        );
        if (!empty($groupKey)) {
            $data['gid'] = Instance::table('user_group')->getValue('id', array('group_key' => $groupKey));
        }

        $userGroups = Instance::table('user_group')->getAll('*', "group_key!='super_admin'", null, '`order` asc,id asc', 0, 0, 'id');
        if (!empty($uid)) {
            $userInfo  = Instance::table('user')->getOne("*", array('uid' => $uid));
            if (!empty($userInfo)) {
                $data = array_merge($data, $userInfo);
            }
        }
        $city   = Instance::table('geoinfo_city')->getAll('name', 'province_id > 20');
        $cities = array();
        foreach ($city as $v) {
            $cities[] = $v['name'];
        }
        $bigCity =array(
            '北京市',
            '天津市',
            '上海市',
            '重庆市'
        );
        foreach($bigCity as $v){
            $cities[] = $v;
        }

        $superAdmin = Instance::table('user_group')->getValue('id', array('group_key' => 'super_admin'));
        $this->assigns(array(
            'data'       => $data,
            'city'       => $cities,
            'groups'     => $userGroups,
            'superAdmin' => $superAdmin,
            'mainTpl'    => 'user/item'
        ));
        $this->display();
    }

    /**
     *
     * 执行添加/修改
     * @return void
     */
    public function edit()
    {
        $data   = Lib_Request::getArray(array(
            'puid'           => $this->_session['user']['uid'],
            'gid'            => 0,
            'passport'       => '',
            'password'       => '',
            'nickname'       => '',
            'gender'         => 1,
            'status'         => 1,
            'email'          => '',
            'mobile'         => '',
            'telephone'      => '',
            'qq'             => '',
            'address'        => '',
            'brief'          => '',
            'avatar'         => '',
            'city'           => '',
            'create_time'  => time(),
        ), 'post');
        if (empty($data['passport']) || empty($data['password'])) {
            $this->addMessage('帐号或密码不能为空', 'error');
        } else {
            $uid = Lib_Request::getPost('uid');
            // 修改账号时判断该用户是否已经存在
            $isAlreadyExist = Instance::table('user')->getCount("uid != {$uid} and passport='{$data['passport']}'");
            if (!empty($isAlreadyExist)) {
                $this->addMessage("账号名称{$data['passport']}已经存在，不能添加重复名称的账号", 'error');
                Lib_Redirecter::redirectExit();
            }

            if (empty($uid)) {
                $data['from']     = 'admin';
                $data['password'] = md5($data['password'].$data['create_time']);
                $uid              = Instance::table('user')->insert($data);
                if ($uid) {
                    $this->addMessage('用户添加成功', 'success');
                } else {
                    $this->addMessage('用户添加失败', 'error');
                }
            } else {
                // 判断是否修改了密码
                $user = Instance::table('user')->getOne('*', array('uid=?', $uid));
                if (strcasecmp(md5($user['password']), $data['password']) == 0) {
                    $data['password'] = $user['password'];
                } else {
                    $data['password'] = md5($data['password'].$data['create_time']);
                }

                if (!empty($user)) {
                    $data['update_time'] = time();
                    if (Instance::table('user')->update($data, array('uid=?', $uid))) {
                        $this->addMessage('用户修改成功', 'success');
                    } else {
                        $this->addMessage('用户修改失败', 'error');
                    }
                }
            }
        }

        Lib_Redirecter::redirectExit();
    }
}
