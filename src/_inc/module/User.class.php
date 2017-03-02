<?php
if(!defined('PhpMe')){
    exit('Include Permission Denied!');
}
/**
 * 用户管理模型。
 *
 */
class Module_User extends BaseModule
{
    /**
     * 用户来源配置数组.
     *
     * @var array
     */
    public $fromArray = array(
        'system' => '系统',
        'admin'  => '后台',
        'wechat' => '微信',
        'app'    => 'APP',
        'wap'    => 'WAP',
        'web'    => 'WEB',
    );

    /**
     * 获得实例.
     *
     * @return Module_User
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * (微信自动登陆)根据openid初始化用户session信息.
     *
     * @param string $openid OpenID.
     *
     * @return void
     */
    public function initSessionByOpenid($openid)
    {
        $wechatInfo = Instance::table('wechat_user')->getOne('*', array('openid=?', $openid));
        if (!empty($wechatInfo)) {
            $this->_session['openid'] = $openid;
            $this->_session['user']   = Instance::table('user')->getOne('*', array('wechat_id=?', $wechatInfo['id']));
            if (!empty($this->_session['user'])) {
                $this->_session['user']['wechat_info'] = $wechatInfo;
                // 头像更新
                if (empty($this->_session['user']['avatar'])) {
                    $wechatInfoArray = json_decode($wechatInfo['raw'], true);
                    if (!empty($wechatInfoArray['headimgurl'])) {
                        $this->_session['user']['avatar'] = $wechatInfoArray['headimgurl'];
                        Instance::table('user')->update(
                            array('avatar' => $this->_session['user']['avatar']),
                            array('uid'    => $this->_session['user']['uid'])
                        );
                    }
                }
                Instance::cookie()->set('openid', $openid);
            }
        }
    }

    /**
     * 根据OPENID获取用户信息.
     *
     * @param string $openid OpenID.
     *
     * @return array
     */
    public function getUserByOpenid($openid)
    {
        $user     = array();
        $wechatId = Instance::table('wechat_user')->getValue('id', array('openid=?', $openid));
        if (!empty($wechatId)) {
            $user = Instance::table('user')->getOne('*', array('wechat_id=?', $wechatId));
        }
        return $user;
    }

    /**
     * (用户文件配额管理)获取配额大小.
     *
     * @param integer $uid 用户ID,为0时表示检查当前用户.
     *
     * @return integer
     */
    public function getQuota($uid = 0)
    {
        return 999999999;
    }

    /**
     * (用户文件配额管理)检查增加$size KB数据,用户是否超过配额.
     *
     * @param integer $size 单位Byte.
     * @param integer $uid  用户ID,为0时表示检查当前用户.
     *
     * @return boolean
     */
    public function checkQuota($size, $uid = 0)
    {
        return true;
    }

    /**
     * (用户文件配额管理)使用一定大小的配额.
     *
     * @param integer $size 单位Byte.
     * @param integer $uid  用户ID,为0时表示检查当前用户.
     */
    public function useQuota($size, $uid = 0)
    {
        return;
    }

    /**
     * (用户文件配额管理)更新配额.
     *
     * @param integer $quota 新的配额大小(Byte).
     * @param integer $uid   用户ID.
     */
    public function updateQuota($quota, $uid = 0)
    {
        return ;
    }

    /**
     * 用户登录.
     *
     * @param string  $passport 帐号.
     * @param string  $password 密码.
     * @param boolean $auto     是否设置自动登陆cookie.
     * @return mixed
     */
    public function doLogin($passport, $password, $auto = true)
    {
        $user   = Instance::table('user')->getOne('*', array('passport' => $passport));
        $result = $this->_checkUser($user, $password);
        if (!empty($result)) {
            // 设置session，包括用户信息以及用户用户组
            $session = $result;
            $group   = Instance::table('user_group')->getOne('*', array('id' => $result['gid']));
            if (!empty($group)) {
                $session['auths'] = Module_UserAuth::Instance()->getGroupAuths($result['gid']);
                $session['group'] = $group;
            }
            $this->_session['user'] = $session;

            // 设置自动登录
            if (!empty($auto)) {
                Instance::cookie()->set('user_info', json_encode(array($passport, $password)), 864000);
            }
        }
        return $result;
    }

    /**
     * 用户账号密码自动登陆判断.
     *
     * @param string $cookie 自动登陆信息cookie.
     *
     * @return bool
     */
    public function checkAutoLoginByCookie($cookie = '')
    {
        $result = false;
        if (empty($cookie)) {
            $cookie = Instance::cookie()->get('user_info');
        }
        if (!empty($cookie)) {
            list($passport, $password) = json_decode($cookie, true);
            if (!empty($passport) && !empty($password)) {
                $result = $this->doLogin($passport, $password, true);
            }
        }
        return !empty($result);
    }

    /**
     * 用户账号密码验证.
     *
     * @param array  $user     用户信息数组.
     * @param string $password 密码(MD5后提交的密码).
     * @return mixed
     */

    private function _checkUser(array $user, $password){
        $result = false;
        $ip     = Lib_IpHandler::getClientIp();
        if (!empty($user) && !empty($user['status'])) {
            if (strcasecmp($user['password'], md5($password.$user['create_time'])) == 0) {
                $result = $user;
                // 更新登录时间
                $data = array(
                    'latest_ip'   => $ip,
                    'latest_time' => time(),
                    'login_count' => $user['login_count'] + 1,
                );
                Instance::table('user')->update($data, array('uid' => $user['uid']));
            }
        }

        // 用户登录记录
        if (!empty($user)) {
            $data = array(
                'ip'          => $ip,
                'uid'         => isset($user['uid']) ? $user['uid'] : 0,
                'create_time' => time(),
                'result'      => !empty($result),
            );
            Instance::table('user_login')->insert($data, 'ignore', false);
        }
        return $result;
    }
}
