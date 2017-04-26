<?php
/**
 * 简单高效的模板引擎.
 * 7个标签，1个类，带有文件缓存功能，速度超级快。
 *
 * 1. foreach:
 *     {foreach from=$array index=$index key=$key item=$item} {/foreach }
 * 2. for
 *     {for name=$i min=0 max=15 step=2}{/for}
 * 3. if
 *     {if $value == xxx || $value = xxx && ($value != xxx || $value == xxx)}
 *     {else if $value == xxx}
 *     {else}
 *     {/if}
 * 4. 变量显示
 *     {$value}
 * 5. 变量赋值
 *     {$value=xxx}
 * 6. 模板加载
 *     {include file.tpl }
 * 7. 注释
 *     {*注释*}
 *
 * PHP代码支持
 *     <?php php代码，只有在设置允许的条件下才能使用 ?>
 *
 * @author John
 */

namespace Lge;

/**
 * 注意模板标签替换应该和模板一样不能增删行数，以便产生错误时给用户提醒定位.
 *
 */
class FastTpl
{
    /**
     * 模板引擎选项.
     * @var array
     */
    public $options = array(
        'tpl_ext'         => 'tpl',          // 扩展名(安全原因，不能让传入模板带后缀)
        "tpl_dir"         => '',             // 模板存放目录
        "cache_dir"       => '',             // 缓存目录
        'totally_php'     => false,          // 是否使用PHP模板(模板不使用自定义标签，完全使用PHP，不需要解析，直接包含，解析效率会高很多)
        "plugin_dirs"     => array(),        // 插件目录列表，用于自动加载插件时的搜索
        'php_enabled'     => true,           // 是否允许PHP代码嵌入
        'check_update'    => true,           // 是否每次都更新生成缓存文件
        'checksum'        => 'FastTpl v1.5', // 生成缓存文件需要的校验字符串
        'max_tpl_count'   => 50,             // 允许模板嵌套的最大数量(防止死循环嵌套)
        'max_for_loop'    => 10000,          // 允许循环的最大次数(防止死循环)
    );

    /**
     * 模板标签.
     * @var array
     */
    public $tags    = array(
        'variable'      => array('({\$[^=]+?})',        '{(\$[^=]+?)}'),
        'assign'        => array('({\$.+?=.+?})',       '{((\$.+?)=(.+?))}'),
        'if'            => array('({if\s+.*?})',        '{if\s+(.*?)}'),
        'elseif'        => array('({elseif\s+.*?})',    '{elseif\s+(.*?)}'),
        'else'          => array('({else})',            '{else}'),
        'if_close'      => array('({\/if})',            '{\/if}'),
        'foreach'       => array('({foreach\s+.*?})',   '{foreach\s+(from\s*=(.*?)\s+index\s*=(.*?)\s+key\s*=(.*?)\s+item\s*=(.*?))}'),
        'foreach_close' => array('({\/foreach})',       '{\/foreach}'),
        'for'           => array('({for\s+.*?})',       '{for\s+(name\s*=(.*?)\s+min\s*=(.*?)\s+max\s*=(.*?)\s+step\s*=(.*?))}'),
        'for_close'     => array('({\/for})',           '{\/for}'),
        'include'       => array('({include\s+(.*?)})', '{include\s+(.*?)}'),

    );
    
    /**
     * 模板变量
     * @var array
     */
    public $vars = array();
    
    /**
     * 当前进程已解析过的模板文件.
     *
     * @var array
     */
    private $_parsedTpl = array();
    
    /**
     * 使用print_r打印的变量类型.
     *
     * @var array
     */
    private $_printrTypes = array('array', 'object', 'resource');

    /**
     * FastTpl constructor.
     *
     * @param array $options 配置项
     *
     * @return void
     */
    public function __construct(array $options)
    {
        $this->setOptions($options);
        // 缓存目录权限判断
        if (!empty($this->options['cache_dir']) && !is_dir($this->options['cache_dir'])) {
            @mkdir($this->options['cache_dir'], 0777, true);
            @chmod($this->options['cache_dir'], 0777);
        }
        if (!empty($this->options['cache_dir']) && !is_writable($this->options['cache_dir'])) {
            // 缓存目录不可写
            exception('Cache folder is not writable by current process user');
        }
        // 默认插件目录
        $this->options['plugin_dirs'][] = __DIR__.'/plugin/';
    }
    
