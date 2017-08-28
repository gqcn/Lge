<?php
/**
 * SSH操作类
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * SSH操作类
 */
class Lib_Network_Ssh
{
    public $host;
    public $user;
    public $pass;
    public $port;
    public $conn;

    public $stream        = null;  // 非交互式命令操作对象(阻塞执行)
    public $shellStream   = null;  // 交互式命令操作对象(非阻塞执行)
    public $streamTimeout = 86400; // 命令执行的超时时间
    public $lastLog       = null;  // 最后一次执行命令的结果
    public $echoLog       = true;  // 是否在终端标准输出输出日志

    /**
     * Lib_Network_Ssh constructor.
     *
     * @param string $host 主机地址(IP或域名)
     * @param string $port 端口
     * @param string $user 账号
     * @param string $pass 密码
     */
    public function __construct ($host, $port, $user, $pass)
    {
        if (!function_exists('ssh2_connect')) {
            exception("ssh2 extension not installed!");
        }

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->port = $port;
    }

    /**
     * 初始化shell连接.
     *
     * @return void
     */
    private function _init()
    {
        if (empty($this->conn)) {
            $this->conn = $this->_getSsh2Connection();
            if (empty($this->conn)) {
                $this->log("shell: unable to connect to {$this->host}:{$this->port}");
            }
        }
    }

    /**
     * 创建SSH2链接
     *
     * @return null|resource
     */
    private function _getSsh2Connection()
    {
        if ($conn = ssh2_connect($this->host, $this->port)) {
            $this->log("authenticating to {$this->host}:{$this->port}");
            if (!ssh2_auth_password($conn, $this->user, $this->pass)) {
                $this->log("unable to authenticate to {$this->host}:{$this->port}");
                return null;
            }
        } else {
            $this->log("unable to connect to {$this->host}:{$this->port}");
            return null;
        }
        return $conn;
    }

    /**
     * 上传本地文件到远程服务器地址.
     * 注意文件大小：由于采用的是 file_get_contents 读取文件内容，因此会很占内存，不能传输大文件。
     * @todo 优化读写效率，采用分块形式读写
     *
     * @param string  $localFile  本地文件路径.
     * @param string  $remoteFile 远程文件路径.
     * @param integer $permision  远程文件创建权限.
     *
     * @return boolean
     */
    public function sendFile($localFile, $remoteFile, $permision = 0644)
    {
        $this->_init();

        if (!is_file($localFile)) {
            $this->log("local file {$localFile} does not exist");
            return false;
        }
        $this->log("sending file {$localFile} to {$remoteFile}");

        $result = @ssh2_scp_send($this->conn, $localFile, $remoteFile, $permision);
        if (empty($result)) {
            $sftp       = ssh2_sftp($this->conn);
            $sftpStream = @fopen('ssh2.sftp://'.$sftp . $remoteFile, 'w');
            $dataToSend = @file_get_contents($localFile);
            $result     = false;
            if (!empty($dataToSend)) {
                $result = (@fwrite($sftpStream, $dataToSend) !== false);
            }
            @fclose($sftpStream);
            return $result;
        } else {
            return true;
        }
    }

    /**
     * 下载远程文件到本地.
     *
     * @param string $remoteFile 远程文件路径.
     * @param string $localFile  本地文件路径.
     *
     * @return boolean
     */
    public function getFile($remoteFile, $localFile)
    {
        $this->_init();
        $this->log("receiving file {$remoteFile} to {$localFile}");

        $result = ssh2_scp_recv($this->conn, $remoteFile, $localFile);
        var_dump($result);
        if (empty($result)) {
            $sftp       = ssh2_sftp($this->conn);
            $sftpStream = fopen('ssh2.sftp://'.$sftp . $remoteFile, 'r');
            if (!empty($sftpStream)) {
                $contents = stream_get_contents($sftpStream);
                file_put_contents($localFile, $contents);
                fclose($sftpStream);
            }
            var_dump(file_exists($localFile));
            // 如果以上两种方式都失败了，那么尝试使用scp的方式来下载文件
            if (!file_exists($localFile)) {
                $this->disconnect();
                $shellCmd = "sshpass -p {$this->pass} scp -P {$this->port} {$this->user}@{$this->host}:{$remoteFile} {$localFile}";
                var_dump($shellCmd);
                shell_exec($shellCmd);
            }
        }
        return file_exists($localFile);
    }

    /**
     * 远程阻塞执行一条SHELL命令(非交互式).
     *
     * @param string  $cmd     命令.
     * @param integer $timeout 命令执行超时时间.
     *
     * @return string
     */
    public function syncExec($cmd, $timeout = 0)
    {
        $this->_init();
        if (empty($timeout)) {
            $timeout = $this->streamTimeout;
        }
        $this->log("Exec: ".$cmd);
        $stream = ssh2_exec($this->conn, $cmd);
        if (false === $stream ) {
            $this->log("unable to execute command:{$cmd}");
        }
        stream_set_blocking($stream, 1);
        stream_set_timeout($stream,  $timeout);
        $this->lastLog = stream_get_contents($stream);
        fclose($stream);
        return $this->lastLog;
    }

