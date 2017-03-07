<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}

/**
 * 网页授权获取用户信息.
 *
 * @author john
 */
class Controller_WebAuthCallback extends BaseController
{
    /**
     * 微信授权回调函数.
     *
     * @return void
     */
    public function index()
    {
        // 这里的url是urlencode之后的值
        $url    = Lib_Request::get('url',     '');
        $new    = Lib_Request::get('new',     0);
        $code   = Lib_Request::get('code',    '');
        $update = Lib_Request::get('update',  0);
        // $state = Lib_Request::get('state', '');
        if (!empty($code)) {
            $config         = Config::get();
            $wechatApi      = Module_WeChat_Api::Instance();
            $userAccessInfo = $wechatApi->getUserAccessInfoByCode($code);

            if (empty($userAccessInfo['errcode'])) {
                // 进入回调页面时会马上获取openid
                $openid = $userAccessInfo['openid'];
                if (!empty($new) || !empty($update)) {
                    $userInfo  = $wechatApi->getUserInfoByAuth($openid, $userAccessInfo['access_token']);
                    $data      = array(
                        'openid'        => $openid,
                        'appid'         => $config['WeChat']['appid'],
                        'unionid'       => isset($userAccessInfo['unionid']) ? $userAccessInfo['unionid'] : '',
                        'nickname'      => isset($userInfo['nickname']) ? $userInfo['nickname'] : '',
                        'sex'           => $userInfo['sex'],
                        'city'          => $userInfo['city'],
                        'province'      => $userInfo['province'],
                        'country'       => $userInfo['country'],
                        'subscribe'     => isset($userInfo['subscribe']) ? $userInfo['subscribe'] : 0,
                        'access_token'  => $userAccessInfo['access_token'],
                        'expires_in'    => $userAccessInfo['expires_in'],
                        'refresh_token' => $userAccessInfo['refresh_token'],
                        'raw'           => json_encode($userInfo),
                    );
                }
                if (!empty($new)) {
                    // 新增用户信息(注意：当第一次进入该回调页面时new为null，第二次为1)
                    // Logger::log("insert for existing user:{$openid}", 'wechat_webauth');
                    $data['create_time'] = time();
                    $wechatId = Instance::table('wechat_user')->insert($data, 'ignore');
                    if (!empty($wechatId)) {
                        $data = array(
                            'wechat_id'     => $wechatId,
                            'nickname'      => $userInfo['nickname'],
                            'avatar'        => $userInfo['headimgurl'],
                            'register_time' => time(),
                        );
                        Instance::table('user')->insert($data, 'ignore', true);
                    }
                } else if (!empty($update)) {
                    // 微信用户信息更新
                    // Logger::log("update for existing user:{$openid}", 'wechat_webauth');
                    $data['update_time'] = time();
                    $wechatId = Instance::table('wechat_user')->insert($data, 'update');
                    if (!empty($wechatId)) {
                        $data = array(
                            'nickname'    => $userInfo['nickname'],
                            'avatar'      => $userInfo['headimgurl'],
                            'update_time' => time(),
                        );
                        Instance::table('user')->update($data, array('wechat_id' => $wechatId));
                    }
                } else {
                    // 判断对应openid是否在数据库中存在
                    $fields     = '*';
                    $condition  = array('openid=? and appid=?', $openid, $config['WeChat']['appid']);
                    $wechatInfo = Instance::table('wechat_user')->getOne($fields, $condition);
                    if (empty($wechatInfo)) {
                        Logger::log("request for new user:{$openid}", 'wechat_webauth');
                        // 如果是新用户，那么新增用户，这里会重新请求微信接口，并且需要用户授权获取用户信息，成功后跳转回该页面
                        $callbackUrl = Lib_Redirecter::getCurrentUrlWithoutUri()."WebAuthCallback?url={$url}&new=1";
                        $wechatApi->redirectToGetUserInfoByWebAuth($callbackUrl, 'snsapi_userinfo');
                    } else {
                        // 判断是否更新(7天更新一次，这里的时间可以根据不同的业务场景需要进行调整)
                        if (time() - $wechatInfo['update_time'] > 7*86400) {
                            Logger::log("request for existing user:{$openid}", 'wechat_webauth');
                            $callbackUrl = Lib_Redirecter::getCurrentUrlWithoutUri()."WebAuthCallback?url={$url}&update=1";
                            $wechatApi->redirectToGetUserInfoByWebAuth($callbackUrl, 'snsapi_userinfo');
                        }
                    }
                }

                if (!empty($openid)) {
                    Module_User::Instance()->initSessionByOpenid($openid);
                }
            }
        }

        if (!empty($url)) {
            Lib_Redirecter::redirectExit(urldecode($url));
        }
    }
}