    /**
     * 设置模板参数.
     *
     * @param array $options 模板参数.
     *
     * @return void
     */
    public function setOptions(array $options)
    {
        foreach ($options as $k => $v) {
            if ($k == 'plugin_dirs') {
                $this->options[$k] = array_merge($this->options[$k], $v);
            } else {
                $this->options[$k] = $v;
            }
        }
    }
    
    /**
     * 添加插件目录.
     *
     * @param string $dirPath 插件目录绝对路径.
     *
     * @return void
     */
    public function addPluginDir($dirPath)
    {
        $this->options['plugin_dirs'][] = $dirPath;
    }

    /**
     * 清除所有模板缓存文件.
     *
     * @return void
     */
    public function clearCache()
    {
        $dirs = array($this->options['cache_dir']);
        while (true) {
            foreach ($dirs as $k => $dir) {
                $files = scandir($dir);
                foreach ($files as $file) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    $filePath = $dir.$file;
                    if (is_dir($filePath)) {
                        $dirs[] = $filePath.'/';
                    } else {
                        @unlink($filePath);
                    }
                }
            }
            if (empty($dirs)) {
                break;
            }
        }
    }

    /**
     * 模板变量设置.
     *
     * @param string $var   变量名称.
     * @param mixed  $value 变量内容.
     *
     * @return void
     */
    public function assign($var, $value)
    {
        $this->vars[$var] = $value;
    }
    
    /**
     * 模板变量设置.
     *
     * @param array $vars 模板变量.
     *
     * @return void
     */
    public function assigns(array $vars)
    {
        foreach ($vars as $k => $v) {
            $this->vars[$k] = $v;
        }
    }
    
    /**
     * 获得模板解析后展示的内容.
     *
     * @param string $file 文件名(不带扩展名)
     *
     * @return string
     */
    public function getDisplayContent($file)
    {
        // 由于模板引擎对于代码的宽松性，这里屏蔽模板文件中的非严重错误信息
        $oldErrorReporting = ini_get('error_reporting');
        $newErrorReporting = E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE & ~E_USER_NOTICE & ~E_WARNING & ~E_USER_WARNING;
        error_reporting($newErrorReporting);

        extract($this->vars);
        $parsedTplPath = $this->parseTpl($file);
        ob_start();
        include($parsedTplPath);
        $content = ob_get_contents();
        ob_end_clean();

        error_reporting($oldErrorReporting);
        return $content;
    }
    
    /**
     * 显示模板, 缓冲功能以便在错误产生时不会把代码错误显示给用户.
     *
     * @param string $file 文件名(不带扩展名)
     *
     * @return void
     */
    public function display($file)
    {
        echo $this->getDisplayContent($file);
    }
    
    /**
     * 解析模板文件.
     *
     * @param string $file 文件名(不带扩展名)
     *
     * @return string
     */
    public function parseTpl($file)
    {
        static $parsedCount;
        if (++$parsedCount > $this->options['max_tpl_count']) {
            // 模板解析数量最多支持{$this->options['max_tpl_count']}个
            exception("Max tpl number({$this->options['max_tpl_count']}) exceeded");
        }
        $tplPath = $this->_getTplPath($file);
        if ($tplPath === false) {
            // 模板文件不存在
            exception("Tpl file not found:{$file}");
        } elseif (!empty($this->options['totally_php'])) {
            return $tplPath;
        } elseif (isset($this->_parsedTpl[$tplPath])) {
            return $this->_parsedTpl[$tplPath];
        } else {
            $parsedTplPath = $this->_getParsedTplPath($file);
            if ($this->options['check_update'] || !file_exists($parsedTplPath)) {
                $content = file_get_contents($tplPath);
                // 优先处理注释内容，由于注释内容比较复杂，并且出于性能考虑，因此优先处理
                $content = preg_replace_callback('/{\*(.*?)\*}/s', array($this, 'parseCommentCallback'), $content);
                // 其次处理PHP代码
                $content = preg_replace_callback('/<\?(.*?)\?>/s', array($this, 'parsePhpCallback'), $content);
                // 最后处理模板标签解析
                $content = preg_replace_callback('/\{[^{]*?\}/s',  array($this, 'parseTagCallback'), $content);
                // 将临时更换标签的PHP代码还原
                $content = preg_replace_callback('/=##=PHP=##=(.*?)=##=PHP=##=/s', array($this, 'parsePhpCallback'), $content);
                // 缓存文件处理
                $dirPath = substr($parsedTplPath, 0, strrpos($parsedTplPath, '/'));
                if (!file_exists($dirPath)) {
                    mkdir($dirPath, 0777, true);
                    chmod($dirPath, 0777);
                }
                file_put_contents($parsedTplPath, $content);
                chmod($parsedTplPath, 0777);
            }
            $this->_parsedTpl[$tplPath] = $parsedTplPath;
            return $parsedTplPath;
        }
    }

    /**
     * 模板注释标签处理回调函数.
     *
     * @param array $match 标签内容匹配数组.
     *
     * @return string
     */
    public function parseCommentCallback(array $match)
    {
        $count = substr_count($match[0], "\n");
        $code  = '';
        // 换行字符用于保证模板解析文件的行数与模板原文件对应，方便定位错误
        while ($count > 0) {
            $code .= "\n";
            --$count;
        }
        return "<?php {$code} ?>";
    }

    /**
     * PHP标签处理回调函数.
     *
     * @param array $match 标签内容匹配数组.
     *
     * @return string
     */
    public function parsePhpCallback(array $match)
    {
        $return = $match[0];
        if (!empty($match[0][0]) && !empty($match[1])) {
            if ($match[0][0] == '<') {
                $phpContent = base64_encode($match[1]);
                $return     = "=##=PHP=##={$phpContent}=##=PHP=##=";
            } else {
                $phpContent = base64_decode($match[1]);
                if ($this->options['php_enabled']) {
                    $return = "<?{$phpContent}?>";
                } else {
                    // 如果不允许执行PHP代码，那么过滤掉模板中的所有PHP代码
                    $return = "<?php echo '<?'; ?>{$phpContent}<?php echo '?>'; ?>";
                }
            }
        }

        return $return;
    }
    
    /**
     * 模板解析标签处理回调函数.
     *
     * @param array $match 标签内容匹配数组.
     *
     * @return string
     */
    public function parseTagCallback(array $match)
    {
        $code  = false;
        $html  = $match[0];
        foreach ($this->tags as $key => $item) {
            if (preg_match("/{$this->tags[$key][0]}/sx", $html, $match2)) {
                $code = $this->_parseTag($key, $match2[0]);
                break;
            }
        }
        return ($code === false) ? $html : $code;
    }
    
    /**
     * 处理标签内容.
     *
     * @param string $tag  标签.
     * @param string $html 标签内容.
     *
     * @return string
     */
    private function _parseTag($tag, $html)
    {
        $return = '';
        switch ($tag) {
            case 'else':
                $return = '} else {';
                break;
                
            case 'if_close':
            case 'for_close':
            case 'foreach_close':
                $return = '}';
                break;
                
            case 'comment':
                $return = '';
                break;
                
            default:
                if (preg_match("/{$this->tags[$tag][1]}/sx", $html, $match)) {
                    switch ($tag) {
                        case 'variable':
                            $return = $this->_parseVar($match[1]);
                            break;
                            
                        case 'assign':
                            $op1    = trim($match[2]);
                            $op2    = trim($match[3]);
                            $return = "{$op1} = {$op2};";
                            break;

                        case 'if':
                            $return = "if ({$match[1]}) {";
                            break;
                            
                        case 'elseif':
                            $return = "} elseif ({$match[1]}) {";
                            break;
                            
                        case 'foreach':
                            if (isset($match[2]) && isset($match[3]) && isset($match[4]) && isset($match[5])) {
                                $index  = trim($match[3]);
                                $return = "{$index} = -1; foreach ({$match[2]} as {$match[4]} => {$match[5]}) {{$index}++;";
                            } else {
                                // {$html} 编写不符合模板规范 - 缺少foreach循环必要参数
                                exception("Invalid tag usage, incomplete foreach params: {$html}");
                            }
                            break;
                            
                        case 'for':
                            if (isset($match[2]) && isset($match[3]) && isset($match[4]) && isset($match[5])) {
                                $loop    = '$_tmp'.(microtime(true) * 10000).rand(0, 9999);
                                $return  = "{$loop} = 0; for ({$match[2]} = {$match[3]}; {$match[2]} <= {$match[4]}; {$match[2]} += {$match[5]}) { if (++{$loop} > {$this->options['max_for_loop']}) { echo '最大循环次数不能超过{$this->options['max_for_loop']}!'; }";
                            } else {
                                // {$html} 编写不符合模板规范 - 缺少for循环必要参数
                                exception("Invalid tag usage, incomplete for params: {$html}");
                            }
                            break;
                            
                        case 'include':
                            $file = trim($match[1], '\'"');
                            // 简单的安全检测，里面不能包含完整的statement语法语句(以;号判断)
                            if (stripos($file, ';') === false) {
                                if (stripos($file, '$') !== false) {
                                    $include = "\$this->parseTpl({$file})";
                                    $return  = "include({$include});";
                                } else {
                                    $include = $this->parseTpl($file);
                                    $include = str_replace($this->options['cache_dir'], '', $include);
                                    $return  = "include(\$this->options['cache_dir'].'{$include}');";
                                }
                            }

                            break;
                    }
                }
                break;
        }
        if (!empty($return)) {
            // 自动加载自定义对象检查(格式是特殊的"$_大写字母"开头,因此规避了不允许在模板标签中函数调用的问题)
            preg_match_all("/\\$(_[A-Z]{1}\w+?)\->.+?/sx", $return, $matches);
            if (!empty($matches[1])) {
                $classes = array_unique($matches[1]);
                foreach ($classes as $class) {
                    $className = "Plugin{$class}";
                    foreach ($this->options['plugin_dirs'] as $index => $dirPath) {
                        // 文件搜索只会在模板解析时产生，不用担心效率问题
                        if (file_exists("{$dirPath}{$className}.class.php")) {
                            $return = "if(empty(\${$class})){require_once(\$this->options['plugin_dirs'][{$index}].'{$className}.class.php');\${$class} = new \Lge\\{$className}();}{$return}";
                            break;
                        }
                    }
                }
            }
            $return = "<?php {$return} ?>";
        }
        return $return;
    }
    
    /**
     * 根据模板名称获得模板文件绝对路径。
     * 注意：
     * 模板文件参数支持不带后缀名和带后缀名，支持相对路径和绝对路径，但绝对路径容易产生安全问题，建议都采用相对路径形式。
     *
     * @param string $file 模板文件名称
     *
     * @return string | false
     */
    private function _getTplPath($file)
    {
        if (strpos($file, '.') !== false) {
            $fileNames = array($file, $file.$this->options['tpl_ext']);
        } else {
            $fileNames = array($file.$this->options['tpl_ext'], $file);
        }
        foreach ($fileNames as $fileName) {
            $filePath = $this->options['tpl_dir'].$fileName;
            $realPath = realpath($filePath);
            if (empty($realPath)) {
                $realPath = realpath($fileName);
            }
            if (!empty($realPath)) {
                break;
            }
        }
        return $realPath;
    }
    
    /**
     * 根据模板名称获得模板缓存文件绝对路径.
     *
     * @param string $file 模板名称
     *
     * @return string | false
     */
    private function _getParsedTplPath($file)
    {
        $parsedTplPath = false;
        $tplPath       = $this->_getTplPath($file);
        if (!empty($tplPath)) {
            $parsedTplPath = $this->options['cache_dir'].$file.'.'.md5("{$tplPath}_{$this->options['checksum']}").'.ftpl.php';
        }
        return $parsedTplPath;
    }
    
    /**
     * 解析变量标签.
     *
     * @param string $html 标签内容
     *
     * @return string
     */
    private function _parseVar($html)
    {
        $html   = trim($html);
        $return = "\$this->_printVar({$html});";
        return $return;
    }
    
    /**
     * (用在模板解析中)在模板中打印展示内容.
     *
     * @param mixed $var 变量
     *
     * @return void
     */
    private function _printVar($var)
    {
        if (isset($var)) {
            if (in_array(gettype($var), $this->_printrTypes)) {
                print_r($var);
            } else {
                echo $var;
            }
        }
    }

}
