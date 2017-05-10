<?php
/**
 * COOKIE操作封装类
 * 由于COOKIE涉及到加密操作，因此使用专门的类来封装，而SESSION封装比较简单，只是对全局内置变量的引用。
 *
 * @author John
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * COOKIE操作封装类
 */
class Cookie
{
    private $_path;
    private $_domain;
    private $_expire  = 0;
    private $_authkey = '';
    private $_cookies = array();

    /**
     * 构造函数，设置相关成员变量，解密COOKIE并保存.
     *
     * @param string  $path    Path.
     * @param string  $domain  域名.
     * @param integer $expire  过期时间
     * @param string  $authkey 加密key.
     *
     * @return void
     */
    public function __construct($path = null, $domain = null, $expire = 0, $authkey = '')
    {
        $this->_path    = $path;
        $this->_domain  = $domain;
        $this->_expire  = $expire;
        $this->_authkey = $authkey;

        if (empty($this->_domain) && !empty($_SERVER['HTTP_HOST'])) {
            $array             = explode(':', $_SERVER['HTTP_HOST']);
            list($name, $port) = each($array);
            $this->_domain     = '.'.trim(substr($name, strpos($name, '.')), '.');
        }
        if (isset($_COOKIE['Lge_Cookie'])) {
            $this->_cookies = unserialize($this->_authcode($_COOKIE['Lge_Cookie'], false));
            // 过滤已过期的COOKIE
            if (is_array($this->_cookies)) {
                $timestamp = time();
                foreach ($this->_cookies as $k => $v) {
                    if ($v['exp'] < $timestamp) {
                        unset($this->_cookies[$k]);
                    }
                }
            }
        }
    }

    /**
     * 输出加密后的COOKIE
     * @return void
     */
    public function output()
    {
        if (empty($this->_cookies)) {
            $cookie = '';
        } else {
            $cookie = $this->_authcode(serialize($this->_cookies), true, $this->_authkey);
        }
        setcookie('Lge_Cookie', $cookie, time() + $this->_expire, $this->_path, $this->_domain);
    }

    /**
     * 设置COOKIE，保存进COOKIE数组，在页面最后析构的时候进行输出设置
     *
     * @param string  $name   名称
     * @param mixed   $value  数值
     * @param integer $expire 过期时间
     * @return void
     */
    public function set($name, $value, $expire = 0)
    {
        $this->_cookies[$name] = array(
            'val' => $value,
            'exp' => time() + $expire
        );
    }

    /**
     * 获得COOKIE值
     *
     * @param string $name Cookie名称
     * @return mixed
     */
    public function get($name)
    {
        if (isset($this->_cookies[$name])) {
            if ($this->_cookies[$name]['exp'] >= time()) {
                return $this->_cookies[$name]['val'];
            }
        }
    }

    /**
     * 获取所有的cookie值数组.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->_cookies;
    }

    /**
     * 清除某COOKIE值
     *
     * @param string $name Cookie名称
     * @return void
     */
    public function drop($name)
    {
        unset($this->_cookies[$name]);
    }

    /**
     * 字符串解密加密
     *
     * @param  string  $string 字符串
     * @param  boolean $encode 加密或者解密
     * @param  string  $key    键值
     * @param  integer $expire 过期时间
     * @return string
     */
    private function _authcode($string, $encode = true, $key = '', $expire = 0)
    {
        // 随机密钥长度 取值 0-32;
        // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
        // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckeyLength 次方
        // 当此值为 0 时，则不产生随机密钥
        $ckeyLength = 4;

        $key  = md5($key ? $key : $this->_authkey);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckeyLength ? ($encode ? substr(md5(microtime()), - $ckeyLength) : substr($string, 0, $ckeyLength)) : '';

        $cryptkey  = $keya.md5($keya.$keyc);
        $keyLength = strlen($cryptkey);

        if ($encode) {
            $string = sprintf('%010d', $expire ? $expire + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        } else {
            $string = base64_decode(substr($string, $ckeyLength));
        }

        $stringLength = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $keyLength]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j   = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $stringLength; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($encode) {
            return $keyc.str_replace('=', '', base64_encode($result));
        } else {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0)
                && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        }
    }
}