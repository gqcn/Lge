<?php
/**
 * 回调控制控制器.
 *
 * @author qiangg <qiangg@jumei.com>
 */

/**
 * 回调控制控制器 .
 */
class Controller_Callback extends BaseController
{
    /**
     * 请求数据.
     *
     * @var array
     */
    public $data = array();

    /**
     * 入口函数.
     *
     * @return void
     */
    public function index()
    {
        if ($this->_checkSignature()) {
            $echostr    = isset($this->_get['echostr']) ? $this->_get['echostr'] : '';
            $this->data = $this->_getRequestData();
            if (empty($this->data)) {
                // 没有任何POST参数的时候，这是一个服务器验证信息
                echo $echostr;
            } else {
                // 去重检查
                $repeatKey = "{$this->data['FromUserName']}_{$this->data['CreateTime']}";
                if (!$this->checkRepeat($repeatKey)) {
                    $msgType = '';
                    if (!empty($this->data['Event'])) {
                        $msgType = 'event';
                    } elseif (!empty($this->data['MsgId'])) {
                        $msgType = 'message';
                    }
                    // 消息判断
                    switch ($msgType) {
                        // 事件推送判断
                        case 'event':
                            $event = strtolower($this->data['Event']);
                            switch ($event) {
                                // 订阅
                                case 'subscribe':
                                    $this->_checkAndUpdateUserInfo();
                                    $this->_onSubscribe();
                                    break;
                                // 取消订阅
                                case 'unsubscribe':
                                    $this->_onUnsubscribe();
                                    break;
                                // 用户已关注时的二维码扫描
                                case 'scan':
                                    $this->_checkAndUpdateUserInfo();
                                    $this->_onScan();
                                    break;
                                // 上报地理位置事件
                                case 'location':
                                    $this->_onLocation();
                                    break;
                                // 点击菜单拉取消息时的事件
                                case 'click':
                                    $this->_onClick();
                                    break;
                                // 点击菜单跳转链接时的事件
                                case 'view':
                                    break;
                                // 模板内容发送结束
                                case 'templatesendjobfinish':
                                    break;
                                // 其他事件
                                default:
                                    break;
                            }
                            break;

                        // 普通消息
                        case 'message':
                            $this->_onMessage();
                            break;

                        // @todo 其他消息处理
                        default:
                            break;
                    }
                }
            }
        }
    }

    /**
     * 根据关键字执行回复.
     *
     * @param string $keywords 关键字.
     *
     * @return void
     */
    private function _checkAndReplyByKeywords($keywords)
    {
        $data  = array();
        $reply = Instance::table('wechat_reply')->getOne('*', array('keywords' => $keywords), null, "`order`,`id` ASC");
        if (!empty($reply)) {
            switch ($reply['type']) {
                case 'text':
                    $data = array(
                        'ToUserName'   => "<![CDATA[{$this->data['FromUserName']}]]>",
                        'FromUserName' => "<![CDATA[{$this->data['ToUserName']}]]>",
                        'CreateTime'   => time(),
                        'MsgType'      => 'text',
                        'Content'      => "<![CDATA[{$reply['content']}]]>",
                    );
                    break;

                case 'news':
                    $news = json_decode($reply['content'], true);
                    $data = array(
                        'ToUserName'   => "<![CDATA[{$this->data['FromUserName']}]]>",
                        'FromUserName' => "<![CDATA[{$this->data['ToUserName']}]]>",
                        'CreateTime'   => time(),
                        'MsgType'      => '<![CDATA[news]]>',
                        'Content'      => "<![CDATA[]]>",
                        'ArticleCount' => count($news),
                        'Articles'     => array(
                            'item' => array(
//                                'Title'       => "<![CDATA[星级家-迎接家务包年的新生活（内含400元初体验抵用金）]]>",
//                                'Description' => "<![CDATA[感谢您的关注！我们向您推荐星级家家务包年服务，更稳定更专业更安心更实惠，查看详情了解更多，还有400元初体验抵用金限量领取！]]>",
//                                'PicUrl'      => "<![CDATA[http://scjtcl.cn/static/resource/images/home.jpg]]>",
//                                'Url'         => "<![CDATA[http://scjtcl.cn/]]>",
                            ),
                        ),
                    );
                    foreach ($news as $v) {
                        // 图片地址如果为本站地址，那么默认为当前域名下可访问的图片
                        $image = $v['image'];
                        if ($image[0] == '/' || substr($image, 0, 4) != 'http') {
                            $image = Lib_Redirecter::getCurrentUrlWithoutUri().$v['image'];
                        }
                        $brief = isset($v['brief']) ? $v['brief'] : $v['title'];
                        $data['Articles']['item'][] = array(
                            'Title'       => "<![CDATA[{$v['title']}]]>",
                            'Description' => "<![CDATA[{$brief}]]>",
                            'PicUrl'      => "<![CDATA[{$image}]]>",
                            'Url'         => "<![CDATA[{$v['url']}]]>",
                        );
                    }
                    break;
            }
        }

        if (!empty($data)) {
            $this->_response($data);
        }
    }

