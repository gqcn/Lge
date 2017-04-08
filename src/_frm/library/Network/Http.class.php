<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * HTTP请求封装类，内部封装了CURL方法
 * 
 */
class Lib_Network_Http
{
    public $httpCode;   //状态码
    public $httpType;   //Content-Type值，只包含文件类型，不包含编码等其他信息
    public $httpError;  //如果发生错误则返回错误信息，与httpCode相关
    public $httpHeader; //返回的header信息 - 关联数组(其中也包含了http状态码信息)
    
    private $_ch;
    private $_cookie;
    private $_headers;
    private $_referer;
    private $_proxyHost;
    private $_proxyPort;
    private $_proxyUser;
    private $_proxyPass;
    private $_certificate;            // SSL证书
    private $_browserMode    = false; // 浏览器模式, cookie会伴随着整个请求流程,并且请求会自动保存返回的cookie
    private $_connectTimeout = 0;     // 连接超时时间
    
    
    /**
     * 获取cookie成员变量.
     * 
     * @return string
     */
    public function getCookie()
    {
        return $this->_cookie;
    }
    
    /**
     * 设置请求的COOKIE
     * @param string $cookie 例如: session=c36f5eba6978450b12; domain=.iteye.com; path=/; HttpOnly
     */
    public function setCookie($cookie)
    {
        $this->_cookie = $cookie;
    }
    
    /**
     * 设置使用证书
     * @param string $filePath
     */
    public function setCertificate($filePath)
    {
        $this->_certificate = $filePath;
    }
    
    /**
     * 设置提交的header
     *
     * @param array $headers 例如: 
     * array (
            "user_agent" => "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)",
            "language"   => "en-us,en;q=0.5"
       ),
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }
    
    /**
     * 设置浏览器模式.
     * 
     * @param boolean $mode 开启或者关闭.
     * 
     * @return void
     */
    public function setBrowserMode($mode = true)
    {
        $this->_browserMode = $mode;
    }
    
    /**
     * 设置请求的来源地址
     *
     * @param string $referer
     */
    public function setReferer($referer)
    {
        $this->_referer = $referer;
    }
    
    /**
     * 设置代理
     *
     * @param string $proxyHost 代理地址
     * @param int    $proxyPort 代理端口
     * @param string $proxyUser 代理账号
     * @param string $proxyPass 代理密码
     */
    public function setProxy($proxyHost, $proxyPort, $proxyUser = null, $proxyPass = null)
    {
        $this->_proxyHost = $proxyHost;
        $this->_proxyPort = $proxyPort;
        $this->_proxyUser = $proxyUser;
        $this->_proxyPass = $proxyPass;
    }
    
    /**
     * 设置连接超时时间
     * @param int $second
     */
    public function setConnectionTimeout($second)
    {
        $this->_connectTimeout = $second;
    }
    
    /**
     * GET方式发送请求
     *
     * @param  string        $url
     * @param  array|string  $data
     * @param  int           $getType 0:只返回body | 1:只返回header | 2:同时返回header和body
     * @param  string        保存到本地文件地址
     * @return string
     */
    public function get($url, $data = array(), $getType = 2, $tofile = null)
    {
        return $this->send($url, $data, 'get', $getType, $tofile);
    }
    
    /**
     * POST方式发送请求
     *
     * @param  string        $url
     * @param  array|string  $data
     * @param  int           $getType 0:只返回body | 1:只返回header | 2:同时返回header和body
     * @param  string        保存到本地文件地址
     * @return string
     */
    public function post($url, $data = array(), $getType = 2, $tofile = null)
    {
        return $this->send($url, $data, 'post', $getType, $tofile);
    }

    /**
     * 下载到文件
     * @param string $url
     */
    public function download($url, $tofile)
    {
        return $this->send($url, array(), 'get', 0, $tofile);
    }
    
