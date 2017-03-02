<?php


if (!defined('PhpMe')) {
    exit('Include Permission Denied!');
}

/**
 * 缓存处理类，主要对于Memcache缓存类的封装。
 *
 * @deprecated
 * @version v0.1 2011-12-20
 */
class Lib_Cache_Memcache
{
    public  $expire    = 3600;  //缓存时间(默认1个小时)
	public  $noError   = false; //当缓存服务器当掉时是否抑制出错信息继续执行
    private $_errored  = false; //是否产生了memcache错误
    private $_memcache = null; 
    private $_options  = null;

    /*
        $_options形如:
        	array(
                'host'   => MEMCACHE_HOST,
                'port'   => MEMCACHE_PORT,
                'expire' => CACHE_EXPIRE
            )
    */
    function __construct($options)
    {
        //Memcache主机以及端口
        $this->_options = array(
            'host' => $options['host'],
            'port' => $options['port']
        );
        //缓存时间
        $this->expire = $options['expire'];
    }
    
    /**
     * 析构函数，关闭Memcache缓存服务器连接
     */
    function __destruct()
    {
        if($this->_memcache){
            $this->_memcache->close();
        }
    }

    /**
     * 写入缓存。
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $ttl
     * @return boolean
     */
    public function set($key, $value, $ttl = null)
    {
        if($this->_errored){
            return false;
        }
        if(!$this->_memcache) {
            $this->_init();
        }
        if(!isset($ttl)) {
        	$ttl = $this->expire;
        }
        //不使用缓存
        if($ttl < 0){
            return true;
        }
        //在64位的服务器上不能使用压缩和存储bool类型的值
        if(is_bool($value)){
            $value = intval($value);
        }
        //return $this->_memcache->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
        return $this->_memcache->set($key, $value, 0, $ttl);
    }

    /**
     * 获取缓存
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        if($this->_errored){
            return null;
        }
        
        if(!$this->_memcache) {
            $this->_init();
        }
        return $this->_memcache->get($key);
    }

    /**
     * 清空缓存
     *
     * @return bool
     */
    public function clear()
    {
        if($this->_errored){
            return false;
        }
        if(!$this->_memcache) {
            $this->_init();
        }
        return $this->_memcache->flush();
    }

    /**
     * 删除主键对应的缓存。
     *
     * @param  string  $key
     * @return boolean
     */
    public function delete($key)
    {
        if($this->_errored){
            return false;
        }
        
        if(!$this->_memcache) {
            $this->_init();
        }
        return $this->_memcache->delete($key);
    }
    
    /**
     * @see $this->delete
     */
    public function drop($key)
    {
        return $this->delete($key);
    }
    
    /**
     * 初始化Memcache服务器。
     *
     */
    private function _init()
    {
        //连接到缓存服务器
        if(!$this->_connect($this->_options)){
            if($this->noError){
                $this->_errored = true;
                return false;
            }else{
               exit('Memcache Connect failed!'); 
            }
        }
    }

    /**
     * 连接到缓存服务器
     * 
     * @param  array $options
     * @return bool
     */
    private function _connect($options)
    {
        if (empty($options)) { 
            return false;
        }
        $this->_memcache = new Memcache;
        return @$this->_memcache->connect($options['host'], $options['port']);
    }
}
?>