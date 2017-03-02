<?php
if (!defined('PhpMe')) {
	exit('Include Permission Denied!');
}

/**
 * 阿里云域名解析接口定时更新动态服务器IP.
 */
class Controller_IpUpdater extends Controller_Base
{
    public $logCategory     = 'crontab/ip-updater';
    public $accessKeyId     = 'GdXUNhfUzSEdVEpE';
    public $accessKeySecret = 'RvTVnnRKJKuutobcZv2b74vpOQNKg3';
    public $domains         = array('johnx.cn', 'johng.cn');

    /**
     * 默认入口函数.
     *
     * @return void
     */
    public function index()
    {
        set_time_limit(60);
        foreach ($this->domains as $domain) {
            $publicIp = $this->_getMyPublicIp();
            $records  = $this->_sendRequest(array(
                'Action'     => 'DescribeDomainRecords',
                'DomainName' => $domain,
            ));
            if (!empty($records) && !empty($records['DomainRecords'])) {
                // 如果IP有更新，那么调用接口更新IP
                foreach ($records['DomainRecords']['Record'] as $k => $v) {
                    if (preg_match("/(\d+)\.(\d+)\.(\d+)\.(\d+)/", $v['Value']) && strcasecmp($v['Value'], $publicIp) != 0) {
                        $result = $this->_sendRequest(array(
                            'Action'     => 'UpdateDomainRecord',
                            'RecordId'   => $v['RecordId'],
                            'RR'         => $v['RR'],
                            'Type'       => $v['Type'],
                            'Value'      => $publicIp,
                        ));
                        $recordJson = json_encode($v);
                        $resultJson = json_encode($result);
                        Logger::log("{$domain}, ip update: {$v['Value']} => {$publicIp}, record:{$recordJson}, result:{$resultJson}", $this->logCategory);
                    }
                }
            } else {
                Logger::log('invalid records:'.json_encode($records), $this->logCategory);
            }
        }
    }

    /**
     * 阿里云解析请求.
     *
     * @param array $request 请求参数数组.
     * @return mixed
     */
    private function _sendRequest(array $request)
    {
        $http      = new Lib_Network_Http();
        $apiUrl    = 'http://alidns.aliyuncs.com/';
        $nonce     = str_replace('.', '', microtime(true)).rand(0, 9).rand(0, 9).rand(0, 9);
        $utcTime   = time() - 8*3600;
        $params = array(
            'Format'           => 'JSON',
            'Version'          => '2015-01-09',
            'AccessKeyId'      => $this->accessKeyId,
            'SignatureMethod'  => 'HMAC-SHA1',
            'Timestamp'        => date('Y-m-d', $utcTime).'T'.date('H:i:s', $utcTime).'Z',
            'SignatureVersion' => '1.0',
            'SignatureNonce'   => $nonce,
        );

        // 请求参数
        $params = array_merge($params, $request);

        /**
         * 签名处理
         */
        // 排序
        ksort($params);

        // URL编码 + 连接
        $stringA = '';
        foreach ($params as $k => $v) {
            if (isset($v) && $v !== '') {
                $k = urlencode($k);
                $v = urlencode($v);
                if (empty($stringA)) {
                    $stringA .= "{$k}={$v}";
                } else {
                    $stringA .= "&{$k}={$v}";
                }
            }
        }
        $stringA = "GET&%2F&".urlencode($stringA);
        // HMAC + BASE64
        $signature = base64_encode(hash_hmac('sha1', $stringA, $this->accessKeySecret.'&', true));
        // 添加签名
        $params['Signature'] = $signature;

        /**
         * 发送请求
         */
        $requestUrl = $apiUrl.'?'.http_build_query($params);
        $result     = $http->get($requestUrl);
        return json_decode($result, true);
    }

    /**
     * 获取当前服务器的公网IP.
     *
     * @return string
     */
    private function _getMyPublicIp()
    {
        $ip      = '';
        $http    = new Lib_Network_Http();
        $ipInfo  = $http->get('http://ipinfo.io/json');
        $ipArray = json_decode($ipInfo, true);
        if (!empty($ipArray)) {
            $ip = $ipArray['ip'];
        }
        Logger::log('public ip:'.$ip, $this->logCategory);
        return $ip;
    }
}