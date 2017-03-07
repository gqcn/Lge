<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * 终端参数解析，使用方式:
 * 
 * php xxx.php --param=xxx --param2=xxx
 * 
 * ConsoleOption::ParseFromArgv();
 * 
 * @author john
 *
 */
class Lib_ConsoleOption
{
    /**
     * 带参数名称的终端值数组.
     * 
     * @var array
     */
    private $options = array();
    
    /**
     * 不带参数名称的终端值数组.
     * 
     * @var array
     */
    private $values  = array();
    
    /**
     * 解析终端参数，并返回终端参数对象.
     * 
     * @return Lib_ConsoleOption
     */
    static public function instance()
    {
        global $argv, $argc;
        static $options = null;
        if (empty($options)) {
            $options = new Lib_ConsoleOption();
            for($i = 1; $i < $argc; $i++) {
                $s = $argv[$i];
                if (substr($s, 0, 2) == '--') {
                    $s = substr($s, 2);
                    $a = explode('=', $s, 2);
                    if(count($a) == 2) {
                        $options->addOptionValue($a[0], $a[1]);
                    } else {
                        $options->addOptionValue($a[0], true);
                    }
                } else {
                    $options->addValue($s);
                }
            }
        }
        return $options;
    }
    
    /**
     * 添加有参数名称的终端值.
     * 
     * @param string $k Key.
     * @param string $v Value.
     * 
     * @return void
     */
    public function addOptionValue($k, $v)
    {
        if( ! isset($this->options[$k])) {
            $this->options[$k] = $v;
        } else {
            if( ! is_array($this->options[$k]) ) {
                $this->options[$k] = array($this->options[$k]);
            }
            $this->options[$k][] = $v;
        }
    }

    /**
     * 添加没有参数名称的终端值.
     * 
     * @param mixed $v 终端值.
     * 
     * @return void
     */
    public function addValue($v)
    {
        $this->values[] = $v;
    }

    /**
     * 根据参数名称获取终端参数值.
     * 
     * @param string $k   参数名称.
     * @param mixed  $def 当参数不存在时返回的默认值.
     * 
     * @return mixed
     */
    public function getOption($k, $def = null)
    {
        return isset($this->options[$k]) ? $this->options[$k] : $def;
    }

    /**
     * 获得所有没有参数名称的终端值数组.
     * 
     * @return array
     */
    public function &getValues()
    {
        return $this->values;
    }
    
    /**
     * 获得带参数名称的终端值数组.
     * 
     * @return array
     */
    public function &getOptions()
    {
        return $this->options;
    }
}
