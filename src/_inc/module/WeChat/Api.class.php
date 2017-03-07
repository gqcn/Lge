<?php
namespace Lge;

/**
 * 微信API封装.
 */
class Module_WeChat_Api extends Module_WeChat_Base
{
    public $appid     = ''; // 微信appid
    public $unionid   = ''; // 微信unionid
    public $appSecret = ''; // 微信公众号appsecret
    public $token     = ''; // 微信公众号token
    public $mchid     = ''; // (用于微信支付)商家id
    public $mchKey    = ''; // (用于微信支付)商家key
    public $certPath  = ''; // (用于微信支付)商家证书目录

    // accessToken缓存key.
    const REDIS_KEY_ACCESS_TOKEN_CACHE = 'wechat_access_token_cache';
    // jsapi_ticket缓存key
    const REDIS_KEY_JSAPI_TICKET_CACHE = 'wechat_jsapi_ticket_cache';

    /**
     * 构造函数，初始化必须参数.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * 读取配置文件，初始化微信配置，如果没有配置那么则需要手动设置成员变量.
         */
        $config = Config::get();
        if (!empty($config['WeChat'])) {
            $this->appid     = $config['WeChat']['appid'];
            $this->unionid   = $config['WeChat']['unionid'];
            $this->appSecret = $config['WeChat']['appsecret'];
            $this->token     = $config['WeChat']['token'];
            $this->mchKey    = $config['WeChat']['mch_key'];
            $this->mchid     = $config['WeChat']['mch_id'];
            $this->certPath  = $config['WeChat']['cert_path'];
        }
    }

    /**
     * 获得实例.
     *
     * @return Module_WeChat_Api
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * ================================================================================================
     * 菜单管理
     * ================================================================================================
     */
    /**
     * 自定义菜单创建.
     *
     * @param string $menuStr.
     *
     * @return array|string
     */
    public function menuCreate($menuStr)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/menu/create";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $result  = $this->request($url, $menuStr, 'post');
        return $result;
    }

    /**
     * 删除自定义目录.
     *
     * @return array|string
     */
    public function menuDelete()
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/menu/delete";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $result  = $this->request($url, '', 'get');
        return $result;
    }

    /**
     * 获取菜单列表.
     *
     * @return array
     */
    public function menuList()
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $result  = $this->request($url, '', 'get');
        return $result;
    }


    /**
     * ================================================================================================
     * 卡券管理
     * ================================================================================================
     */
    /**
     * 获取卡券列表.
     *
     * @param integer $start      分页开始数.
     * @param integer $limit      每页数量.
     * @param array   $statusList 状态码过滤(http://mp.weixin.qq.com/wiki/3/3f88e06725fd911e6a46e2f5552d80a7.html).
     * @return array|string
     */
    public function cardList($start, $limit, $statusList = array('CARD_STATUS_VERIFY_OK'))
    {
        $url     = "https://api.weixin.qq.com/card/batchget";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $params  = array(
            "offset"      => $start,
            "count"       => $limit,
            "status_list" => $statusList
        );
        $result = $this->request($url, $this->jsonEncdoe($params), 'post');
        return $result;
    }

    /**
     * 用于获取用户卡包里的，属于该appid下的卡券.
     *
     * @param string $openid OpenID.
     * @param string $cardId CardID(不填写时默认查询当前appid下的卡券。).
     * @return array|string
     */
    public function cardListForUser($openid, $cardId = '')
    {
        $url     = "https://api.weixin.qq.com/card/user/getcardlist";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $params  = array(
            "openid"  => $openid,
            "card_id" => $cardId,
        );
        $list   = array();
        $result = $this->request($url, $this->jsonEncdoe($params), 'post');
        if (!empty($result['card_list'])) {
            $list = $result['card_list'];
        }
        return $list;
    }

    /**
     * 获取卡券详情信息.
     *
     * @param string $cardId 卡券ID.
     *
     * @return array|string
     */
    public function cardInfo($cardId)
    {
        $url     = "https://api.weixin.qq.com/card/get";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $params  = array(
            "card_id" => $cardId
        );
        $result = $this->request($url, $this->jsonEncdoe($params), 'post');
        return $result;
    }


    /**
     * ================================================================================================
     * 素材管理
     * ================================================================================================
     */

    /**
     * (临时素材)根据Media ID 返回微信下载使用的URL.
     *
     * @param string $mediaId 素材ID.
     * @return string
     */
    public function materialTempUrlByMediaId($mediaId)
    {
        $accessToken = $this->accessToken();
        $url         = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$accessToken}&media_id={$mediaId}";
        return $url;
    }

    /**
     * 获取永久素材总数(返回的结果是各个分类的总数json).
     *
     * @return array|string
     */
    public function materialCount()
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $result = $this->request($url, '', 'get');
        return $result;
    }

    /**
     * 获取微信永久素材列表.
     *
     * @param string $type  类型.
     * @param int    $start 分页参数.
     * @param int    $limit 每页数量.
     *
     * @return array|string
     */
    public function materialList($type, $start = 0, $limit = 100)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/material/batchget_material";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $params  = array(
            "type"      => $type,
            "offset"    => $start,
            "count"     => $limit,
        );
        $result = $this->request($url, $this->jsonEncdoe($params), 'post');
        return $result;
    }

    /**
     * 根据Media ID 获取永久素材内容(注意：如果素材为图文消息或者视频消息，那么返回的是json字符串，否则返回的是素材内容).
     *
     * @param string $mediaId 素材ID.
     *
     * @return string
     */
    public function materialGetByMediaId($mediaId)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/material/get_material";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $params  = array(
            "media_id" => $mediaId,
        );
        $result = $this->request($url, $this->jsonEncdoe($params), 'post', 'raw');
        return $result;
    }

    /**
     * 图文消息**内容**中的图片上传。
     * 请注意，在图文消息的具体内容中，将过滤外部的图片链接，开发者可以通过下述接口上传图片得到URL，放到图文内容中使用。
     * 上传图文消息内的图片获取URL 请注意，本接口所上传的图片不占用公众号的素材库中图片数量的5000个的限制。图片仅支持jpg/png格式，大小必须在1MB以下。
     *
     * @param string $filePath 本地文件绝对路径.
     *
     * @return array|string
     */
    public function materialAddImageForNews($filePath)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/media/uploadimg";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $params  = array(
            "media" => "@{$filePath}",
        );
        $result = $this->request($url, $params, 'post');
        return $result;
    }

    /**
     * 添加图文消息。
     * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738729&token=&lang=zh_CN
     *
     * @param array $params 参数.
     *
     * @return array|string
     */
    public function materialAddNews(array $params)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/material/add_news";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $result = $this->request($url, $this->jsonEncdoe($params), 'post');
        return $result;
    }

    /**
     * 修改图文消息。
     * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738732&token=&lang=zh_CN
     *
     * @param array $params 参数.
     *
     * @return array|string
     */
    public function materialupdateNews(array $params)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/material/update_news";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $result = $this->request($url, $this->jsonEncdoe($params), 'post');
        return $result;
    }

    /**
     * 上传永久素材.
     *
     * @param string $type     素材类型:图片(image)、语音(voice)、视频(video)和缩略图(thumb).
     * @param string $filePath 本地文件绝对路径.
     * @return array|string
     */
    public function materialAdd($type, $filePath)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/material/add_material";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}&type={$type}";
        $params  = array(
            "media" => "@{$filePath}",
        );
        $result = $this->request($url, $params, 'post');
        return $result;
    }

    /**
     * 删除永久素材.
     *
     * @param string $mediaId 素材ID.
     *
     * @return array|string
     */
    public function materialDelete($mediaId)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/material/del_material?";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $params  = array(
            "media_id" => $mediaId
        );
        $result = $this->request($url, $params, 'post');
        return $result;
    }


    /**
     * ================================================================================================
     * 二维码管理
     * ================================================================================================
     */
    /**
     * 获得临时的二维码Ticket.
     *
     * @param integer $sceneId 场景ID值，注意类型.
     *
     * @return string
     */
    public function qrcodeTempTicket($sceneId)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/qrcode/create";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $params  = array(
            "expire_seconds" => 604800,
            "action_name"    => 'QR_SCENE',
            "action_info"    => array(
                "scene" => array(
                    "scene_id" => $sceneId
                )
            )
        );
        $result = $this->request($url, $this->jsonEncdoe($params), 'post');
        $ticket = '';
        if (!empty($result['ticket'])) {
            $ticket = $result['ticket'];
        }
        return $ticket;
    }

    /**
     * 获得永久的二维码Ticket(每个公众好目前为最多10万个).
     *
     * @param string $sceneStr 场景字符串值，注意类型.
     *
     * @return string
     */
    public function qrcodeLimitTicket($sceneStr)
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/qrcode/create";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        $params  = array(
            "action_name"    => 'QR_LIMIT_STR_SCENE',
            "action_info"    => array(
                "scene" => array(
                    "scene_str" => $sceneStr
                )
            )
        );
        $result = $this->request($url, $this->jsonEncdoe($params), 'post');
        $ticket = '';
        if (!empty($result['ticket'])) {
            $ticket = $result['ticket'];
        }
        return $ticket;
    }

    /**
     * 根据Ticket获取二维码图片URL.
     * @param string $ticket
     */
    public function qrcodeUrlByTicket($ticket)
    {
        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
    }


    /**
     * ================================================================================================
     * 消息管理
     * ================================================================================================
     */
    /**
     * 发送模板消息.
     *
     * @param $openid
     * @param $templateId
     * @param array $data
     * @param string $redirectUrl
     *
     * @return string
     */
    public function messageSendByTemplate($openid, $templateId, array $data, $redirectUrl = '')
    {
        $url     = "https://api.weixin.qq.com/cgi-bin/message/template/send";
        $token   = $this->accessToken();
        $url    .= "?access_token={$token}";
        // 处理data数据
        foreach ($data as $k => $v) {
            if (!is_array($v)) {
                $v = array(
                    'value' => $v,
                    'color' => '#000',
                );
            } else {
                if (!isset($v['color'])) {
                    $v['color'] = '#000';
                }
            }
            $data[$k] = $v;
        }
        $params  = array(
            "touser"      => $openid,
            "template_id" => $templateId,
            "data"        => $data
        );
        if (!empty($redirectUrl)) {
            $params['url'] = $redirectUrl;
        }
        $result = $this->request($url, $this->jsonEncdoe($params), 'post');
        return $result;
    }


    /**
     * ================================================================================================
     * JS-SDK微信网页
     * ================================================================================================
     */
    /**
     * 获取JSAPI Ticket.
     *
     * @param string $type Type.
     *
     * @return string
     */
    public function jsApiTicket($type = 'jsapi')
    {
        $redis    = Instance::redis('cache');
        $redisKey = self::REDIS_KEY_JSAPI_TICKET_CACHE.'_'.$type.'_'.$this->appid;
        $ticket   = $redis->get($redisKey);
        if (empty($ticket)) {
            $url    = "https://api.weixin.qq.com/cgi-bin/ticket/getticket";
            $token  = $this->accessToken();
            $params = "access_token={$token}&type={$type}";
            $result = $this->request($url, $params);
            if (empty($result['ticket'])) {
                $ticket = null;
            } else {
                $ticket = $result['ticket'];
                $expire = intval($result['expires_in']);
                $expire = $expire - 3600;
                $redis->setex($redisKey, $expire, $ticket);
                $this->log("{$this->appid} | {$ticket}", "{$type}_ticket");
            }
        }
        return $ticket;
    }

    /**
     * 生成用于卡券JS-SDK的cardExt参数.
     *
     * @param array $params 参数.
     *
     * @return string
     */
    public function jsApiMakeCardExt($params)
    {
        sort($params, SORT_STRING);
        $stringA = '';
        foreach ($params as $k => $v) {
            $stringA .= $v;
        }
        $signature = sha1($stringA);
        return $signature;
    }

    /**
     * 生成JS SDK接口签名.
     *
     * @param  $params 参数.
     * @return string
     */
    public function jsApiSignature($params)
    {
        ksort($params);
        $stringA = '';
        foreach ($params as $k => $v) {
            if (isset($v) && $v !== '') {
                $stringA .= "{$k}={$v}&";
            }
        }
        $stringA   = rtrim($stringA, '&');
        $signature = sha1($stringA);
        return $signature;
    }

    /**
     * ================================================================================================
     * 用户管理
     * ================================================================================================
     */
    /**
     * 根据用户授权的code获取用户的openid(授权获取用户信息第一步).
     *
     * @param string $code CODE.
     *
     * @return array
     */
    public function userAccessInfoByCode($code)
    {
        $url     = "https://api.weixin.qq.com/sns/oauth2/access_token";
        $url    .= "?appid={$this->appid}&secret={$this->appSecret}&code={$code}&grant_type=authorization_code";
        $result  = $this->request($url, '', 'get');
        return $result;
    }

    /**
     * 根据用户网页授权获取用户信息(授权获取用户信息第二步).
     *
     * @param string $openid      OpenID.
     * @param string $accessToken 用户授权的AccessToken.
     * @return array
     */
    public function userInfoByAuth($openid, $accessToken)
    {
        $url     = "https://api.weixin.qq.com/sns/userinfo";
        $url    .= "?access_token={$accessToken}&openid={$openid}&lang=zh_CN";
        $result  = $this->request($url, '', 'get');
        return $result;
    }

    /**
     * 用户关注微信公众号后，可获取用户的基本信息.
     *
     * @param string $openid OpenID.
     *
     * @return array
     */
    public function userInfoByAccessToken($openid)
    {
        $token   = $this->accessToken();
        $url     = "https://api.weixin.qq.com/cgi-bin/user/info";
        $url    .= "?access_token={$token}&openid={$openid}&lang=zh_CN";
        $result  = $this->request($url, '', 'get');
        return $result;
    }


    /**
     * ================================================================================================
     * 支付管理
     * ================================================================================================
     */
    /**
     * 发放红包(https://pay.weixin.qq.com/wiki/doc/api/cash_coupon.php?chapter=13_5).
     *
     * @param $openid
     * @param $totalAmount
     * @param $totalNum
     * @param $wishing
     * @param $clientIp
     * @param $actName
     * @param $remark
     * @param $sendName
     * @return array|string
     */
    public function paymentSendRedPack($openid,
                                $totalAmount,
                                $totalNum,
                                $wishing,
                                $clientIp,
                                $actName,
                                $remark,
                                $sendName)
    {
        $url       = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
        $mchBillno = $this->mchid;
        $mchBillno.= date('Ymd');
        $mchBillno.= substr(str_replace('.', '', microtime(true)), 4);
        $params    = array(
            'nonce_str'    => md5(microtime(true).rand(0, 9999)),
            'mch_billno'   => $mchBillno,
            'mch_id'       => $this->mchid,
            'wxappid'      => $this->appid,
            'send_name'    => $sendName,
            're_openid'    => $openid,
            'total_amount' => $totalAmount,
            'total_num'    => $totalNum,
            'wishing'      => $wishing,
            'client_ip'    => $clientIp,
            'act_name'     => $actName,
            'remark'       => $remark,
        );
        $params['sign'] = $this->paymentSignature($params);
        $params = Lib_XmlParser::array2Xml(array('xml' => $params), false, null, true);
        $result = $this->request($url, $params, 'post', 'xml', true);
        return $result;
    }

    /**
     * 创建微信支付订单.
     *
     * @param array $params 订单参数(参考 https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=9_1).
     * @return array|string
     */
    public function paymentCreateOrder(array $params)
    {
        $xml = Lib_XmlParser::array2Xml(array('xml' => $params), true, 'utf-8', true);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $result = $this->request($url, $xml, 'post', 'xml');
        return $result;
    }

    /**
     * 生成商户API接口签名.
     *
     * @param  $params 参数.
     * @return string
     */
    public function paymentSignature($params)
    {
        ksort($params);
        $stringA = '';
        foreach ($params as $k => $v) {
            if (isset($v) && $v !== '') {
                $stringA .= "{$k}={$v}&";
            }
        }
        $stringA  .= "key={$this->mchKey}";
        $signature = strtoupper(md5($stringA));
        return $signature;
    }


    /**
     * ================================================================================================
     * 基础功能
     * ================================================================================================
     */
    /**
     * 获得帐号访问的accessToken.
     *
     * @return string
     */
    public function accessToken()
    {
        $redis    = Instance::redis('cache');
        $redisKey = self::REDIS_KEY_ACCESS_TOKEN_CACHE.'_'.$this->appid;
        $token    = $redis->get($redisKey);
        if (empty($token)) {
            $url    = "https://api.weixin.qq.com/cgi-bin/token";
            $params = "grant_type=client_credential&appid={$this->appid}&secret={$this->appSecret}";
            $result = $this->request($url, $params);
            if (empty($result['access_token'])) {
                $token = null;
            } else {
                $token  = $result['access_token'];
                $expire = intval($result['expires_in']);
                $expire = $expire - 3600; // 防止服务器的时间差，这里本地服务器提前1个小时过期
                $redis->setex($redisKey, $expire, $token);
                $this->log("{$this->appid} | {$this->appSecret} | {$token}", "access_tokens");
            }
        }
        return $token;
    }

    /**
     * 网页跳转授权获取用户信息(授权类型可选).
     *
     * @param string $callbackUrl 回调URL.
     * @param string $scope       授权类型(snsapi_base|snsapi_userinfo).
     *
     * @return void
     */
    public function redirectToGetUserInfoByWebAuth($callbackUrl, $scope = 'snsapi_base') {
        $callbackUrl = urlencode($callbackUrl);
        $state       = str_replace('.', '', microtime(true).rand(0, 9999));
        $url         = "https://open.weixin.qq.com/connect/oauth2/authorize";
        $url        .= "?appid={$this->appid}&redirect_uri={$callbackUrl}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
        Lib_Redirecter::redirectExit($url);
    }

    /**
     * 生成32位的唯一字符串.
     *
     * @return string
     */
    public function nonceStr()
    {
        return md5(microtime(true).rand(0, 9999));
    }

    /**
     * 发送请求.
     * 
     * @param string  $url        请求地址.
     * @param mixed   $params     请求参数(可以是字符串也可以是数组).
     * @param string  $type       请求类型.
     * @param string  $resultType 返回数据类型.
     * @param boolean $useSSL     是否使用SSL验证.
     *
     * @return array|string
     */
    public function request($url, $params, $type = 'get', $resultType = 'json', $useSSL = false)
    {
        for ($i = 0; $i < 3; ++$i) {
            try {
                $ch = curl_init();
                if ($type == 'get' && !empty($params)) {
                    // get请求时需要转换为字符串
                    if (is_array($params)) {
                        $params = http_build_query($params);
                    }
                    if (stripos($url, '?') !== false) {
                        $url .= "&{$params}";
                    } else {
                        $url .= "?{$params}";
                    }
                } else {
                    // 文件上传兼容处理
                    if (is_array($params)) {
                        foreach ($params as $k => $v) {
                            if ($v[0] == '@') {
                                if (class_exists('\CURLFile')) {
                                    $filePath   = substr($v, 1);
                                    $params[$k] = new \CURLFile(realpath($filePath));
                                }
                            }
                        }
                    }
                    curl_setopt($ch, CURLOPT_POST,       1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                }
                curl_setopt($ch, CURLOPT_URL,            $url);
                curl_setopt($ch, CURLOPT_HEADER,         0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($ch, CURLOPT_SSLVERSION,     3);

                if ($useSSL) {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSLCERT, $this->certPath.'apiclient_cert.pem');
                    curl_setopt($ch, CURLOPT_SSLKEY,  $this->certPath.'apiclient_key.pem');
                    curl_setopt($ch, CURLOPT_CAINFO,  $this->certPath.'rootca.pem');
                }

                $result = curl_exec($ch);
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                // 下面都不会再使用这个参数,需要将这个数组抓欢为字符串以便记录日志
                if (is_array($params)) {
                    $params = $this->jsonEncdoe($params);
                }
                $this->log("{$url}\t{$params}\t{$result}\t{$status}", "requests");
                if (!empty($result) && $status == '200') {
                    if ($resultType == 'json') {
                        $result = json_decode($result, true);
                    } else if ($resultType == 'xml') {
                        $result = Lib_XmlParser::xml2Array($result);
                    }
                    break;
                }
            } catch (Exception $e) {
                $this->log($e->getMessage(), "requests_error");
            }
        }
        return $result;
    }

    /**
     * 将数据转换为json格式.
     *
     * @param mixed $data 数据.
     *
     * @return string
     */
    public function jsonEncdoe($data)
    {
        if (PHP_VERSION >= '5.4.0') {
            $string = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $string = $this->wechatJson53($data);
        }
        return $string;
    }

    /**
     * 使用特定function对数组中所有元素做处理.
     *
     * @param string  &$array             要处理的字符串.
     * @param string  $function           要执行的函数.
     * @param boolean $apply_to_keys_also 处理键.
     *
     * @return void
     */
    private function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }

    /**
     * 将数组转换为JSON字符串（兼容中文）.
     *
     * @param array $array 要转换的数组.
     *
     * @return string 转换得到的json字符串.
     */
    private function wechatJson53(array $array)
    {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
}
