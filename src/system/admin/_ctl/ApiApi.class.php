<?php
namespace Lge;

if(!defined('LGE')){
	exit('Include Permission Denied!');
}
/**
 * 云服务 - 接口管理
 */
class Controller_ApiApi extends BaseControllerAdminAuth
{
    public $bindTableName = 'api_app_api';

    /**
     * 接口管理.
     */
    public function index()
    {
        $appid = Lib_Request::get('appid');
        $appid = intval($appid);
        $app   = Instance::table('api_app')->getOne('*', array('id' => $appid, 'uid' => $this->_session['user']['uid']));
        if (empty($app)) {
            $app = Instance::table('api_app')->getOne('*', array('uid' => $this->_session['user']['uid']), null, "`order` ASC,`id` ASC");
            if (empty($app)) {
                $this->addMessage('您当前没有任何应用信息，请先添加应用后进行操作', 'info');
                Lib_Redirecter::redirectExit('/api-app');
            } else {
                Lib_Redirecter::redirectExit('/api-api?appid='.$app['id']);
            }
        }
        $this->setBreadCrumbs(array(
            array(
                'icon' => 'fa fa-cloud',
                'name' => '服务管理',
                'url'  => '/api-app',
            ),
            array(
                'icon' => '',
                'name' => '应用管理',
                'url'  => '/api-app',
            ),
            array(
                'icon' => '',
                'name' => $app['name'],
                'url'  => '/api-api?appid='.$app['id'],
            ),
            array(
                'icon' => '',
                'name' => '接口管理',
                'url'  => '',
            ),
        ));

        $this->assigns(array(
            'app'      => Instance::table('api_app')->getOne('*', array('id' => $appid)),
            'apps'     => Model_ApiApp::instance()->getMyApps(),
            'catList'  => Model_ApiCategory::instance()->getCatTree($appid),
            'mainTpl' => 'api/api/index',
        ));
        $this->display();
    }

    /**
     * 展示分类的接口列表
     */
    public function apilist()
    {
        $key    = Lib_Request::get('key');
        $appid  = Lib_Request::get('appid');
        $catid  = Lib_Request::get('catid');
        $catid  = intval($catid);
        $cat    = Instance::table('api_app_cat')->getOne('*', array('id' => $catid));
        $tables = array(
            'api_app_api aaa',
            'left join api_app_cat aac on(aac.id=aaa.cat_id)',
        );
        $fields      = 'aaa.*,aac.name as cat_name';
        $condition   = array();
        $condition[] = array("aaa.appid = {$appid}");
        $condition[] = array("aaa.uid = {$this->_session['user']['uid']}");
        if (!empty($catid)) {
            $condition[] = array("and aaa.cat_id={$catid}");
        }
        if (!empty($key)) {
            $condition[] = array("and aaa.name like '%{$key}%'");
        }
        $list      = Instance::table($tables)->getAll($fields, $condition, null, "`order` ASC,`id` ASC");
        foreach ($list as $k => $v) {
            $list[$k]['content'] = json_decode($v['content'], true);
        }
        $this->assigns(array(
            'cat'      => $cat,
            'list'     => $list,
            'catList'  => Model_ApiCategory::instance()->getCatTree($appid),
            'mainTpl' => 'api/api/embed_index',
        ));
        $this->display();
    }

    /**
     * 通过返回示例自动识别返回参数
     */
    public function ajaxCheckRespnseParams()
    {
        $params = array();
        $json   = Lib_Request::getPost('json');
        $xml    = Lib_Request::getPost('xml');
        if (!empty($json)) {
            $jsonArray = json_decode($json, true);
            if (!isset($jsonArray)) {
                Lib_Response::json(false, null, 'JSON数据解析失败，请查看数据格式是否正确');
            } else {
                $params = $this->_contentArrayToParams($jsonArray, $params);
            }
        }
        if (!empty($xml)) {
            $xmlArray = Lib_XmlParser::xml2Array($xml);
            if (empty($xmlArray)) {
                Lib_Response::json(false, null, 'XML数据解析失败，请查看数据格式是否正确');
            } else {
                $params = $this->_contentArrayToParams($xmlArray, $params);
            }
        }
        Lib_Response::json(1, array_values($params));
    }

