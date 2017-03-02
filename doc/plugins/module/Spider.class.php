<?php
if (!defined('PhpMe')) {
	exit('Include Permission Denied!');
}

/**
 * 采集模型
 *
 */
class Module_Spider extends BaseModule
{
	/**
	 * HTTP 操作对象.
	 * @var Lib_Network_Http
	 */
	public $http;

	public function __construct()
	{
		parent::__construct();
		require_once(FRAME_PATH.'thirdparty/PhpQuery.class.php');
	}

	/**
	 * 获得实例.
	 *
	 * @return Module_Spider
	 */
	public static function instance()
	{
		return self::InstanceInternal(__CLASS__);
	}

	/**
	 * 根据URL获得用于DOM操作的PhpQuery对象
	 *
	 * @param  string  $url
	 * @param  boolean $noScript 是否去掉javascript内容，这样使phpjQuery对象初始化更快
	 * @return object
	 */
	public function getPhpQueryDoc($url, $noScript = true)
	{
		// 通过CURL获取页面内容(当抓取失败时，每隔1秒再次尝试10次请求)
		for ($i=0; $i < 10; $i++) {
			$http    = &$this->getHttp();
			$content = $http->get($url);
			if ($content === false && $http->httpCode == 0) {
				if ($i >= 10) {
					$this->log("网络连接不通，请检查后再试！", 'spider');
					exit();
				}
				$this->log("网络连接不通，第{$i}次尝试！", 'spider');
				sleep(1);
			} else {
				break;
			}
		}
		//判断返回状态是否成功
		if ($http->httpCode != 200) {
			return false;
		}
		// 去掉javascript脚本内容
		if ($noScript) {
			$content = $this->stripScripts($content);
		}
		//检查编码，如果不是utf-8编码，则转换编码
		$charset = null;
		$match   = array();
		preg_match('/<meta.*charset=([^"]*)"/i', $content, $match);
		$charset = strtolower($match[1]);
		if ($charset == 'x-gbk') {
			$charset = 'gbk';
		}
		if ($charset != '' && $charset != 'utf-8') {
			$content = preg_replace('@<meta[^>]+http-equiv\\s*=\\s*(["|\'])Content-Type\\1([^>]+?)>@i', '', $content);
			$content = $this->convertEncode($charset, 'utf-8', $content);
		}

		return phpQuery::newDocument($content);
	}

    /**
	 * 获得http远程调用对象
	 *
	 * @return object
	 */
    public function &getHttp()
    {
        if(empty($this->http)){
            $this->http = new Lib_Network_Http();
            //设置头信息
            $headers = array (
                'User-Agent'      => 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.111 Safari/537.36',
				'Accept-Language' => 'zh-cn,en;q=0.5',
				// 'Accept'	      => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                // 'Accept-Encoding' => 'gzip, deflate',
                // 'Accept-Charset'  => 'UTF-8,*',
                // 'Connection'      => 'keep-alive',
                // 'Cache-Control'   => 'max-age=0'
            );
            //设置HTTP发送信息
            $this->http->setHeaders($headers);
            $this->http->setBrowserMode(true);
			$this->http->setConnectionTimeout(20);
        }
        return $this->http;
    }
	
	/**
     * 转换字符串编码。
     *
     * @param  string $inputCharset 输入编码
     * @param  string $outputCharset 输出编码
     * @param  string $string 字符串
     * @return string 转换后的编码
     */
    public function convertEncode($inputCharset, $outputCharset, $string)
    {
    	if (function_exists('mb_convert_encoding')) {
    		return @mb_convert_encoding($string, $outputCharset, $inputCharset);
    	} else {
    		return @iconv($inputCharset, $outputCharset.'//IGNORE', $string);
    	}
    }

	/**
	 * 下载并保存图片，当返回false时表示图片太小，不符合要求
	 *
	 * @param  string  $src  图片地址(绝对路径)
	 * @return string|false  本地保存地址
	 */
	public function saveImg($src)
	{
		if (!(stristr($src, 'http://') or stristr($src, 'https://') or stristr($src, 'ftp://'))) {
			return false;
		}
		//下载图片
		$http    = &$this->getHttp();
		$content = $http->get($src);
		if($http->httpCode != 200 || strtolower($http->httpType) == 'text/html'){
		    return false;
		}
		//判断文件类型
		if (Lib_FileSys::isImage($src) == false) {
			return false;
		}
		
	    $year   = date('Y',time());
		$month  = date('m',time());
		$day    = date('d',time());
		$hour   = date('H',time());
		$minite = date('i',time());
		$dir    = "upload/image/{$year}/{$month}/{$day}/{$hour}/{$minite}";

		$path   = ROOT_PATH.'/'.$dir;
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		
		//保存图片
		$type     = Lib_FileSys::getFileType($src);
		$fileName = microtime(true).'.'.$type;
		$src      = "{$dir}/{$fileName}";
		file_put_contents($path.'/'.$fileName, $content);

		return $src;
	}
	
