<?php
if(!defined('PhpMe')){
    exit('Include Permission Denied!');
}

/**
 * 触发式文件同步 - 客户端.
 * 功能：
 * 1. 用于将文件修改事件转发给服务端(通过消息队列)；
 * 2. 接收服务端指令，将文件同步到其他客户端服务器；
 */
class Controller_InotifySyncClient extends Controller_Base
{
    public $startSession = false;   // 是否开启session
    public $sessionID    = null;    // 设置sessionid

    /**
     * inotify的文件操作对象.
     *
     * @var object
     */
    public $fd;

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
     * 已被纳入监控的本地目录绝对路径数组(程序使用).
     *
     * @var array
     */
    public $inotifiedDirArray = array();

    /**
     * 监听事件
     * @var int
     */
    public $inotifyEventQueue = array();

    /**
     * 监听目录数组(array( integer => string )).
     * 
     * @var array
     */
    public $watchDescriptors = array();

    /**
     * 服务端IP.
     *
     * @var string
     */
    public $serverIp   = '192.168.1.11';
    public $serverPort = '1007';

    /**
     * 当前服务器的IP.
     *
     * @var string
     */
    public $clientIp   = '';

    /**
     * @var string
     */
    public $clientPort = '1008';


    public function index()
    {
        $clientIp = Lib_IpHandler::getServerIp();
        $server   = new swoole_server($clientIp, $this->clientPort, SWOOLE_PROCESS);
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
            $this->fd        = inotify_init();
            $this->clientIp  = Lib_IpHandler::getServerIp();
            $this->events    = IN_MODIFY|IN_CREATE|IN_DELETE|IN_MOVE;
            // 目录监控
            foreach ($this->inotifyDirArray as $node => $dirPath) {
                $this->_addWatchDir($dirPath);
            }
            // 加入到swoole的异步处理事件循环中(注意是异步并发处理)
            swoole_event_add($this->fd, function ($fd) {
                // 阻塞执行
                $events = inotify_read($fd);
                if (!empty($events)) {
                    $this->_handleEvents($events);
                }
            });

            // 事件队列处理
            swoole_timer_tick(1000, function () {
                // 判断inotify是否有事件在排队
                $inotifyQueueLength = inotify_queue_len($this->fd);
                if (empty($inotifyQueueLength)) {
                    // 判断本地队列数据是否正在增加，如果没有增加则可以发送到服务端
                    $needHandleQueue   = true;
                    $localQueueLength1 = count($this->inotifyEventQueue);
                    $usleepIntervals   = array(300000, 500000, 1000000);
                    foreach ($usleepIntervals as $interval) {
                        usleep($interval);
                        $localQueueLength2 = count($this->inotifyEventQueue);
                        if ($localQueueLength2 != $localQueueLength1) {
                            $needHandleQueue = false;
                            break;
                        }
                    }
                    if ($needHandleQueue) {
                        $this->_handleQueue();
                    }
                } else {
                    // echo "{$this->clientIp} queue length: {$inotifyQueueLength}".PHP_EOL;
                }
            });

            // 自定义心跳
            $this->_clientHeartBeat();
            swoole_timer_tick(5000, function () {
                $this->_clientHeartBeat();
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
        $data = json_decode($data, 'true');
        switch ($data['cmd']) {
            // 执行文件同步操作
            case 'async_file':


                $server->close($fd);
                break;

            case 'async_file_stream':


                break;
        }
    }

    /**
     * 添加目录监控.
     *
     * @param string $dirPath 目录绝对路径.
     *
     * @return void
     */
    private function _addWatchDir($dirPath)
    {
        if (is_dir($dirPath)) {
            $dirPath                           = rtrim($dirPath, '/').'/';
            $wd                                = inotify_add_watch($this->fd, $dirPath, $this->events);
            $this->watchDescriptors[$wd]       = $dirPath;
            $this->inotifiedDirArray[$dirPath] = true;
            $files = scandir($dirPath);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $filePath = $dirPath.$file;
                if (is_dir($filePath)) {
                    $this->_addWatchDir($filePath);
                }
            }
        }
    }

    /**
     * 去掉目录监控.
     *
     * @param string $dirPath 目录绝对路径.
     *
     * @return void
     */
    private function _removeWatchDir($dirPath)
    {
        if (is_dir($dirPath)) {
            $wd                          = inotify_rm_watch($this->fd, $dirPath, $this->events);
            $this->watchDescriptors[$wd] = $dirPath;
            $files = scandir($dirPath);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $filePath = rtrim($dirPath, '/').'/'.$file;
                if (is_dir($filePath)) {
                    $this->_removeWatchDir($filePath);
                }
            }
        }
    }

