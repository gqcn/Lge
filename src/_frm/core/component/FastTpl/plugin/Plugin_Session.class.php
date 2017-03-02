<?php
/**
 * SESSION参数对象.
 * 
 * @author john
 */

class Plugin_Session
{
    /**
     * 参数.
     * 
     * @var array
     */
    public $data = array();
    
    public function __construct()
    {
        // 这里不能使用引用，防止模板赋值给$this->data -> $_SESSION
        $this->data = $_SESSION;
    }
    
    /**
     * 获取一项SESSION参数.
     * 
     * @param string $name 参数名称.
     * 
     * @return string
     */
    public function get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
}