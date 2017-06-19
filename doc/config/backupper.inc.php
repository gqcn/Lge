<?php
/**
 * 服务器备份配置示例文件.
 * backup_client: 备份客户端，只能有一个，多台服务器的内容统一备份到这台服务器磁盘上；
 * backup_server: 备份服务端，需要备份的服务器，可以备份的内容包括：
 *     1、MySQL数据库: 支持多个，支持按日期过期，可以是本地的，也可以是本地链接的其他MySQL服务器;
 *     2、本地文件夹:   支持多个，支持按日期过期;
 *
 * @author john
 */

return array(
    // 存放备份数据及文件的客户端信息(通过SSH远程登录客户端执行配备文件的写入)
    'backup_client' => array(
        'host'    => '127.0.0.1',
        'port'    => '22',
        'user'    => 'john',
        'pass'    => '123456',
        'folder'  => '/home/john/temp/',
    ),
    // 需要备份的服务器信息
    'backup_server' => array(
        // 每个服务器的配置名称，备份时作为一个目录名保存到备份客户端
        'test' => array(
            'data' => array(
                array(
                    'host'  => '127.0.0.1',
                    'port'  => '3306',
                    'user'  => 'root',
                    'pass'  => '123456',
                    'names' => array(
                        'lge_playard' => 7,
                    ),
                ),
            ),

            'file' => array(
                array(
                    'host'    => '127.0.0.1',
                    'port'    => '22',
                    'user'    => 'john',
                    'pass'    => '123456',
                    'folders' => array(
                        '/home/john/Documents/' => 3,
                    ),
                ),
            ),

        ),
    ),
);