	/**
	 * 将内容里的图片连接替换为绝对连接
	 *
	 * @param  string $content
	 * @param  string $feedUrl
	 * @return string
	 */
	public function imgs2Absolute($content, $feedUrl)
	{
		preg_match_all("/\<img.*?src\=\"(.*?)\"[^>]*>/i", $content, $match);
		foreach ($match[1] as $src){
			//替换连接
			$content = str_ireplace($src, $this->toAbsolute($src, $feedUrl), $content);
		}
		return $content;
	}
	
	/**
	 * 相对路径转化成绝对路径
	 *
	 * @param  string $url     href连接路径
	 * @param  string $feedUrl 基础路径
	 * @return string
	 */
	public function toAbsolute($url, $feedUrl) 
	{
		if (empty($url) || stristr($url, 'http://') || stristr($url, 'https://') || stristr($url, 'ftp://')) {
			return $url;
		}
		if ($feedUrl[strlen($feedUrl) - 1] == '/') {
		    $urlPrefix = $feedUrl;
		} elseif ($url[0] == '?') {
		    $urlPrefix = substr($feedUrl, 0, strripos($feedUrl, '?'));
		} else {
		    $urlPrefix = substr($feedUrl, 0, strripos($feedUrl, '/') + 1);
		}
		
		if ($url[0] == '/') {
		    preg_match('/(http|https|ftp):\/\//', $feedUrl, $protocol);
    		$serverUrl = preg_replace("/(http|https|ftp|news):\/\//", "", $feedUrl);
    		$serverUrl = preg_replace("/\/.*/", "", $serverUrl);
    		if ($serverUrl == '') {
    			return $url;
    		}
			$url = $protocol[0].$serverUrl.$url;
		} else {
			$url = $urlPrefix.$url;
		}

		return $url;
	}
	
	/**
	 * 将文本里面的相对路径转化成绝对路径
	 *
	 * @param  string $content
	 * @param  string $feedUrl
	 * @return string
	 */
	public function relative2Absolute($content, $feedUrl) 
	{
		$urls = $this->_getAllUrl($content);
		if ($feedUrl[strlen($feedUrl) - 1] == '/') {
		    $urlPrefix = $feedUrl;
		} else {
		    $urlPrefix = substr($feedUrl, 0, strripos($feedUrl, '/') + 1);
		}
		
		preg_match('/(http|https|ftp):\/\//', $feedUrl, $protocol);
		$serverUrl = preg_replace("/(http|https|ftp|news):\/\//", "", $feedUrl);
		$serverUrl = preg_replace("/\/.*/", "", $serverUrl);

		if ($serverUrl == '') {
			return $content;
		}

		foreach ($urls['url'] as $url) {
			if (stristr($url, 'http://') || stristr($url, 'https://') || stristr($url, 'ftp://')) {
				continue;
			} else {
				if ($url[0] == '/') {
					$content = str_ireplace("href='$url'", "href='{$protocol[0]}{$serverUrl}{$url}'", $content);
					$content = str_ireplace("href=\"$url\"", "href=\"{$protocol[0]}{$serverUrl}{$url}\"", $content);
				} else {
					$content = str_ireplace("href='$url'", "href='{$urlPrefix}{$url}'", $content);
					$content = str_ireplace("href=\"$url\"", "href=\"{$urlPrefix}{$url}\"", $content);
				}
			}
		}
		return $content;
	}
	
	/**
	 * 取得所有链接
	 *
	 * @param  string $content
	 * @return array
	 */
	private function _getAllUrl($content)
	{
		preg_match_all('/<a\s+href=["|\']?([^>"\' ]+)["|\']?\s*[^>]*>([^>]+)<\/a>/i', $content, $arr);
		return array('name' => $arr[2], 'url' => $arr[1]);
	}
	
	/**
	 * 取得域名名称
	 *
	 * @param  string $url
	 * @return string
	 */
	public function getHostName($url)
	{
		$referer = preg_replace("/https?:\/\/([^\/]+).*/i", "\\1", $url);
		//$referer = str_replace("www.", "", $referer);
		return $referer;
	}

	/**
	 * 获取指定标记中的内容
	 *
	 * @param  string $str
	 * @param  string $start
	 * @param  string $end
	 * @return string
	 */
	public function getTagData($str, $start, $end)
	{
		if ( $start == '' || $end == '' ) {
			return;
		}
		$str = explode($start, $str);
		$str = explode($end, $str[1]);
		return $str[0];
	}

	 /**
	  * 去掉内容的超链接
	  *
	  * @param  string $content
	  * @return string
	  */
	public function stripLinks($content)
	{
		return preg_replace("/<a[^>]*href=[^>]*>|<\/a>/i", "", $content);
	}

	/**
	 * 清除HTML代码script
	 *
	 * @param  string $content
	 * @return string
	 */
	public function stripScripts($content)
	{
		return preg_replace("/<script[^>]*?>.*?<\/script>/si", "", $content);
	}

	/**
	 * 清除HTML代码table
	 *
	 * @param  string $content
	 * @return string
	 */
	public function stripTables($content)
	{
		return preg_replace("/<table[^>]*?>.*?<\/table>/si", "", $content);
	}

	/**
	 * 清除HTML代码iframe
	 *
	 * @param  string $content
	 * @return string
	 */
	public function stripIframes($content)
	{
		return preg_replace("/<IFRAME[^>]*?>.*?<\/IFRAME>/si", "", $content);
	}
}