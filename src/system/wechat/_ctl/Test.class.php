<?php
if (!defined('PhpMe')) {
    exit('Include Permission Denied!');
}

class Controller_Test extends BaseController
{

    public function t()
    {
        $filePath = '/home/hbfj/www/phpme_wechat/src/upload/228620795000849798.jpg';
        $r = Module_WeChat_Api::instance()->materialAdd('thumb', $filePath);
        var_dump($r);
        exit();
        $db  = Instance::database();
        $sql = file_get_contents(ROOT_PATH.'../doc/InitSQL/init.sql');
        $db->query($sql, array(), 'master');
    }
}