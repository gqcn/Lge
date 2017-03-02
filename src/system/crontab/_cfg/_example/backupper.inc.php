<?php
/**
 * 服务器备份配置.
 *
 */
return array(
    // 存放备份数据及文件的客户端信息(通过SSH远程登录客户端执行配备文件的写入)
    'backup_client' => array(
        'host'    => 'johnx.cn',
        'port'    => '8822',
        'user'    => 'john',
        'pass'    => '8692651',
        'folder'  => '/home/john/Backup/',
    ),
    // 需要备份的服务器信息
    'backup_server' => array(
        // 按照公司或者类型进行分组划分，便于管理备份内容
        '分组名称' => array(
            // 数据库
            'data' => array(
                array(
                    'host'  => '211.149.236.141',
                    'port'  => '3306',
                    'user'  => 'root',
                    'pass'  => '',
                    'names' => array(
                        'phpme_picc',
                    ),
                ),
                array(
                    'host'  => '211.149.246.197',
                    'port'  => '3306',
                    'user'  => 'root',
                    'pass'  => '',
                    'names' => array(
                        'iwshop',
                    ),
                ),
            ),

            // 网站文件
            'file' => array(
                array(
                    'host'    => '211.149.236.141',
                    'port'    => '22000',
                    'user'    => 'root',
                    'pass'    => '',
                    'folders' => array(
                        '/home/john/Workspace/picc',
                    ),
                ),
                array(
                    'host'    => '211.149.246.197',
                    'port'    => '22000',
                    'user'    => 'root',
                    'pass'    => '',
                    'folders' => array(
                        '/home/john/www/zbyl',
                    ),
                ),
            ),
        ),
    ),
);