    /**
     * 根据文件绝对路径获取节点名称.
     *
     * @param string $filePath 文件绝对路径.
     *
     * @return string
     */
    private function _getNodeByFilePath($filePath)
    {
        $result = '';
        foreach ($this->inotifyDirArray as $node => $dirPath) {
            if (strpos($filePath, $dirPath) === 0) {
                $result = $node;
                break;
            }
        }
        return $result;
    }

    /**
     * 处理文件监控事件.
     *
     * @param array $events 事件数组.
     *
     * @return void
     */
    private function _handleEvents($events)
    {
        if (!empty($events)) {
            foreach ($events as $event) {
                $wd   = $event['wd'];
                $name = $event['name'];
                $path = $this->watchDescriptors[$wd].$name;
                $node = $this->_getNodeByFilePath($path);
                // 修改为相对路径
                if (!empty($node)) {
                    $path = substr($path, strlen($this->inotifyDirArray[$node]) - 1);
                }
                /*
                // 处理目录事件
                if ($mask & IN_ISDIR) {
                    $dirPath = $this->watchDescriptors[$wd].'/'.$name;
                    if ($mask & IN_CREATE) {
                        $this->_addWatchDir($dirPath);
                    } else if ($mask & IN_DELETE) {
                        $this->_removeWatchDir($dirPath);
                    }
                }
                */
                $data = array(
                    'ip'     => $this->clientIp,
                    'path'   => $path,
                    'node'   => $node,
                    'mask'   => $event['mask'],
                    'cookie' => $event['cookie'],
                );
                $this->inotifyEventQueue[] = $data;
            }
        }

    }

    /**
     * 当文件事件为IN_MODIFY时，需要检查一个文件是否已经修改完成，只有完成之后才能发送这条消息到服务端.
     *
     * @param string $filePath 文件绝对路径.
     *
     * @return boolean
     */
    private function _checkFileModified($filePath)
    {
        $filestate1 = stat($filePath);
        $intervals = array(300000, 500000, 1000000, 5000000, 10000000);
        foreach ($intervals as $interval) {
            usleep($interval);
            clearstatcache();
            $filestate2 = stat($filePath);
            if ($filestate2['blocks'] != $filestate1['blocks']
                || $filestate2['mtime'] != $filestate1['mtime']) {
                return false;
            }
        }
        return true;
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
         * 1. 合并同一文件的同一事件(比如复制一个大文件时，会产生一大堆IN_MODIFY事件)；
         * 2. 同一文件如果存在多个事件时以其最后一个事件为主；
         */
        $eventsMap      = array();
        $filteredEvents = array();
        foreach ($events as $k => $event) {
            $event['index']            = $k;
            $eventsMap[$event['path']] = $event;
        }
        /**
         * 将事件按照事件出现的顺序从小到大进行排列
         */
        $usortFunction = function($a, $b) {
            if ($a['index'] == $b['index']) {
                return 0;
            } else {
                return ($a['index'] < $b['index']) ? -1 : 1;
            }
        };
        uasort($eventsMap, $usortFunction);

        // 将结果数据复制到返回数组
        foreach ($eventsMap as $path => $item) {
            unset($item['index']);
            /*
            if ($item['mask'] & IN_MODIFY) {
                $dirPath  = $this->inotifyDirArray[$item['node']];
                $filePath = rtrim($dirPath, '/').$item['path'];
                // 判断有问题，当复制结束后修改事件丢失
                if (!$this->_checkFileModified($filePath)) {
                    continue;
                }
            }
            */
            $filteredEvents[] = $item;
        }

        return $filteredEvents;
    }

    /**
     * 循环处理事件.
     *
     * @return void
     */
    private function _handleQueue()
    {
        if (!empty($this->inotifyEventQueue)) {
            $filteredEvents = $this->_filterEvents($this->inotifyEventQueue);
            if (!empty($filteredEvents)) {
                $this->_send('add_events', $filteredEvents);
            }
            $this->inotifyEventQueue = array();
        }
    }

    /**
     * 自定义心跳.
     *
     * @return void
     */
    private function _clientHeartBeat()
    {
        $this->_send('client_heartbeat', array('ip' => $this->clientIp));
    }

    /**
     * 向服务端发送事件指令.
     *
     * @param string $command 指令.
     * @param mixed  $data    数据.
     * @param string $host    目标主机IP.
     * @param string $port    目标主机端口.
     */
    private function _send($command, $data, $host = null, $port = null)
    {
        $tcpClient = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        if (empty($host)) {
            $host = $this->serverIp;
            $port = $this->serverPort;
        }
        $tcpClient->connect($host, $port, 1);
        if ($tcpClient->isConnected()) {
            $tcpClient->send(json_encode(array(
                'cmd'  => $command,
                'data' => $data,
            )));
        }
        $tcpClient->close();
    }



}
