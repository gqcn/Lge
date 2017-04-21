<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}

/**
 * 该脚本用于gitlab的服务端 pre-receive hook钩子中，使用PHP_CodeSniffer对提交代码进行代码检测。
 */
class Controller_GitlabCodeSnifferHook extends BaseController
{
    public function index()
    {
        try {
            $rawInput = file_get_contents('php://stdin');
            $rawArray = explode(' ', $rawInput);
            if (!empty($rawArray)) {
                $result = shell_exec("git diff --name-only {$rawArray[0]} {$rawArray[1]}");
                if (!empty($result)) {
                    $files = explode("\n", trim($result));
                    foreach ($files as $file) {
                        $type = Lib_FileSys::getFileType($file);
                        if ($type == 'php') {
                            $result = shell_exec("git show {$rawArray[1]}:{$file}");
                            // 文件被删除，则不需要检测
                            if (preg_match("/fatal:.+?Path.+?does not exist in.+?/", $result)) {
                                continue;
                            }
                            $filePath = "/tmp/{$file}";
                            $dirPath  = dirname($filePath);
                            if (!file_exists($dirPath)) {
                                @mkdir($dirPath, 0777, true);
                            }
                            if (file_exists($dirPath)) {
                                file_put_contents($filePath, $result);
                                $result = shell_exec("phpcs {$filePath}");
                                var_dump($result);
                            } else {
                                echo "Cannot create dir path:{$dirPath}".PHP_EOL;
                            }

                        }
                    }
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        echo "\n";
        exit(1);
    }

}
