<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
* 当需要伪静态时，需要这几句代码：
if(strstr($_SERVER['REQUEST_URI'],'.html'))//是否使用了伪静态处理
{
    $queryString=strReplace("{$_SERVER['SCRIPT_NAME']}/",'',urldecode($_SERVER['REQUEST_URI']));//必须对URL解码
    $queryString=substr($queryString,0,strrpos($queryString,'.'));//这句是去掉尾部的.html,注意自己写的脚本都是以.html结尾的，这样便于处理
    $vars = explode("/",$queryString);
    $count=count($vars);
    for($i=0;$i<$count;$i+=2)
    {
        $_GET["{$vars[$i]}"]=$vars[$i+1];
        $_SERVER['QUERY_STRING'].="&{$vars[$i]}={$vars[$i+1]}";
    }
    $_SERVER['QUERY_STRING'][0]=null;
    $_SERVER['QUERY_STRING']=ltrim($_SERVER['QUERY_STRING']);
}

* 模式四种分页模式：
   require_once('../libs/classes/page.class.php');
   $page=new Page(array('total'=>1000,'perpage'=>20));
   echo 'mode:1'.$page->show();
   echo '<hr>mode:2'.$page->show(2);
   echo '<hr>mode:3'.$page->show(3);
   echo '<hr>mode:4'.$page->show(4);
   开启AJAX：
   $ajaxpage=new page(array('total'=>1000,'perpage'=>20,'ajax'=>'ajaxPage','pageName'=>'test'));
   echo 'mode:1'.$ajaxpage->show();
   采用继承自定义分页显示模式：
   demo:[url=http://www.phpobject.net/blog]http://www.phpobject.net/blog[/url]
 * 
*/
class Lib_Page
{
    /**
    * config ,public
    */
    var $pageName       =   "page"; //page标签，用来控制url页。比如说xxx.php?PBPage=2中的PBPage
    var $nextPage       =   '>';    //下一页
    var $prePage        =   '<';    //上一页
    var $firstPage      =   '|<';   //首页
    var $lastPage       =   '>|';   //尾页
    var $pre_bar        =   '<<';   //上一分页条
    var $next_bar       =   '>>';   //下一分页条
    var $formatLeft     =   '';
    var $formatRight    =   '';
    var $isAjax         =   false;  //是否支持AJAX分页模式
    var $isFstatic      =   false;  //是否使用伪静态URL方式
    var $totalSize      =   0;

    /**
    * private
    *
    */ 
    var $pagebarNum     =   10;     //控制记录条的个数。
    var $totalPage      =   0;      //总页数
    var $ajaxActionName =   ''; //AJAX动作名
    var $currentPage    =   1;      //当前页
    var $url            =   "";     //url地址头
    var $offset         =   0;

    /**
    * constructor构造函数
    *
    * @param array $array['total'],$array['perpage'],$array['currentPage'],$array['url'],$array['ajax']...
    */
    function __construct($array)
    {
        if (is_array($array)) {
            if (!array_key_exists('total', $array)) {
                $this->error(__FUNCTION__,'need a param of total');
            }
            $total       = intval($array['total']);
            $perpage     = (array_key_exists('perpage',$array))?intval($array['perpage']):10;
            $currentPage = (array_key_exists('currentPage',$array))?intval($array['currentPage']):'';
            $url         = (array_key_exists('url',$array))?$array['url']:'';
        } else {
            $total       = $array;
            $perpage     = 10;
            $currentPage ='';
            $url         = '';
        }
        if ((!is_int($total)) || ($total < 0)) {
            return;
            $this->error(__FUNCTION__, $total.' is not a positive integer!');
        }
        if ((!is_int($perpage)) || ($perpage <= 0)) {
            return;
            $this->error(__FUNCTION__, $perpage.' is not a positive integer!');
        }
        //的确使用了伪静态
        if(strstr($_SERVER['REQUEST_URI'], '.html')){
            $this->isFstatic = true;
        }
        if(!empty($array['pageName'])) {
            //设置pagename
            $this->set('pageName', $array['pageName']);
        }
        $this->SetCurrentPage($currentPage);//设置当前页
        $this->SetUrl($url);//设置链接地址
        $this->totalSize  = $total;
        $this->totalPage  = ceil($total/$perpage);
        $this->offset     = ($this->currentPage-1)*$perpage;
        if(!empty($array['ajax'])){
            $this->openAjax($array['ajax']);//打开AJAX模式
        }

    }
    
    /**
    * 设定类中指定变量名的值，如果改变量不属于这个类，将throw一个exception
    *
    * @param string $var
    * @param string $value
    */
    function set($var, $value)
    {
        if(inArray($var,get_object_vars($this))) {
            $this->$var = $value;
        } else {
            $this->error(__FUNCTION__, $var." does not belong to PB_Page!");
        }

    }
    
    /**
    * 打开倒AJAX模式
    *
    * @param string $action 默认ajax触发的动作。
    */
    public function openAjax($action)
    {
        $this->isAjax          = true;
        $this->ajaxActionName = $action;
    }
    
    /**
    * 获取显示"下一页"的代码
    * 
    * @param string $style
    * @return string
    */
    function nextPage($curStyle='', $style='')
    {
        if($this->currentPage < $this->totalPage) {
            return $this->_getLink($this->_getUrl($this->currentPage+1), $this->nextPage, '下一页', $style);
        }
        return '<span class="'.$curStyle.'">'.$this->nextPage.'</span>';
    }

    /**
    * 获取显示“上一页”的代码
    *
    * @param string $style
    * @return string
    */
    function prePage($curStyle='', $style='')
    {
        if($this->currentPage > 1) {
            return $this->_getLink($this->_getUrl($this->currentPage - 1), $this->prePage, '上一页', $style);
        }
        return '<span class="'.$curStyle.'">'.$this->prePage.'</span>';
    }

    /**
    * 获取显示“首页”的代码
    *
    * @return string
    */
    function firstPage($curStyle = '', $style = '')
    {
        if($this->currentPage == 1) {
            return '<span class="'.$curStyle.'">'.$this->firstPage.'</span>';
        }
        return $this->_getLink($this->_getUrl(1), $this->firstPage, '第一页', $style);
    }

    /**
    * 获取显示“尾页”的代码
    *
    * @return string
    */
    function lastPage($curStyle='', $style='')
    {
        if($this->currentPage == $this->totalPage)
        {
            return '<span class="'.$curStyle.'">'.$this->lastPage.'</span>';
        }
        return $this->_getLink($this->_getUrl($this->totalPage), $this->lastPage, '最后页', $style);
    }

    /**
     * 获得分页条。
     *
     * @param 当前页码 $curStyle
     * @param 连接CSS $style
     * @return 分页条字符串
     */
    function nowbar($curStyle='', $style='')
    {
        $plus = ceil($this->pagebarNum / 2);
        if($this->pagebarNum - $plus + $this->currentPage > $this->totalPage) {
            $plus = ($this->pagebarNum - $this->totalPage + $this->currentPage);
        }
        $begin  = $this->currentPage - $plus + 1;
        $begin  = ($begin>=1) ? $begin : 1;
        $return = '';
        for($i = $begin; $i < $begin + $this->pagebarNum; $i++) {
            if ($i <= $this->totalPage) {
                if ($i != $this->currentPage) {
                    $return .= $this->_getText($this->_getLink($this->_getUrl($i), $i, $style));
                } else {
                    $return .= $this->_getText('<span class="'.$curStyle.'">'.$i.'</span>');
                }
            } else {
                break;
            }
            $return .= "\n";
        }
        unset($begin);
        return $return;
    }
    /**
    * 获取显示跳转按钮的代码
    *
    * @return string
    */
    function select()
    {
        $url    = $this->_getUrl("' + this.value");
        $return = "<select name=\"PB_Page_Select\" onchange=\"window.location.href='$url\">";
        for($i=1; $i <= $this->totalPage; $i++) {
            if ($i==$this->currentPage) {
                $return.='<option value="'.$i.'" selected>'.$i.'</option>';
            } else {
                $return.='<option value="'.$i.'">'.$i.'</option>';
            }
        }
        unset($i);
        $return .= '</select>';
        return $return;
    }

    /**
    * 获取mysql 语句中limit需要的值
    *
    * @return string
    */
    function offset()
    {
        return $this->offset;
    }

    /**
    * 控制分页显示风格（你可以增加相应的风格）
    * 
    * @param int $mode
    * @return string
    */
    function show($mode=1)
    {
        switch ($mode)
        {
            case '1':
                $this->nextPage = '下一页';
                $this->prePage  = '上一页';
                return $this->prePage()."<span class=\"current\">{$this->currentPage}</span>".$this->nextPage();
                break;
                
            case '2':
                $this->nextPage  = '下一页>>';
                $this->prePage   = '<<上一页';
                $this->firstPage = '首页';
                $this->lastPage  = '尾页';
                return $this->firstPage().$this->prePage().'<span class="current">[第'.$this->currentPage.'页]</span>'.$this->nextPage().$this->lastPage().'第'.$this->select().'页';
                break;
                
            case '3':
                $this->nextPage  = '下一页';
                $this->prePage   = '上一页';
                $this->firstPage = '首页';
                $this->lastPage  = '尾页';
                $pageStr = $this->firstPage()." ".$this->prePage();
                $pageStr .= ' '.$this->nowbar('current');
                $pageStr .= ' '.$this->nextPage()." ".$this->lastPage();
                $pageStr .= "<span>当前页{$this->currentPage}/{$this->totalPage}</span> <span>共{$this->totalSize}条</span>";
                return $pageStr;
                break;
                
            case '4':
                $this->nextPage  = '下一页';
                $this->prePage   = '上一页';
                $this->firstPage = '首页';
                $this->lastPage  = '尾页';
                $pageStr = $this->firstPage()." ".$this->prePage();
                $pageStr .= ' '.$this->nowbar('current');
                $pageStr .= ' '.$this->nextPage()." ".$this->lastPage();
                return $pageStr;
                break;
        }

    }
    
    
    
    /*----------------private function (私有方法)-----------------------------------------------------------*/
    /**
    * 设置url头地址
    * @param: String $url
    * @return boolean
    */
    function SetUrl($url="")
    {
        if($this->isFstatic) {
            /**
             * @todo isFstatic
             */
        } else {
            if(!empty($url)) {
                //手动设置
                $this->url=$url.((stristr($url,'?'))?'&':'?').$this->pageName."=";
            } else {
                $parse = parse_url($_SERVER['REQUEST_URI']);
                $query = array();
                if (!empty($parse['query'])) {
                    parse_str($parse['query'], $query);
                    if (!empty($query) && isset($query[$this->pageName])) {
                        unset($query[$this->pageName]);
                    }
                }

                $array = explode('?', $_SERVER['REQUEST_URI']);
                if (!empty($query)) {
                    $this->url = $array[0].'?'.http_build_query($query)."&{$this->pageName}=";
                } else {
                    $this->url = $array[0]."?{$this->pageName}=";
                }
            }
        }
    }

   /**
   * 设置当前页面
   */
    function SetCurrentPage($currentPage)
    {
        if(empty($currentPage)) {
            //系统获取
            if(isset($_GET[$this->pageName])) {
                $this->currentPage = intval($_GET[$this->pageName]);
            }
        } else {
            //手动设置
            $this->currentPage = intval($currentPage);
        }
    }

    /**
   * 为指定的页面返回地址值
   *
   * @param int $pageNo
   * @return string $url
   */
    function _getUrl($pageNo=1)
    {
        if($this->isFstatic) {
            return $this->url.$pageNo.'.html';
        } else {
            return $this->url.$pageNo;
        }
    }

   /**
   * 获取分页显示文字，比如说默认情况下_getText('<a href="">1</a>')将返回[<a href="">1</a>]
   *
   * @param String $str
   * @return string $url
   */ 
    function _getText($str)
    {
        return $this->formatLeft.$str.$this->formatRight;
    }

    //获取链接地址
    function _getLink($url, $text, $title='', $style='')
    {
        $style = (empty($style)) ? '' : 'class="'.$style.'"';
        if($this->isAjax) {
            //如果是使用AJAX模式
            return "<a $style href='#' onclick=\"{$this->ajaxActionName}('$url');\">$text</a>";
        } else {
            return "<a $style href='$url' title='$title'>$text</a>";
        }
    }

    //出错处理方式
    function error($function,$errormsg)
    {
        die('Error in file <b>'.__FILE__.'</b> ,Function <b>'.$function.'()</b> :'.$errormsg);
    }
}