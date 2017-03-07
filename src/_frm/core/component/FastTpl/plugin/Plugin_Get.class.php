<?php
/**
 * GET参数对象.
 * 
 * @author john
 */
namespace Lge;

class Plugin_Get
{
    /**
     * 参数.
     * 
     * @var array
     */
    public $data = array();
    
    public function __construct()
    {
        $this->data = &$_GET;
    }
    
    /**
     * 获取一项GET参数.
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