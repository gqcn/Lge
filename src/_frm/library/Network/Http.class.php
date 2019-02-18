<?php
/**
 * HTTP请求封装类，内部封装了CURL方法
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * HTTP请求封装类，内部封装了CURL方法
 */
class Lib_Network_Http
{
    public $httpCode;   // 状态码
    public $httpType;   // Content-Type值，只包含文件类型，不包含编码等其他信息
    public $httpError;  // 如果发生错误则返回错误信息，与httpCode相关
    public $httpHeaderArray   = array(); // 返回的header信息 - 关联数组(其中也包含了http状态码信息)
    public $httpHeaderContent = '';      // 返回的header信息 - 原始字符串

    private $_ch;                // CURL对象
    private $_cookie  = '';      // 请求时需要提交的cookie
    private $_headers = array(); // 请求时需要提交的header
    private $_referer = '';      // 请求时需要提交的来源地址
    private $_proxyHost;
    private $_proxyPort;
    private $_proxyUser;
    private $_proxyPass;
    private $_certificate    = '';    // SSL证书地址绝对路径
    private $_browserMode    = false; // 浏览器模式, cookie会伴随着整个请求流程,并且请求会自动保存返回的cookie
    private $_timeout        = 10;    // 执行超时时间
    private $_connectTimeout = 10;    // 连接超时时间

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
     *
     * @param string $cookie 例如: session=c36f5eba6978450b12; domain=.iteye.com; path=/; HttpOnly
     *
     * @return void
     */
    public function setCookie($cookie)
    {
        $this->_cookie = $cookie;
    }
    
    /**
     * 设置使用证书
     *
     * @param string $filePath 证书绝对路径
     *
     * @return void
     */
    public function setCertificate($filePath)
    {
        $this->_certificate = $filePath;
    }
    