    /**
     * 远程阻塞执行一条SHELL命令(交互式).
     *
     * @param string  $cmd                   命令.
     * @param integer $timeout               命令执行超时时间.
     * @param boolean $showInteractiveString 是否显示交互式显示内容，为false的话，只会输出执行结果，不过显示交互内容.
     *
     * @return string
     */
    public function syncShell($cmd, $timeout = 600, $showInteractiveString = false)
    {
        $this->_init();

        $shellStream = $this->_getShellStream();
        if (!empty($shellStream)) {
            // 首先获取对象输出的缓冲区内容，例如提示符界面：[john@iZwz9f6h0o28aja4p79wztZ ~]$
            while (true) {
                usleep(100000);
                $line = fgets($this->shellStream);
                if (empty($line)) {
                    break;
                } else {
                    if ($showInteractiveString) {
                        $this->log($line);
                    }
                }
            }
            // 其次执行用户指令
            $this->log("Shell: ".$cmd);
            $shellCmd = "{$cmd} && echo '##end##';".PHP_EOL;
            fwrite($shellStream, $shellCmd);
            // 命令超时时间
            $this->lastLog     = '';
            $cmdTimeoutEndTime = time() + $timeout;
            while (true) {
                $line = fgets($shellStream);
                if (empty($line)) {
                    usleep(100000);
                } else {
                    if (trim($line) == '##end##') {
                        break;
                    }
                }
                $this->lastLog .= $line;
                // 命令超时时间
                if (time() > $cmdTimeoutEndTime) {
                    $this->log("shell command timeout");
                    break;
                }
            }
            if (!$showInteractiveString && !empty($this->lastLog)) {
                $this->lastLog = substr($this->lastLog, strlen($shellCmd) + 1);
            }
        }
        return $this->lastLog;
    }

    /**
     * 获得一个交互命令的操作对象.
     *
     * @return null|resource
     */
    private function _getShellStream()
    {
        $this->_init();

        if (empty($this->shellStream)) {
            $this->shellStream = ssh2_shell($this->conn);
            if (!empty($this->shellStream)) {
                stream_set_timeout($this->shellStream, $this->streamTimeout);
            }
        }
        return $this->shellStream;
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
     * @return void
     */
    public function asyncShell($cmds = array())
    {
        $this->_init();

        $this->log("openning ssh2 shell..");
        // 打开一条数据流用于执行当前命令
        $this->shellStream = ssh2_shell($this->conn);
        // 命令执行后等待一段时间再获取返回数据
        sleep(2);
        while ($line = fgets($this->shellStream)) {
            $this->log($line);
        }
        // 数据类型兼容(数组参数一般用于交互式命令)
        if (!is_array($cmds)) {
            $cmds = array($cmds);
        }
        foreach ($cmds as $index => $item) {
            if (is_array($item)) {
                $cmd             = isset($item[0]) ? $item[0] : '';
                $interactive     = isset($item[1]) ? $item[1] : false;
                $intervalTimeout = isset($item[2]) ? $item[2] : 30;
            } else {
                $cmd             = $item;
                $interactive     = false;
                $intervalTimeout = $this->streamTimeout;
            }
            $cmd = trim($cmd, ';');
            if ($interactive) {
                // 作为交互式输入数据，不能对命令做修改
                $shellCmd = $cmd.PHP_EOL;
                fwrite($this->shellStream, $shellCmd);
            } else {
                $shellCmd  = "{$cmd} && echo '##end##';".PHP_EOL;
                fwrite($this->shellStream, $shellCmd);
            }
            // 命令超时时间
            $cmdTimeoutEndTime  = time() + $intervalTimeout;
            while (true) {
                $line = fgets($this->shellStream);
                if (empty($line)) {
                    usleep(100000);
                } else {
                    $this->log($line);
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
     * 判断远程文件是否存在.
     *
     * @param string $path 远程文件绝对路径.
     *
     * @return boolean
     */
    public function fileExists($path)
    {
        $this->_init();

        $output = $this->syncCmd("[ -f {$path} ] && echo 1; || echo 0;");
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
            @ssh2_disconnect($this->conn);
        } else {
            @fclose($this->conn);
            $this->conn = null;
        }
    }

    /**
     * 判断服务端的指定命令是否存在，例如：php,sshpass
     *
     * @param string $cmd 命令
     *
     * @return boolean
     */
    public function checkCmd($cmd)
    {
        $result = $this->getCmdPath($cmd);
        return !empty($result);
    }

    /**
     * 判断服务端的指定命令的可执行文件绝对路径，例如：php,sshpass
     *
     * @param string $cmd 命令
     *
     * @return string
     */
    public function getCmdPath($cmd)
    {
        $result = $this->syncShell("which {$cmd}");
        $result = trim($result);
        return $result;
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
        if ($this->echoLog) {
            echo 'Lge_SSH2: '.trim($content)."\n";
        }
    }

}
