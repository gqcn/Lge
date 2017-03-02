<?php
/**
 * 路由规则配置文件，请注意优先级：配置项在前面的优先于后面的进入匹配判断。
 *
 * @author John<john@johnx.cn>
 */
return array(
    /**
     * URI解析规则，用于将前端URI转换为内部可识别的GET变量(主要用于SEO或者请求转发到特定的控制器中)。
     */
    'uri' => array(
        '/\/api\/list\/(\d+)[\/\?]*/'        => '__c=api&__a=list&appid=$1',
        '/\/api\/(\d+)[\/\?]*/'              => '__c=api&appid=$1',
    ),
);