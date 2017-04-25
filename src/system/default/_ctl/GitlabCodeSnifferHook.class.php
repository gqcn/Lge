<?php
/**
 * 该脚本用于gitlab的服务端 pre-receive hook钩子中，使用PHP_CodeSniffer对提交代码进行代码检测。
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Giblab + PHP_CodeSniffer服务端自动检测脚本。
 */
class Controller_GitlabCodeSnifferHook extends BaseController
{

    /**
     * 入口函数。
     *
     * @return void
     */
    public function index()
    {
        try {
            $exitCode = 0;
            $rawInput = file_get_contents('php://stdin');
            $rawArray = explode(' ', $rawInput);
            if (!empty($rawArray)) {
                $result = shell_exec("git diff --name-only {$rawArray[0]} {$rawArray[1]}");
                if (!empty($result)) {
                    $currentTime = date('YmdHis');
                    $baseDirPath = "/tmp/lge-code-sniffer-{$currentTime}/";
                    $files       = explode("\n", trim($result));
                    foreach ($files as $file) {
                        $type = Lib_FileSys::getFileType($file);
                        if ($type == 'php') {
                            // 这里使用proc_open替换shell_exec是为了防止标准错误直接输出到终端上
                            $descripts = array(
                                // 0 => array("pipe", "r"),
                                1 => array("pipe", "w"),
                                2 => array("pipe", "w"),
                            );
                            $process = proc_open("git show {$rawArray[1]}:{$file}", $descripts, $pipes);
                            $result  = stream_get_contents($pipes[1]);
                            // fclose($pipes[0]);
                            fclose($pipes[1]);
                            fclose($pipes[2]);
                            proc_close($process);
                            // 文件被删除，或者文件内容为空，则不需要检测
                            if (empty($result) || strcasecmp($result, 'null') == 0) {
                                continue;
                            }
                            $filePath = "{$baseDirPath}{$file}";
                            $dirPath  = dirname($filePath);
                            if (!file_exists($dirPath)) {
                                @mkdir($dirPath, 0777, true);
                            }
                            if (file_exists($dirPath)) {
                                file_put_contents($filePath, $result);
                            } else {
                                echo "Cannot create dir path:{$dirPath}".PHP_EOL;
                            }
                        }
                    }
                    // 执行代码检测
                    $result = '';
                    if (file_exists($baseDirPath)) {
                        $result = shell_exec("phpcs {$baseDirPath}");
                        $result = trim($result);
                    }
                    if (empty($result)) {
                        $exitCode = 0;
                    } else {
                        $exitCode = 1;
                        $result   = str_replace($baseDirPath, '/', $result);
                        echo "======================================================================\n";
                        echo "======================= Code Sniffer Errors ==========================\n";
                        echo "======================================================================\n";
                        echo ($result);
                        echo PHP_EOL.PHP_EOL;
                        echo "======================================================================\n";
                    }
                    shell_exec("rm {$baseDirPath} -fr");
                }
            }
        } catch (\Exception $e) {
            $exitCode = 1;
            echo $e->getMessage().PHP_EOL;
        }
        exit($exitCode);
    }

}
