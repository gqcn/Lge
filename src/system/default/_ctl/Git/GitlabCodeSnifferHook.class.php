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
class Controller_Git_GitlabCodeSnifferHook extends BaseController
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
                $files = $this->_getDiffFiles($rawArray);
                if (!empty($files)) {
                    $currentTime = date('YmdHis');
                    $baseDirPath = "/tmp/{$currentTime}-lge-code-sniffer/";
                    foreach ($files as $file) {
                        $type = Lib_FileSys::getFileType($file);
                        if ($type == 'php') {
                            $result = $this->_getGitFileContent($file, $rawArray[1]);
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
                        $ignoreContent = $this->_getCSIgnoreContent($rawArray[1]);
                        $ignore = empty($ignoreContent) ? "" : "--ignore={$ignoreContent}";
                        $result = shell_exec("cd {$baseDirPath} && phpcs {$baseDirPath} {$ignore}");
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

    /**
     * 获取本次提交的不同的文件，构成数组返回。
     *
     * @param array $rawArray 输入参数数组
     *
     * @return array
     */
    private function _getDiffFiles(array $rawArray)
    {
        $result = '';
        $files  = array();
        // 有可能是一个新branch
        if ($rawArray[0] == '0000000000000000000000000000000000000000') {
            $currentBranch = $this->_getCurrentBranch();
            if (!empty($currentBranch)) {
                $result = shell_exec("git diff --name-only {$currentBranch} {$rawArray[1]}");
            }
        } else {
            $result = shell_exec("git diff --name-only {$rawArray[0]} {$rawArray[1]}");
        }
        $result = trim($result);
        if (!empty($result)) {
            $files = explode("\n", $result);
        }
        return $files;
    }

    /**
     * 获取当前版本库所在分支
     *
     * @return string
     */
    private function _getCurrentBranch()
    {
        $result = shell_exec("git branch|grep '*'");
        $result = preg_replace('/[\*\r\s\n\t]*/', '', $result);
        return $result;
    }

    /**
     * 获得code sniffer忽略文件内容，并构建成phpcs --ignore=xxx 需要的参数返回.
     *
     * @param string $commit 提交版本号
     *
     * @return string
     */
    private function _getCSIgnoreContent($commit = 'HEAD')
    {
        $result  = '';
        $content = $this->_getGitFileContent('.csignore', $commit);
        if (!empty($content)) {
            $array  = explode("\n", trim($content));
            $result = implode(',', $array);
        }
        return $result;
    }

    /**
     * 获得指定提交的文件内容.
     *
     * @param string $file   文件名称
     * @param string $commit 提交版本号
     *
     * @return string
     */
    private function _getGitFileContent($file, $commit)
    {
        // 这里使用proc_open替换shell_exec是为了防止标准错误直接输出到终端上
        $descripts = array(
            // 0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w"),
        );
        $process = proc_open("git show {$commit}:{$file}", $descripts, $pipes);
        $result  = stream_get_contents($pipes[1]);
        // fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        return $result;
    }

}
