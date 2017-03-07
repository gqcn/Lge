<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}

/**
 * 触发式文件同步 - 服务端.
 */
class Controller_InotifySyncServer extends Controller_Base
{
    public $startSession = false;
    public $sessionID    = null;

    /**
     * 所有的客户端列表.
     * @var array
     */
    public $clients = array();

    /**
     * 需要监控的目录数组(监控是递归监控，因此监控一个目录将会同时监控其下的所有递归的子级目录)
     * 注意：配置中的监控目录，不能带有子父级关系.
     *
     * @var array
     */
    public $inotifyDirArray = array(
        'temp' => '/home/john/temp/temp/'
    );

    /**
     * 监听事件
     * @var int
     */
    public $inotifyEventQueue = array();



    public function index()
    {
        $currentIp = Lib_IpHandler::getServerIp();
        $server    = new swoole_server($currentIp, 1007, SWOOLE_PROCESS);
        $server->set(array(
            'worker_num' => 1,
            'daemonize'  => false,
        ));
        $server->on('WorkerStart', array($this, 'onWorkerStart'));
        $server->on('receive',     array($this, 'onReceive'));
        $server->start();
    }

    /**
     * 服务进程开启时的回调方法.
     *
     * @param swoole_server $server   Swoole服务端对象.
     * @param integer       $workerId 服务进程序列ID.
     *
     * @return void
     */
    public function onWorkerStart($server, $workerId)
    {
        if ($workerId == 0) {
            swoole_timer_tick(1000, function () {
                $this->_handleQueue();
            });
        }
    }

    /**
     *
     * 数据接收回调方法.
     *
     * @param swoole_server $server Swoole服务端对象.
     * @param integer       $fd     文件描述符.
     * @param integer       $fromId 来源的资源ID号.
     * @param mixed         $data   数据.
     *
     * @return void
     */
    public function onReceive($server, $fd, $fromId, $data)
    {
        echo "received:{$data}\n";
        $data = json_decode($data, 'true');
        switch ($data['cmd']) {
            // 自定义客户端心跳
            case 'client_heartbeat':
                $this->clients[$data['data']['ip']] = time();
                break;

            // 文件触发事件
            case 'add_events':
                if (!empty($data['data'])) {
                    foreach ($data['data'] as $v) {
                        $this->inotifyEventQueue[] = $v;
                    }
                }
                break;

            // 文件传输
            case 'async_file_stream':


                break;
        }
    }

    /**
     * 优化事件队列.
     *
     * @param array $events 事件数组.
     *
     * @return array
     */
    private function _filterEvents(array $events)
    {
        /**
         * 1. 合并同一服务器同一文件的同一事件；
         * 2. 同一服务器同一文件如果存在多个事件时以其最后一个事件为主；
         */
        $prevEvent      = array();
        $eventsMap      = array();
        $filteredEvents = array();
        foreach ($events as $k => $event) {
            if (empty($prevEvent)) {
                $prevEvent = $event;
            } else {
                // 连续的事件将合并成单一事件(比如复制一个大文件时，会产生一大堆IN_MODIFY事件)
                if ($prevEvent['ip'] == $event['ip']
                    && $prevEvent['path'] == $event['path']
                    && $prevEvent['mask'] == $event['mask']) {
                    continue;
                } else {
                    $prevEvent = $event;
                }
            }
            $filteredEvents[] = $event;
        }
        return $filteredEvents;
    }

    /**
     * 递归循环处理事件.
     *
     * @return void
     */
    private function _handleQueue()
    {
        if (empty($this->inotifyEventQueue)) {
            return;
        }

        $filteredEvents = $this->_filterEvents($this->inotifyEventQueue);
        print_r($filteredEvents);
        Logger::log(count($filteredEvents),      'test');
        Logger::log(json_encode($this->clients), 'test');
        $this->inotifyEventQueue = array();
    }

}
