<?php
namespace Lge;

if (!defined('LGE')) {
	exit('Include Permission Denied!');
}

class Controller_Backupper extends Controller_Base
{
    public $logCategory = 'crontab/backupper';
    
    /**
     * 默认入口函数.
     *
     * @return void
     */
    public function index()
    {
        Logger::log('start', $this->logCategory);

        $config       = Config::get('backupper', true);
        $clientConfig = $config['backup_client'];
        $serverConfig = $config['backup_server'];
        $backupDir    = rtrim($clientConfig['folder'], '/');
        foreach ($serverConfig as $groupName => $backupConfig) {
            // 首先备份数据库
            if (!empty($backupConfig['data'])) {
                foreach ($backupConfig['data'] as $dataConfig) {
                    $host = $dataConfig['host'];
                    $port = $dataConfig['port'];
                    $user = $dataConfig['user'];
                    $pass = $dataConfig['pass'];
                    $dataBackupDir = "{$backupDir}/{$groupName}/{$host}/data";
                    if (!file_exists($dataBackupDir)) {
                        @mkdir($dataBackupDir, 0777, true);
                    }
                    // 执行备份
                    foreach ($dataConfig['names'] as $name) {
                        $filePath      = "{$dataBackupDir}/{$name}.sql";
                        $filePathTemp  = "{$dataBackupDir}/{$name}.temp.sql";
                        $localShellCmd = "mysqldump -C -h{$host} -P{$port} -u{$user} -p{$pass} {$name} > {$filePathTemp}";
                        shell_exec($localShellCmd);
                        // 判断是否备份成功(大于1K)，使用临时文件防止失败时被覆盖
                        if (filesize($filePathTemp) > 1024) {
                            copy($filePathTemp, $filePath);
                        }
                        unlink($filePathTemp);
                    }
                }
            }

            // 其次增量备份项目文件
            if (!empty($backupConfig['file'])) {
                foreach ($backupConfig['file'] as $fileConfig) {
                    $fileBackupDir = "{$backupDir}/{$groupName}/{$fileConfig['host']}/file/";
                    if (!file_exists($fileBackupDir)) {
                        @mkdir($fileBackupDir, 0777, true);
                    }
                    foreach ($fileConfig['folders'] as $folder) {
                        $host = $clientConfig['host'];
                        $port = $clientConfig['port'];
                        $user = $clientConfig['user'];
                        $pass = $clientConfig['pass'];
                        $folder = rtrim($folder, '/');
                        $sshShellCmds = array(
                            array("rsync -aurvz --delete -e 'ssh -p {$port}' {$folder} {$user}@{$host}:{$fileBackupDir}", false, 10),
                            array("yes", true, 10),
                            array($pass, true, 86400),
                        );
                        $ssh = new Lib_Network_Ssh($fileConfig['host'], $fileConfig['port'], $fileConfig['user'], $fileConfig['pass']);
                        $ssh->shellCmd($sshShellCmds);
                    }
                }
            }
        }

        Logger::log('end', $this->logCategory);
    }

}