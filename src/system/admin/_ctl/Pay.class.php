<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}

/**
 * 支付管理
 */
class Controller_Pay extends BaseControllerAdminAuth
{
    /**
     * 支付类型
     * @var array
     */
    public $channelMap = array(
        'wechat'  => '微信支付',
    );

    /**
     * 支付用途
     * @var array
     */
    public $payTypes   = array(
        'redpack'  => '微信红包',
    );


    /**
     * 支付查询.
     */
    public function index()
    {
        $data = Lib_Request::getArray(array(
            'limit'     => 10,
            'channel'   => '',
            'pay_type'  => '',
            'date_from' => '',
            'date_to'   => '',
        ));
        $condition = array();
        if (!empty($data['channel'])) {
            $condition[] = array('p.channel =?', $data['channel']);
        }
        if (!empty($data['pay_type'])) {
            $condition[] = array('p.pay_type =?', $data['pay_type']);
        }
        if (!empty($data['date_from'])) {
            $condition[] = array('p.create_time >=?', strtotime($data['date_from'].' 00:00:00'));
        }
        if (!empty($data['date_to'])) {
            $condition[] = array('p.create_time <=?', strtotime($data['date_to'].' 23:59:59'));
        }
        $limit  = $data['limit'] > 100 ? 100 : $data['limit'];
        $limit  = empty($limit) ? 10 : $limit;
        $start  = $this->getStart($limit);
        $tables = array(
            'pay p',
            'left join user u on(u.uid=p.uid)',
        );
        $fields = 'p.*,u.avatar,u.nickname';
        $list   = Instance::table($tables)->getAll($fields, $condition, null, 'id DESC', $start, $limit);
        $count  = Instance::table($tables)->getCount($condition);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $v['amount_format']        = number_format($v['amount']/100, 2);
                $v['channel_name']  = $this->channelMap[$v['channel']];
                $v['pay_type_name'] = $this->payTypes[$v['pay_type']];
                $list[$k] = $v;
            }

        }

        $this->assigns(array(
            'list'      => $list,
            'channels'  => $this->channelMap,
            'payTypes'  => $this->payTypes,
            'listIndex' => $count - $start,
            'page'      => $this->getPage($count, $limit),
            'mainTpl'   => 'pay/index'
        ));
        $this->display();
    }
}
