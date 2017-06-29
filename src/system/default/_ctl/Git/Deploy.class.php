<?php
/**
 * Git方式自动部署程序到服务器。
 * 使用说明：
 * 1、项目客户端应当已经保存密码;
 * 2、如果是ssh push那么应当保证客户端与服务端已经通过ssh的authorized_keys授权，或者，安装sshpass工具，并在配置文件中对服务器指定密码；
 * 3、在项目根目录下执行；
 * 配置文件格式如下：
 *
 * array(
 *     '配置项名称' => array(
 *          array('服务器地址', '默认push分支名称', 'ssh push用户对应的服务器密码(非必须)')
 *     ),
 * );
 *
 * @author john
 */

namespace Lge;

if (!defined('LGE')) {
    exit('Include Permission Denied!');
}

/**
 * Git方式自动部署.
 */
class Controller_Git_Deploy extends BaseController
{

    /**
     * 入口函数.
     *
     * @return void
     */
    public function index()
    {
        $pwd = $this->_server['PWD'];
        if (empty($pwd)) {
            echo "当前工作目录地址不能为空！\n";
            exit();
        }
        $option       = Lib_ConsoleOption::instance();
        $deployKey    = $option->getOption('key', 'default');
        $deployConfig = $option->getOption('config');
        if (!empty($deployConfig) && file_exists($deployConfig)) {
            $configArray = include($deployConfig);
            $deployArray = isset($configArray[$deployKey]) ? $configArray[$deployKey] : array();
        } else {
            $deployArray = Config::get($deployKey, 'git-deploy', true);
        }
        if (empty($deployArray)) {
            echo "找不到\"{$deployKey}\"对应的配置项！\n";
            exit();
        } else {
            chdir($pwd);
            foreach ($deployArray as $k => $item) {
                $resp   = $item[0];
                $branch = empty($deployBranch) ? $item[1] : $deployBranch;
                if ($branch == '*') {
                    // 推送所有的分支及标签
                    exec('git config push.default matching');
                    $branch   = '';
                    $branches = array('--all', '--tags');
                } else {
                    $branches = array($branch);
                }
                echo ($k + 1).": {$resp} {$branch}\n";
                foreach ($branches as $branch) {
                    if (empty($item[2])) {
                        $pushResult = $this->_exeCmd("git push {$resp} {$branch}");
                        $this->_checkPushResult($pushResult);
                    } else {
                        $this->_checkAndInitGitRepoForRemoteServer($resp, $item[2]);
                        $pushResult = $this->_exeCmd("sshpass -p {$item[2]} git push {$resp} {$branch}");
                        $this->_checkPushResult($pushResult);
                        $this->_checkAndChangeRemoteRepoToSpecifiedBranch($resp, $branch, $item[2]);
                    }
                }
                echo "\n";
            }
        }
    }

    /**
     * 检测git push提交结果，并根据结果判断是否执行成功。
     *
     * @param string $pushResult 提交结果
     *
     * @return void
     */
    private function _checkPushResult($pushResult)
    {
        echo $pushResult;
        if (preg_match('/(error\W+)|(fatal\W+)/i', $pushResult)) {
            echo <<<MM
*******************************************************************************
********* ERROR OCCURRED: Please check and fix it before next push ************
*******************************************************************************


MM;
            exit();
        }
    }

    /**
     * 执行Linux命令，数组形式返回执行结果.
     *
     * @param string $cmd Linux命令
     *
     * @return string
     */
    private function _exeCmd($cmd)
    {
        $result = Lib_Console::execCommand($cmd);
        return $result['stdout'];
    }

    /**
     * 检查目标服务器是否已经初始化。
     *
     * @param string $resp 版本库地址。
     * @param string $pass 服务器SSH密码。
     *
     * @return void
     */
    private function _checkAndInitGitRepoForRemoteServer($resp, $pass)
    {
        $parsed = $this->_parseRepository($resp);
        if (!empty($parsed)) {
            $path = $parsed['path'];
            $ssh  = $this->_getSshClientByResp($resp, $pass);
            if (!empty($ssh)) {
                $result = $ssh->syncExec("if [ -d \"{$path}/.git\" ]; then echo 1; else echo 0; fi");
                $result = trim($result);
                if ($result == "0") {
                    // 如果服务器的git目录不存在那么初始化目录
                    $ssh->syncExec("mkdir -p \"{$path}\" && cd \"{$path}\" && git init && git config receive.denyCurrentBranch ignore");
                    $ssh->sendFile($this->_getGitPostReceiveHookFilePath(), $path.'/.git/hooks/post-receive', 0777);
                }
            }
        }
    }

    /**
     * 切换服务器上的分支为指定分支.
     *
     * @param string $resp   版本库地址。
     * @param string $branch 分支名称。
     * @param string $pass   服务器SSH密码。
     *
     * @return void
     */
    private function _checkAndChangeRemoteRepoToSpecifiedBranch($resp, $branch, $pass)
    {
        $parsed = $this->_parseRepository($resp);
        if (!empty($parsed)) {
            $path = $parsed['path'];
            $ssh  = $this->_getSshClientByResp($resp, $pass);
            if (!empty($ssh)) {
                $ssh->syncExec("cd \"{$path}\" && git checkout {$branch} -f");
            }
        }
    }

    /**
     * 解析版本库，将ssh的版本库解析为用户账号、地址、端口、路径的形式。
     *
     * @param string $resp 版本库地址。
     *
     * @return array
     */
    private function _parseRepository($resp)
    {
        // SSH仓库格式形如： ssh://john@120.76.249.69//home/john/www/lge
        $result = array();
        if (preg_match("/ssh:\/\/(.+?)@([^:]+):{0,1}(\d*)\/(\/.+)/", $resp, $match)) {
            $result['user'] = $match[1];
            $result['host'] = $match[2];
            $result['port'] = empty($match[3]) ? 22 : $match[3];
            $result['path'] = rtrim($match[4], '/');
        }
        return $result;
    }

    /**
     * 获得版本库服务器的SSH链接客户端。
     *
     * @param string $resp 版本库地址
     * @param string $pass 版本库服务器SSH密码
     *
     * @return Lib_Network_Ssh|null
     */
    private function _getSshClientByResp($resp, $pass = null)
    {
        static $sshClients = array();
        $parsed = $this->_parseRepository($resp);
        if (!empty($parsed)) {
            $user      = $parsed['user'];
            $host      = $parsed['host'];
            $port      = $parsed['port'];
            $checkKey  = "{$user}@{$host}";
            if (!isset($sshClients[$checkKey])) {
                $sshClients[$checkKey] = new Lib_Network_Ssh($host, $port, $user, $pass);
            }
            return $sshClients[$checkKey];
        }
        return null;
    }

    /**
     * 获得自动部署所需的hook文件。
     *
     * @return string
     */
    private function _getGitPostReceiveHookFilePath()
    {
        $hookFilePath = '/tmp/lge_auto_git_repo_post-receive';
        if (!file_exists($hookFilePath)) {
            $hookContent  = <<<MM
#!/bin/sh
export GIT_WORK_TREE=\${PWD}/..
export GIT_DIR=\${GIT_WORK_TREE}/.git
cd \${GIT_WORK_TREE} && git checkout -f; 
MM;
            file_put_contents($hookFilePath, $hookContent);
        }
        return $hookFilePath;
    }

}
