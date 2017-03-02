<?php
if (!defined('PhpMe')) {
	exit('Include Permission Denied!');
}

class Controller_Demo extends Controller_Base
{
    /**
     * 展示路由SEO功能.
     */
    public function routerPatch()
    {
        echo 'http://xxx.xxx.xxx/index.php?__c=user&__m=list&type=1&page=2';
    }
    
    /**
     * 展示请求自定义控制器方法处理.
     */
    public function specifiedRouter()
    {
        echo 'it is my job';
    }
    
    /**
     * 模板展示.
     */
    public function template()
    {
        $this->assigns(array(
            'title'   => 'Demo Test Title',
            'content' => 'Demo Content',
            'hello'   => 'Hello World',
        ));
        $this->display('index');
    }
}