    /**
     * 向地址发送请求
     *
     * @param  string        $url
     * @param  array|string  $data
     * @param  string        $method get | post
     * @param  int           $getType 0:只返回body | 1:只返回header | 2:同时返回header和body
     * @param  string        保存到本地文件的指针
     * @return string
     */
    public function send($url, $data = array(), $method = 'get', $getType = 2, $tofile = null)
    {
        if(empty($this->_ch)){
            $this->init();
        }

        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true); //返回给变量
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true); //抓取跳转后的页面
        curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, $this->_connectTimeout);
        // curl_setopt($this->_ch, CURLOPT_NOPROGRESS, false);  //启用时关闭curl传输的进度条，此项的默认设置为true
        if(!empty($this->_certificate)){
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($this->_ch, CURLOPT_CAINFO, $this->_certificate);
        }else{
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        // 是否使用代理
        if ($this->_proxyHost) {
            curl_setopt($this->_ch, CURLOPT_PROXY, $this->_proxyHost);
            curl_setopt($this->_ch, CURLOPT_PROXYPORT, $this->_proxyPort);
            // 代理是否需要用户账号密码验证
            if ($this->_proxyUser) {
                curl_setopt($this->_ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                curl_setopt($this->_ch, CURLOPT_PROXYUSERPWD, "{$this->_proxyUser}:{$this->_proxyPass}");
            }
        }
        // 设置请求header
        if ($this->_headers) {
            curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $this->_headers);
        }
        // 设置COOKIE
        // $this->_cookie = 'JSESSIONID=BC41C8F105F04572D7E8E3C1963C4684.server-d';
        if ($this->_cookie) {
            curl_setopt($this->_ch, CURLOPT_COOKIE, $this->_cookie);
        }
        // 设置请求来源地址
        if ($this->_referer) {
            curl_setopt($this->_ch, CURLOPT_REFERER, $this->_referer);
        } else {
            curl_setopt($this->_ch, CURLOPT_REFERER, $url);
        }
        // 下载到文件
        if (!empty($tofile)) {
            $tofilept = fopen ($tofile, "wb");
            curl_setopt($this->_ch, CURLOPT_FILE, $tofilept);
        }
        //处理请求方式
        $method = strtolower($method);
        switch ($method){
            case 'get':
                // get请求时需要转换为字符串
                if (is_array($data)) {
                    $data = http_build_query($data);
                }
                if (stripos($url, '?') !== false) {
                    $url .= "&{$data}";
                } else {
                    $url .= "?{$data}";
                }
                curl_setopt($this->_ch, CURLOPT_POST, false);
                break;

            case 'post':
                // 文件上传兼容处理
                if (is_array($data)) {
                    foreach ($data as $k => $v) {
                        if ($v[0] == '@') {
                            if (class_exists('\CURLFile')) {
                                $filePath = substr($v, 1);
                                $data[$k] = new \CURLFile(realpath($filePath));
                            }
                        }
                    }
                }
                curl_setopt($this->_ch, CURLOPT_POST,       true);
                curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $data);
                break;

            default:
                // 设置请求方式
                curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }
        switch ($getType){
            case 0:
                // 只返回body
                curl_setopt($this->_ch, CURLOPT_HEADER, false);         
                // curl_setopt($this->_ch, CURLOPT_NOBODY, false);
                break;
            case 1:
                // 只返回header
                curl_setopt($this->_ch, CURLOPT_HEADER, true);         
                curl_setopt($this->_ch, CURLOPT_NOBODY, true); 
                break;
            case 2:
                // 同时返回header和body
                curl_setopt($this->_ch, CURLOPT_HEADER, true);         
                // curl_setopt($this->_ch, CURLOPT_NOBODY, false);
                break;
        }

        // 设置请求地址
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        $rawContent = curl_exec($this->_ch);
        switch ($getType){
            case 0:
                // 只返回body
                $headerContent = null;
                $bodyContent   = $rawContent;
                break;
            case 1:
                // 只返回header
                $headerContent = $rawContent;
                $bodyContent   = null;
                break;
            case 2:
                // 同时返回header和body
                $headerSize    = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                $headerContent = substr($rawContent, 0, $headerSize);
                $bodyContent   = substr($rawContent, $headerSize);
                break;
        }
        $this->httpCode   = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);
        $this->httpHeader = $this->_parseHeader($headerContent);
        if(isset($this->httpHeader['content-type'])){
            $tArray           = explode(';', $this->httpHeader['content-type']);
            $this->httpType   = $tArray[0];
        }
        $this->httpError  = curl_error($this->_ch);
        if(isset($tofilept) && is_object($tofilept)){
            $tofilept->close();
        }
        // COOKIE保存
        if ($this->_browserMode) {
            $cookie = '';
            if (isset($this->httpHeader['set-cookie'])) {
                $cookie = $this->httpHeader['set-cookie'];
            } else if (isset($this->httpHeader['Set-Cookie'])) {
                $cookie = $this->httpHeader['Set-Cookie'];
            }
            $this->_saveToLocalCookie($cookie);
        }
        //$this->close();
        return $bodyContent;
    }
    
    
    /**
     * 获取指定URL的header，返回的是关联数组
     *
     * @param  string $url
     * @return array
     */
    public function getHeader($url)
    {
        $this->get($url, '', 1);
        return $this->httpHeader;
    }
    
    /**
     * 关闭CURL连接
     *
     */
    public function close()
    {
        if($this->_ch){
            curl_close($this->_ch);
            unset($this->_ch);
        }
    }
    
    /**
     * 初始化curl
     *
     */
    private function init()
    {
        $this->_ch = curl_init();
    }
    
    /**
     * 析构函数
     *
     */
    public function __destruct()
    {
        $this->close();
    }
    
    /**
     * 解析Cookie为数组键值对.
     * 
     * @param string $cookie COOKIE.
     * 
     * @return array
     */
    private function _parseCookie($cookie)
    {
        $cookieArray = array();
        if (!empty($cookie)) {
            $array = explode(';', $cookie);
            foreach ($array as $string) {
                $t = explode('=', trim($string));
                $k = $t[0];
                $v = isset($t[1]) ? $t[1] : '';
                $cookieArray[$k] = $v;
            }
        }
        return $cookieArray;
    }
    
    /**
     * 保存cookie到本地.
     * 
     * @param string $cookie COOKIE;
     * 
     * @return void
     */
    private function _saveToLocalCookie($cookie)
    {
        $cookieArray = $this->_parseCookie($this->_cookie);
        $tempArray   = $this->_parseCookie($cookie);
        foreach ($tempArray as $k => $v) {
            $cookieArray[$k] = $v;
        }
        $cookieString = '';
        foreach ($cookieArray as $k => $v) {
            $cookieString .= "{$k}={$v}; ";
        }
        $cookieString = rtrim($cookieString, '; =');
        $this->_cookie = $cookieString;
    }
    
    /**
     * 解析header，返回的是关联数组
     *
     * @param  string $header
     * @return array
     */
    private function _parseHeader($header)
    {
        $returnArray = array();
        $headerArray = explode("\n", $header);
        foreach ($headerArray as $v){
            $tArray = array();
            $tArray = explode(": ", trim($v));
            if($tArray[0]){
                if(empty($tArray[1])){
                    $returnArray[0] = $tArray[0];
                }else{
                    $returnArray[strtolower($tArray[0])] = $tArray[1];
                }
            }
        }
        return $returnArray;
    }
}