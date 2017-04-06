<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * SSH操作类
 *
 */
class Lib_Network_Ssh
{
    private $host;
    private $user;
    private $pass;
    private $port;
    private $conn;
    private $stream;
    private $streamTimeout = 86400;
    private $lastLog;

    public function __construct ($host, $port, $user, $pass) {
        if (!function_exists('ssh2_connect')) {
            $this->log("error:ssh2 extension not installed!\n");
            exit();
        }

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->port = $port;
    }

    /**
     * 初始化连接.
     *
     * @throws Exception
     */
    private function _init()
    {
        if (empty($this->conn)) {
            $this->log("connecting to {$this->host}:{$this->port}");
            if ($this->conn = ssh2_connect($this->host, $this->port)) {
                $this->log("authenticating to {$this->host}:{$this->port}");
                if (!ssh2_auth_password($this->conn, $this->user, $this->pass)) {
                    throw new \Exception("unable to authenticate to {$this->host}:{$this->port}");
                }
            } else {
                throw new \Exception("unable to connect to {$this->host}:{$this->port}");
            }
        }
    }

    /**
     * 上传本地文件到远程服务器地址.
     * 注意文件大小：由于采用的是 file_get_contents 读取文件内容，因此会很占内存。
     * @todo 优化读写效率，采用分块形式读写
     *
     * @param string  $localFile  本地文件路径.
     * @param string  $remoteFile 远程文件路径.
     * @param integer $permision  远程文件创建权限.
     *
     * @return bool
     * @throws \Exception
     */
    public function sendFile($localFile, $remoteFile, $permision = 0644)
    {
        $this->_init();

        if (!is_file($localFile)) {
            throw new \Exception("local file {$localFile} does not exist");
        }
        $this->log("sending file {$localFile} to {$remoteFile}");

        $sftp       = ssh2_sftp($this->conn);
        $sftpStream = @fopen('ssh2.sftp://'.$sftp . $remoteFile, 'w');
        if(empty($sftpStream)) {
            if (!@ssh2_scp_send($this->conn, $localFile, $remoteFile, $permision)) {
                throw new \Exception("could not open remote file: {$remoteFile}");
            } else {
                return true;
            }
        }

        $dataToSend = @file_get_contents($localFile);
        if (@fwrite($sftpStream, $dataToSend) === false) {
            throw new \Exception("could not send data from file: $localFile.");
        }
        @fclose($sftpStream);
        $this->log("sending file {$localFile} as {$remoteFile} succeeded");
        return true;
    }

    /**
     * 下载远程文件到本地.
     *
     * @param string $remoteFile 远程文件路径.
     * @param string $localFile  本地文件路径.
     *
     * @return bool
     */
    public function getFile($remoteFile, $localFile)
    {
        $this->_init();

        $this->log("receiving file {$remoteFile} top {$localFile}");
        return @ssh2_scp_recv($this->conn, $remoteFile, $localFile);
    }

    /**
     * 远程阻塞执行一条SHELL命令.
     *
     * @todo 功能不完善
     *
     * @param string $cmd 命令.
     *
     * @return string
     * @throws \Exception
     */
    public function cmd($cmd)
    {
        $this->_init();

        $this->log($cmd);
        $this->stream = ssh2_exec($this->conn, $cmd);
        if (false === $this->stream ) {
            throw new \Exception("unable to execute command:{$cmd}");
        }
        stream_set_blocking($this->stream, 1);
        stream_set_timeout($this->stream,  $this->streamTimeout);
        $this->lastLog = stream_get_contents($this->stream);
        fclose($this->stream);
        return $this->lastLog;
    }

    /**
     * 创建新的SHELL并非阻塞执行命令(注意执行指令返回后该命令会自动返回，并且如果SSH没有操作那么SSH通道可能会关闭，请注意守护进程命令的使用)。
     *
     * 参数如果是数组，可以带有3个参数:
     * 第一个是需要执行的shell命令，
     * 第二个表示是否是交互式命令(true|false)，
     * 第三个表示超时时间(秒，比如有的交互式命令，忘了写退出命令，那么该进程将一直执行，为了防止类似情况的发生,不管有没有输出，只能等待这么长时间)；
     *
     * @param mixed $cmds 命令列表.
     *
     * @throws \Exception
     */
    public function shellCmd($cmds = array())
    {
        $this->_init();

        $this->log("openning ssh2 shell..");
        // 打开一条数据流用于执行当前命令
        $this->shellStream = ssh2_shell($this->conn);
        // 命令执行后等待一段时间再获取返回数据
        sleep(2);
        while($line = fgets($this->shellStream)) {
            $this->log($line);
        }
        // 数据类型兼容(数组参数一般用于交互式命令)
        if (!is_array($cmds)) {
            $cmds = array($cmds);
        }
        foreach($cmds as $index => $item) {
            /**
             * $isInteractive 表示是否交互命令，以便执行输入
             * 注意$cmdInterval的默认值是null,判断的时候使用isset
             */
            $cmdInterval        = null;
            $interactive        = false;
            if (is_array($item)) {
                $cmd             = isset($item[0]) ? $item[0] : '';
                $interactive     = isset($item[1]) ? $item[1] : false;
                $intervalTimeout = isset($item[2]) ? $item[2] : 30;
            } else {
                $cmd         = $item;
                $interactive = false;
            }
            $cmd = trim($cmd, ';');
            if ($interactive) {
                // 作为交互式输入数据，不能对命令做修改
                $this->log('case 1:'.$cmd);
                $shellCmd = $cmd.PHP_EOL;
                fwrite($this->shellStream, $shellCmd);
            } else {
                $this->log('case 2:'.$cmd);
                $shellCmd  = "{$cmd};echo '##end##';".PHP_EOL;
                fwrite($this->shellStream, $shellCmd);
            }
            // 命令超时时间
            $cmdTimeoutEndTime  = time() + $intervalTimeout;
            while(true) {
                // 100毫秒
                usleep(100000);
                $line = fgets($this->shellStream);
                if (!empty($line)) {
                    $this->log($line);
                    // 判断命令是否结束(如果是交互式命令，这里不起作用)
                    if (trim($line) == '##end##') {
                        break;
                    }
                }
                // 命令超时时间
                if (time() > $cmdTimeoutEndTime) {
                    $this->log("shell command timeout");
                    break;
                }
            }
        }
        $this->log("closing shell stream");
        fclose($this->shellStream);
    }

    /**
     * 最后一条日志信息.
     *
     * @return mixed
     */
    public function getLastLog()
    {
        return $this->lastLog;
    }

    /**
     * 判断远程文件是否存在.
     *
     * @param  string $path 远程文件绝对路径.
     *
     * @return bool
     * @throws \Exception
     */
    public function fileExists($path)
    {
        $this->_init();

        $output = $this->cmd("[ -f {$path} ] && echo 1; || echo 0;");
        return (bool)trim($output);
    }

    /**
     * 关闭SSH连接.
     *
     * @return void
     */
    public function disconnect()
    {
        $this->_init();

        if (function_exists('ssh2_disconnect')) {
            // if disconnect function is available call it..
            ssh2_disconnect($this->conn);
        } else {
            // if no disconnect func is available, close conn, unset var
            @fclose($this->conn);
            $this->conn = null;
        }
    }

    /**
     * 打印日志.
     *
     * @param string $content 日志内容.
     *
     * @return void
     */
    public function log($content)
    {
        echo trim($content)."\n";
    }
}