    /**
     * 设置提交的header
     *
     * @param array $headers 例如:
     *
     * array (
            "user_agent" => "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)",
            "language"   => "en-us,en;q=0.5"
       ),
     *
     * @return void
     */
    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
    }
    
    /**
     * 设置浏览器模式，模拟浏览器模式请求，连续的访问请求下，所有的cookie将会自动保存.
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
     * @param string $referer 来源地址
     *
     * @return void
     */
    public function setReferer($referer)
    {
        $this->_referer = $referer;
    }
    
    /**
     * 设置代理
     *
     * @param string  $proxyHost 代理地址
     * @param integer $proxyPort 代理端口
     * @param string  $proxyUser 代理账号
     * @param string  $proxyPass 代理密码
     *
     * @return void
     */
    public function setProxy($proxyHost, $proxyPort, $proxyUser = null, $proxyPass = null)
    {
        $this->_proxyHost = $proxyHost;
        $this->_proxyPort = $proxyPort;
        $this->_proxyUser = $proxyUser;
        $this->_proxyPass = $proxyPass;
    }

    /**
     * 设置请求执行超时时间
     *
     * @param integer $second 秒
     *
     * @return void
     */
    public function setTimeout($second)
    {
        $this->_timeout = $second;
    }

    /**
     * 设置请求连接超时时间
     *
     * @param integer $second 秒
     *
     * @return void
     */
    public function setConnectionTimeout($second)
    {
        $this->_connectTimeout = $second;
    }
    
    /**
     * GET方式发送请求
     *
     * @param string       $url     请求地址
     * @param array|string $data    请求数据
     * @param integer      $getType 0:只返回header | 1:只返回body | 2:同时返回header和body
     * @param string       $tofile  保存到本地文件地址
     *
     * @return string
     */
    public function get($url, $data = array(), $getType = 1, $tofile = null)
    {
        return $this->send($url, $data, 'get', $getType, $tofile);
    }
    
    /**
     * POST方式发送请求
     *
     * @param string       $url     请求地址
     * @param array|string $data    请求数据
     * @param integer      $getType 0:只返回header | 1:只返回body | 2:同时返回header和body
     * @param string       $tofile  保存到本地文件地址
     *
     * @return string
     */
    public function post($url, $data = array(), $getType = 1, $tofile = null)
    {
        return $this->send($url, $data, 'post', $getType, $tofile);
    }

    /**
     * 下载到文件
     *
     * @param string $url    请求地址
     * @param string $tofile 保存到本地文件地址
     *
     * @return string
     */
    public function download($url, $tofile)
    {
        return $this->send($url, array(), 'get', 1, $tofile);
    }
    
    /**
     * 向地址发送请求
     *
     * @param string       $url     请求地址
     * @param array|string $data    请求数据
     * @param string       $method  请求方式
     * @param integer      $getType 0:只返回header | 1:只返回body | 2:同时返回header和body
     * @param string       $tofile  保存到本地文件地址
     *
     * @return string
     */
    public function send($url, $data = array(), $method = 'get', $getType = 1, $tofile = null)
    {
        if (empty($this->_ch)) {
            $this->_init();
        }

        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true); // 返回给变量
        curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, true); // 抓取跳转后的页面
        curl_setopt($this->_ch, CURLOPT_TIMEOUT,        $this->_timeout);
        curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, $this->_connectTimeout);
        // curl_setopt($this->_ch, CURLOPT_NOPROGRESS, false);  //启用时关闭curl传输的进度条，此项的默认设置为true
        if (!empty($this->_certificate)) {
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($this->_ch, CURLOPT_CAINFO, $this->_certificate);
        } else {
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
            $tofilept = fopen($tofile, "wb");
            curl_setopt($this->_ch, CURLOPT_FILE, $tofilept);
        }
        // 处理请求方式
        $method = strtolower($method);
        switch ($method) {
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
                        if (isset($v[0]) && $v[0] == '@') {
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
                curl_setopt($this->_ch, CURLOPT_POSTFIELDS,    $data);
                curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
        }
        switch ($getType) {
            case 0:
                // 只返回header
                curl_setopt($this->_ch, CURLOPT_HEADER, true);
                curl_setopt($this->_ch, CURLOPT_NOBODY, true);
                break;
            case 1:
            case 2:
                // 只返回body，或同时返回header和body
                curl_setopt($this->_ch, CURLOPT_HEADER, true);
                curl_setopt($this->_ch, CURLOPT_NOBODY, false);
                break;
        }

        // 设置请求地址
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        $rawContent    = curl_exec($this->_ch);
        $resultContent = '';
        switch ($getType) {
            case 0:
                // 只返回header
                $headerContent = $rawContent;
                $resultContent = $headerContent;
                break;
            case 1:
            case 2:
                // 只返回body，或同时返回header和body
                $headerSize    = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                $headerContent = substr($rawContent, 0, $headerSize);
                $bodyContent   = substr($rawContent, $headerSize);
                if ($getType == 1) {
                    $resultContent = $bodyContent;
                } else {
                    $resultContent = $rawContent;
                }
                break;
        }
        $this->httpCode          = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);
        $this->httpHeaderArray   = $this->_parseHeader($headerContent);
        $this->httpHeaderContent = $headerContent;
        if (!empty($this->httpHeaderArray['content-type'])) {
            if (is_array($this->httpHeaderArray['content-type'])) {
                $this->httpHeaderArray['content-type'] = $this->httpHeaderArray['content-type'][count($this->httpHeaderArray['content-type']) - 1];
            }
            $tArray           = explode(';', $this->httpHeaderArray['content-type']);
            $this->httpType   = $tArray[0];
        }
        $this->httpError  = curl_error($this->_ch);
        if (isset($tofilept) && is_object($tofilept)) {
            $tofilept->close();
        }
        // COOKIE保存
        if ($this->_browserMode) {
            $cookie = '';
            if (isset($this->httpHeaderArray['set-cookie'])) {
                $cookie = $this->httpHeaderArray['set-cookie'];
            } elseif (isset($this->httpHeaderArray['Set-Cookie'])) {
                $cookie = $this->httpHeaderArray['Set-Cookie'];
            }
            $this->_saveToLocalCookie($cookie);
        }
        // $this->close();
        return $resultContent;
    }
    
    /**
     * 获取指定URL的header，返回的是关联数组
     *
     * @param string $url 请求地址
     *
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
     * @return void
     */
    public function close()
    {
        if (!empty($this->_ch)) {
            curl_close($this->_ch);
            unset($this->_ch);
        }
    }
    
    /**
     * 初始化curl
     *
     * @return void
     */
    private function _init()
    {
        $this->_ch = curl_init();
    }
    
    /**
     * 析构函数
     *
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }
    
    /**
     * 解析Cookie为数组键值对.
     *
     * @param mixed $inputCookie 返回的COOKIE数值，可能是数组.
     *
     * @return array
     */
    private function _parseCookie($inputCookie)
    {
        $cookieArray = array();
        if (is_array($inputCookie)) {
            $cookies = $inputCookie;
        } else {
            $cookies = array($inputCookie);
        }
        if (!empty($cookies)) {
            foreach ($cookies as $cookie) {
                $array = explode(';', $cookie);
                if (!empty($array[0])) {
                    $string = $array[0];
                    $t = explode('=', trim($string));
                    $k = $t[0];
                    $v = isset($t[1]) ? $t[1] : '';
                    $cookieArray[$k] = $v;
                }
            }
        }
        return $cookieArray;
    }
    
    /**
     * 保存cookie到本地.
     *
     * @param mixed $cookie COOKIE;
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
     * @param string $header HTTP HEADER字符串
     *
     * @return array
     */
    private function _parseHeader($header)
    {
        $returnArray = array();
        $headerArray = explode("\n", $header);
        foreach ($headerArray as $v) {
            $tArray = explode(": ", trim($v));
            if (!empty($tArray[0])) {
                if (empty($tArray[1])) {
                    $returnArray[0] = $tArray[0];
                } else {
                    $key = strtolower($tArray[0]);
                    if (isset($returnArray[$key])) {
                        if (is_array($returnArray[$key])) {
                            $returnArray[$key][] = $tArray[1];
                        } else {
                            $returnArray[$key] = array(
                                $returnArray[$key],
                                $tArray[1]
                            );
                        }
                    } else {
                        $returnArray[$key] = $tArray[1];
                    }
                }
            }
        }
        return $returnArray;
    }

}
