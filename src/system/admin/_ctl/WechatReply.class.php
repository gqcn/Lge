<?php
if(!defined('PhpMe')){
	exit('Include Permission Denied!');
}
/**
 * 微信公众号 - 自动回复管理
 */
class Controller_WechatReply extends BaseControllerAdminAuth
{
    public $types = array(
        'text'  => '文本消息',
        /*
        'image' => '图片消息',
        'video' => '视频消息',
        'voice' => '语音消息',
        'music' => '音乐消息',
        */
        'news'  => '图文消息',
    );

    /**
     * 回复列表.
     */
    public function index()
    {
        $wechat    = Module_WeChat_Api::instance();
        $condition = array('appid' => $wechat->appid, 'unionid' => $wechat->unionid);
        $tables    = 'wechat_reply';
        $fields    = '*';
        $list      = Instance::table($tables)->getAll($fields, $condition, null, "`order` ASC,`id` ASC", 0, 0, 'id');
        foreach ($list as $k => $v) {
            $list[$k]['typeName'] = $this->types[$v['type']];
            switch ($v['type']) {
                case 'news':
                    $content = json_decode($v['content'], true);
                    $content = Module_WeChat_Api::instance()->jsonEncdoe($content);
                    $content = stripslashes($content);
                    break;

                default:
                    $content = strip_tags($v['content']);
                    break;
            }

            $maxLength = 128;
            $encoding  = 'utf-8';
            if (mb_strlen($content, $encoding) > $maxLength) {
                $content = mb_substr($content, 0, $maxLength, $encoding).'...';
            }
            $list[$k]['content'] = $content;
        }
        $this->assigns(array(
            'list'     => $list,
            'mainTpl' => 'wechat/reply/index',
        ));
        $this->display();
    }

    /**
     * 列表排序.
     *
     * @return void
     */
    public function sort()
    {
        $orders = Lib_Request::getPost('orders', array());
        $list   = Instance::table('wechat_reply')->getAll('*', 1, null, "`order` ASC,`id` ASC", 0, 0, 'id');
        $updateList = array();
        foreach ($orders as $id => $order) {
            if (isset($list[$id]) && $list[$id]['order'] != $order) {
                $updateList[$id] = $order;
            }
        }
        foreach ($updateList as $id => $order) {
            Instance::table('wechat_reply')->update(array('order' => $order), array('id' => $id));
        }
        $this->addMessage('列表重新排序完成', 'success');
        Lib_Redirecter::redirectExit();
    }

    /**
     * 添加/修改.
     */
    public function item()
    {
        $id = Lib_Request::getGet('id');
        if (Lib_Request::isRequestMethodPost()) {
            $this->_handleSave();
        } else {
            $this->setCurrentMenu('wechat-reply', 'index');
            $data = array(
                'order' => 99
            );
            if (!empty($id)) {
                $result = Instance::table('wechat_reply')->getOne("*", array('id' => $id));
                if (!empty($result)) {
                    // 不同消息类型处理
                    switch ($result['type']) {
                        case 'text':
                            break;

                        case 'news':
                            $result['content'] = json_decode($result['content'], true);
                            break;
                    }
                    $data = array_merge($data, $result);
                }
            }
            $this->assigns(array(
                'data'     => $data,
                'types'    => $this->types,
                'mainTpl'  => 'wechat/reply/item',
            ));
            $this->display();
        }
    }

    private function _handleSave()
    {
        $wechat = Module_WeChat_Api::instance();
        $type   = Lib_Request::getPost('type');
        $data   = Lib_Request::getPostArray(array(
            'appid'       => $wechat->appid,
            'unionid'     => $wechat->unionid,
            'update_time' => time(),
        ), true);
        if (empty($id)) {
            $data['create_time'] = time();
        }
        // 不同消息类型处理
        switch ($type) {
            case 'text':
                if (empty($data['content'])) {
                    Lib_Response::json(false, '', '回复内容不能为空');
                }
                break;

            case 'news':
                $content    = array();
                $newsTitles = Lib_Request::getPost('news_titles');
                $newsUrls   = Lib_Request::getPost('news_urls');
                $newsImages = Lib_Request::getPost('news_images');
                $newsBriefs = Lib_Request::getPost('news_briefs');
                foreach ($newsTitles as $k => $v) {
                    $title = $newsTitles[$k];
                    $url   = $newsUrls[$k];
                    $image = $newsImages[$k];
                    $brief = $newsBriefs[$k];
                    if (empty($title) || empty($url) || empty($image)) {
                        Lib_Response::json(false, '', '图文消息的【消息标题】、【跳转链接】、【图片地址】不能为空');
                    } else {
                        $content[] = array(
                            'title' => $title,
                            'url'   => $url,
                            'image' => $image,
                            'brief' => $brief,
                        );
                    }
                }
                $data['content'] = json_encode($content);
                break;
        }
        $result = Instance::table('wechat_reply')->mysqlFiltSave($data);
        if (empty($result)) {
            Lib_Response::json(false, '', '操作执行失败');
        } else {
            $this->addMessage('操作执行完成', 'success');
            Lib_Response::json(true, '', '操作执行完成');
        }
    }

    /**
     * 删除回复.
     */
    public function delete()
    {
        $id = Lib_Request::getGet('id', 0);
        if (!empty($id)) {
            Instance::table('wechat_reply')->delete(array('id' => $id));
            $this->addMessage('删除成功', 'success');
        }
        Lib_Redirecter::redirectExit();
    }
}