    /**
     * 检查当前用户是否在数据库中存在，没有则写入，已存在则更新用户信息。
     *
     * @return void
     */
    private function _checkAndUpdateUserInfo()
    {
        $openid    = $this->data['FromUserName'];
        $config    = Config::get();
        $wechatApi = Module_WeChat_Api::Instance();
        $user      = Module_User::instance()->getUserByOpenid($openid);
        $userInfo  = $wechatApi->getUserInfoByAccessToken($openid);
        // 新增/更新微信用户信息
        $data      = array(
            'openid'        => $openid,
            'unionid'       => isset($userInfo['unionid']) ? $userInfo['unionid'] : '',
            'nickname'      => isset($userInfo['nickname']) ? $userInfo['nickname'] : '',
            'sex'           => $userInfo['sex'],
            'city'          => $userInfo['city'],
            'province'      => $userInfo['province'],
            'country'       => $userInfo['country'],
            'subscribe'     => 1,
            'access_token'  => '',
            'expires_in'    => '',
            'refresh_token' => '',
            'raw'           => json_encode($userInfo),
            'appid'         => $config['WeChat']['appid'],
            'update_time'   => time(),
        );
        // 如果不能查询到user表对应openid的数据，那么表示该用户不存在，属于第一次加入
        if (empty($user['wechat_id'])) {
            $data['create_time'] = time();
        }
        $wechatId = Instance::table('wechat_user')->insert($data, 'update');
        // 新增/更新用户数据表信息
        if (!empty($user)) {
            $wechatId = $user['wechat_id'];
        }
        if (empty($user)) {
            $data = array(
                'wechat_id'     => $wechatId,
                'nickname'      => $userInfo['nickname'],
                'avatar'        => $userInfo['headimgurl'],
                'create_time'   => time(),
            );
            Instance::table('user')->insert($data, 'ignore', false);
        } else {
            $data = array(
                'nickname' => $userInfo['nickname'],
                'avatar'   => $userInfo['headimgurl'],
            );
            $condition = "wechat_id={$wechatId} AND gid=0 AND passport IS NULL";
            Instance::table('user')->update($data, $condition);
        }
    }

    /**
     * 关注消息.
     *
     * @return void
     */
    private function _onSubscribe()
    {
        // 更新关注状态
        Instance::table('wechat_user')->update(array('subscribe' => 1), array('openid' => $this->data['FromUserName']));
        $this->_checkAndReplyByKeywords('关注时自动回复');
    }

    /**
     * 取消关注事件，取消关联商家.
     */
    private function _onUnsubscribe()
    {
        // 更新关注状态
        Instance::table('wechat_user')->update(array('subscribe' => 0), array('openid' => $this->data['FromUserName']));
    }

    /**
     * 扫描二维码时处理.
     *
     * @return void
     */
    private function _onScan()
    {

    }

    /**
     * 点击消息处理.
     *
     * @return void
     */
    private function _onClick()
    {
        $this->_checkAndReplyByKeywords($this->data['EventKey']);
    }

    /**
     * 普通消息处理.
     *
     * @return void
     */
    private function _onMessage()
    {
        $this->_checkAndReplyByKeywords($this->data['Content']);
    }

    /**
     * 用户上报地理位置.
     */
    private function _onLocation()
    {

    }

    /**
     * 消息去重.
     *
     * @param string  $key Key.
     * @param integer $ttl 有效时间.
     *
     * @return boolean
     */
    private function checkRepeat($key, $ttl = 120)
    {
        $redis = Instance::redis('cache');
        $code = $redis->get($key);
        if (empty($code)) {
            $redis->setex($key, $ttl, 1);
            return false;
        } else {
            $this->log($key, 'event_repeat');
            return true;
        }
    }

    /**
     * 检查签名.
     *
     * @return boolean
     */
    private function _checkSignature()
    {
        $config = Config::get();
        $data   = Lib_Request::getArray(array(
            'signature' => '',
            'timestamp' => '',
            'nonce'     => '',
        ), 'get');
        $signature = $data['signature'];
        $timestamp = $data['timestamp'];
        $nonce     = $data['nonce'];
        $token     = $config['WeChat']['token'];
        $tmpArr    = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr    = implode($tmpArr);
        $tmpStr    = sha1($tmpStr);
        if ($tmpStr == $signature) {
            $this->log("{$signature}|{$timestamp}|{$nonce}", 'signature_success');
            return true;
        } else {
            $this->log("{$signature}|{$timestamp}|{$nonce}", 'signature_failed');
            return false;
        }
    }


    /**
     * 获取微信服务器上报的数据.
     *
     * @return array
     */
    private function _getRequestData()
    {
        $data = array();
        $xml  = file_get_contents('php://input');
        $this->log($xml, 'events');
        if (!empty($xml)) {
            $data = Lib_XmlParser::xml2Array($xml);
        }
        return $data;
    }

    /**
     * 向微信服务器返回信息.
     *
     * @param array $data XML消息数组.
     *
     * @return void
     */
    private function _response(array $data)
    {
        $xml = Lib_XmlParser::array2Xml(array('xml' => $data), false, 'utf-8', true);
        echo $xml;
        $this->log($xml, 'responses');
    }
}
