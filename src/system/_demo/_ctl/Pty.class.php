<?php
namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

class Controller_Pty extends Controller_Base
{

    /**
     * 默认入口函数.
     *
     * @return void
     */
    public function index()
    {/*
        $ip   = '127.0.0.1';
        $port = 8889;
        $sock = fsockopen($ip, $port);
        $descriptorspec = array(
                0 => $sock,
                1 => $sock,
                2 => $sock
        );*/
        $descriptorspec = array(
            0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
            1 => array("pipe", "w"),  // 标准输出，子进程向此管道中写入数据
            2 => array("file", "/tmp/error-output.txt", "a") // 标准错误，写入到一个文件
        );
        $process = proc_open('/bin/sh', $descriptorspec, $pipes);
        if (is_resource($process)) {
            sleep(2);
            fwrite($pipes[0], 'vi test.php');
            fclose($pipes[0]);
            sleep(2);
            echo stream_get_contents($pipes[1]);
            fclose($pipes[1]);


            // 切记：在调用 proc_close 之前关闭所有的管道以避免死锁。
            $return_value = proc_close($process);
        }
        exit();
/*
        exit();
        $descriptorspec = array(
            0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
            1 => array("pipe", "w"),  // 标准输出，子进程向此管道中写入数据
            2 => array("file", "/tmp/error-output.txt", "a") // 标准错误，写入到一个文件
        );

        $descriptorspec = array(
            0 => array("pipe", "r"),  // 标准输入，子进程从此管道中读取数据
            1 => array("pipe", "w"),  // 标准输出，子进程向此管道中写入数据
            // 2 => array("pipe", "w")   // 标准错误，写入到一个文件
            2 => array("file", "/tmp/error-output.txt", "a") // 标准错误，写入到一个文件
        );

        $descriptorspec = array(
            0 => array('file', '/dev/pty', 'r'),
            1 => array('file', '/dev/pty', 'w'),
            // 2 => array('file', '/dev/tty', 'w')
            2 => array("file", "/tmp/error-output.txt", "a") // 标准错误，写入到一个文件
        );

        $cwd = '/tmp';
        $env = null;//$this->_getEnv();

        $process = proc_open('/bin/bash', $descriptorspec, $pipes, $cwd, $env);
*/
        if (is_resource($process)) {
            // $pipes 现在看起来是这样的：
            // 0 => 可以向子进程标准输入写入的句柄
            // 1 => 可以从子进程标准输出读取的句柄
            // 错误输出将被追加到文件 /tmp/error-output.txt
            sleep(2);
            fwrite($pipes[0], 'top');
            fclose($pipes[0]);
sleep(2);
            echo stream_get_contents($pipes[1]);
            fclose($pipes[1]);


            // 切记：在调用 proc_close 之前关闭所有的管道以避免死锁。
            $return_value = proc_close($process);

            // echo "command returned $return_value\n";
        }
    }

    private function _getEnv()
    {
        $env     = array();
        $content = shell_exec('env');
        $content = trim($content);
        $lines   = explode("\n", $content);
        foreach ($lines as $line) {

        }
        var_dump($env);exit();
    }
}