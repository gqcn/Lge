<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}

/**
 * 操作日志管理.
 */
class Controller_OperationLog extends BaseControllerAdminAuth
{
    /**
     * 日志列表.
     */
    public function index()
    {
        $data = Lib_Request::getArray(array(
            'date'  => '',
            'limit' => '10'
        ));
        $condition = "1";
        if(!empty($data['date'])){
            $startTime  = strtotime("{$data['date']} 00:00:00");
            $endTime    = strtotime("{$data['date']} 23:59:59");
            $condition .= " AND l.create_time between {$startTime} and {$endTime}";
        }
        $tables = array(
            'operation_log l',
            'left join user u on(u.uid=l.uid)',
        );
        $fields = 'u.nickname,l.*';
        $limit  = $data['limit'] > 100 ? 100 : $data['limit'];
        $limit  = empty($limit) ? 0 : $limit;
        $start  = $this->getStart($limit);
        $list   = Instance::table($tables)->getAll($fields, $condition, array(),"create_time desc", $start, $limit);
        $count  = Instance::table($tables)->getCount($condition);
        $this->assigns(array(
            'list'    => $list,
            'page'    => $this->getPage($count, $limit),
            'mainTpl' => 'operation-log/index'
        ));
        $this->display();
    }

    /**
     * 查看操作日志详情.
     */
    public function detail()
    {
        $id     = Lib_Request::get('id');
        $tables = array(
            'operation_log l',
            'left join user u on(u.uid=l.uid)',
        );
        $fields = 'u.nickname,l.*';
        $data   = Instance::table($tables)->getOne($fields, array('id' => $id));
        $data['content'] = json_decode($data['content'], true);
        $this->assigns(array(
            'data'    => $data,
            'mainTpl' => 'operation-log/detail'
        ));
        $this->display();
    }

}