    /**
     * 将返回示例数组转换为返回参数.
     *
     * @param array $array  返回示例数组.
     * @param array $params 返回参数.
     * @return array
     */
    private function _contentArrayToParams(array $array, array $params = array())
    {
        while (true) {
            foreach ($array as $k => $v) {
                // XML带属性的字段以@符号开头，需要过滤掉
                if (is_string($k) && !isset($params[$k]) && preg_match("/^\w+/", $k)) {
                    $type = gettype($v);
                    if (in_array($type, array('boolean', 'integer', 'double'))) {
                        $type = 'integer';
                    } else if (!in_array($type, array('string', 'array', 'object'))) {
                        $type = 'object';
                    }
                    $params[$k] = array(
                        'name'    => $k,
                        'type'    => $type,
                        'example' => ($type == 'array' || $type == 'object') ? "" : $v,
                        'brief'   => '',
                    );
                    if (is_array($v)) {
                        $array = array_merge($array, $v);
                    }
                }
                unset($array[$k]);
            }
            if (empty($array)) {
                break;
            }
        }
        return $params;
    }

    /**
     * 列表排序.
     *
     * @return void
     */
    public function ajaxSort()
    {
        $ids = Lib_Request::getPost('ids');
        foreach ($ids as $k => $id) {
            Instance::table($this->bindTableName)->update(
                array('order' => $k + 1),
                array(
                    'id'  => $id,
                    'uid' => $this->_session['user']['uid']
                )
            );
        }
        Lib_Response::json(1);
    }

    /**
     * 添加/修改接口.
     */
    public function item()
    {
        $id = Lib_Request::getGet('id');
        if (Lib_Request::isRequestMethodPost()) {
            $this->_handleSave();
        } else {
            $appid  = Lib_Request::get('appid');
            $catid  = Lib_Request::get('catid');
            $isCopy = Lib_Request::get('__copy');
            $data   = array(
                'appid'  => $appid,
                'cat_id' => $catid,
                'order'  => 99,
            );
            if (!empty($id)) {
                $result = Instance::table($this->bindTableName)->getOne("*", array('id' => $id));
                if (!empty($result)) {
                    $data = array_merge($data, $result);
                    $data['content'] = json_decode($data['content'], true);
                }
            }
            $cat     = Instance::table('api_app_cat')->getOne('*', array('id' => $data['cat_id']));
            $catList = Model_ApiCategory::instance()->getCatTree($appid);
            if (empty($catList)) {
                echo "暂无分类信息，请先添加分类后再添加接口。";
                exit();
            }
            // 判断是否复制操作
            if (!empty($isCopy)) {
                unset($data['id']);
                $data['name'] .= ' (复制)';
            }
            $this->assigns(array(
                'cat'      => $cat,
                'data'     => $data,
                'catList'  => $catList,
                'mainTpl'  => 'api/api/embed_item',
            ));
            $this->display();
        }
    }

    private function _handleSave()
    {
        $id     = Lib_Request::getPost('id');
        $data   = Lib_Request::getPostArray(array(
            'uid'         => $this->_session['user']['uid'],
            'update_time' => time(),
        ), true);
        if (empty($id)) {
            $data['create_time'] = time();
        }
        // 内容结构处理
        $requestParams = array();
        foreach ($data['content']['request_params']['name'] as $k => $v) {
            if (empty($v)) {
                continue;
            }
            $requestParams[] = array(
                'name'    => $data['content']['request_params']['name'][$k],
                'type'    => $data['content']['request_params']['type'][$k],
                'status'  => $data['content']['request_params']['status'][$k],
                'example' => $data['content']['request_params']['example'][$k],
                'brief'   => $data['content']['request_params']['brief'][$k],
            );
        }
        $responseParams = array();
        foreach ($data['content']['response_params']['name'] as $k => $v) {
            if (empty($v)) {
                continue;
            }
            $responseParams[] = array(
                'name'    => $data['content']['response_params']['name'][$k],
                'type'    => $data['content']['response_params']['type'][$k],
                'example' => $data['content']['response_params']['example'][$k],
                'brief'   => $data['content']['response_params']['brief'][$k],
            );
        }
        $data['content']['request_params']  = $requestParams;
        $data['content']['response_params'] = $responseParams;
        $data['content']                    = json_encode($data['content']);
        $result = Instance::table($this->bindTableName)->mysqlFiltSave($data);
        if (empty($result)) {
            Lib_Response::json(false, '', '接口信息保存失败');
        } else {
            Lib_Response::json(true, '', '接口信息保存成功');
        }
    }

    /**
     * 异步删除接口.
     */
    public function ajaxDelete()
    {
        $id = Lib_Request::getGet('id', 0);
        if (!empty($id)) {
            Instance::table($this->bindTableName)->delete(array('id' => $id));
        }
        Lib_Response::json(1, '', '接口删除成功');
    }
}