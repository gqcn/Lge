<?php
/**
 * 百度ApiStore API封装.
 */
class Module_Baidu_ApiStore_Api extends BaseModule
{

    public $apikey = '3c7be1603d8a420b97e4753f5b72ecf0';

    /**
     * 获得实例.
     *
     * @return Module_Baidu_ApiStore_Api
     */
    public static function instance()
    {
        return self::instanceInternal(__CLASS__);
    }

    /**
     * 身份证查询(http://apistore.baidu.com/apiworks/servicedetail/113.html).
     *
     * @param string $id 身份证号.
     *
     * @return array
     */
    public function checkIdCard($id)
    {
        $http   = new Lib_Network_Http();
        $url    = "http://apis.baidu.com/apistore/idservice/id?id={$id}";
        $http->setHeaders(array("apikey:{$this->apikey}"));
        $result = $http->get($url);
        $this->log("{$url}\t{$result}", 'ApiStore/checkIdCard');
        return json_decode($result, true);
    }

}
