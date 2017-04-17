<?php
namespace Lge;

if(!defined('LGE')){
    exit('Include Permission Denied!');
}

/**
 * Git方式自动部署程序到服务器。
 * 使用说明：
 * 1、项目客户端应当已经保存密码;
 * 2、如果是ssh push那么应当保证客户端与服务端已经通过ssh的authorized_keys授权，或者，安装sshpass工具，并在配置文件中对服务器指定密码；
 * 2、在项目根目录下执行；
 */
class Controller_GitDeploy extends BaseController
{
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

                echo ($k + 1).": {$resp} {$branch}\n";

                if (empty($item[2])) {
                    $cmd = "git push {$resp} {$branch}";
                } else {
                    $this->_checkAndInitGitRepoForRemoteServer($resp, $branch, $item[2]);
                    $cmd = "sshpass -p {$item[2]} git push {$resp} {$branch}";
                }
                exec($cmd);
                echo "\n";
            }
        }
    }

    /**
     * 检查目标服务器是否已经初始化。
     *
     * @param $resp
     * @param $branch
     * @param $pass
     */
    private function _checkAndInitGitRepoForRemoteServer($resp, $branch, $pass)
    {
        // 仓库格式形如： ssh://john@120.76.249.69//home/john/www/lge
        if (preg_match("/ssh:\/\/(.+?)@([^:]+):{0,1}(\d*)\/(\/.+)/", $resp, $match)) {
            $user   = $match[1];
            $host   = $match[2];
            $port   = empty($match[3]) ? 22 : $match[3];
            $path   = rtrim($match[4], '/');
            $ssh    = new Lib_Network_Ssh($host, $port, $user, $pass);
            $result = $ssh->syncCmd("if [ -d \"{$path}/.git\" ]; then echo 1; else echo 0; fi");
            $result = trim($result);
            if ($result == "0") {
                // 如果服务器的git目录不存在那么初始化目录
                $ssh->syncCmd("mkdir -p \"{$path}\" && cd \"{$path}\" && git init && git config receive.denyCurrentBranch ignore");
                $ssh->sendFile($this->_getGitPostReceiveHookFilePath(), $path.'/.git//hooks/post-receive', 0777);
            } else {
                // 否则更换分支为指定分支
                $ssh->syncCmd("cd \"{$path}\" && git checkout {$branch} -f");
            }
        }
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
