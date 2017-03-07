<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}
/**
 * 云服务 - 接口测试
 */
class Controller_ApiTest extends BaseControllerAdminAuth
{

    /**
     * 接口测试.
     */
    public function index()
    {
        $this->assigns(array(
            'list'     => Model_ApiApp::instance()->getMyApps(),
            'mainTpl' => 'api/test/index',
        ));
        $this->display();
    }

    /**
     * 异步执行接口测试请求
     */
    public function ajaxRequest()
    {
        $data = Lib_Request::getPostArray(array(), true);
        if (!empty($this->_session['user'])) {
            $data['uid'] = $this->_session['user']['uid'];
        }
        $params = $this->_parseRequestParams($data['request_params']);
        $http   = new Lib_Network_Http();
        $result = $http->send($data['address'], $params, $data['request_type']);
        if (!empty($data['uid'])) {
            $data['request_params']   = json_encode($params);
            $data['response_content'] = $result;
            Instance::table('api_test')->save($data);
        }
        Lib_Response::json(true, $result);
    }

    /**
     *
     * 将页面提交的请求参数转换为服务端所需使用的请求参数格式.
     *
     * @param array $requestParams 页面提交的请求参数.
     *
     * @return array
     */
    private function _parseRequestParams(array $requestParams)
    {
        $params = array();
        $requestParamNames    = $requestParams['name'];
        $requestParamContents = $requestParams['content'];
        foreach ($requestParamNames as $k => $v) {
            $v = trim($v);
            if (empty($v)) {
                continue;
            }
            $params[$v] = $requestParamContents[$k];
        }
        return $params;
    }
}