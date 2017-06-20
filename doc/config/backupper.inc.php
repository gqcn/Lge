<?php
/**
 * 服务器备份配置示例文件.
 * backup_center: 备份中心，只能有一个，多台服务器的内容统一备份到这台服务器磁盘上；
 * backup_groups: 备份分组，需要备份的服务器集群，可以备份的内容包括：
 *     1、MySQL数据库: 支持多个，支持按日期过期，可以是本地的，也可以是本地链接的其他MySQL服务器;
 *     2、本地文件夹: 支持多个，支持按日期过期;
 *
 * @author john
 */

return array(
    // 存放备份数据及文件的客户端信息(通过SSH远程登录客户端执行配备文件的写入)
    'backup_center' => array(
        'host'    => '192.168.2.102',
        'port'    => '22',
        'user'    => 'john',
        'pass'    => '8692651',
        'folder'  => '/home/john/temp/',
    ),
    // 需要备份的服务器信息
    'backup_groups' => array(
        // 服务器集群配置项名称，备份时作为一个目录名保存到备份客户端
        '测试服务器' => array(
            // 数据库列表，流程：通过ssh链接到服务器->根据数据库配置信息备份数据库->将数据库备份文件同步到备份中心->删除本地备份文件
            'data' => array(
                array(
                    'host'      => '192.168.2.124',
                    'port'      => '22',
                    'user'      => 'hhzl',
                    'pass'      => '123456',
                    'databases' => array(
                        array(
                            'host'  => '127.0.0.1',
                            'port'  => '3306',
                            'user'  => 'root',
                            'pass'  => '123456',
                            'names' => array(
                                'henghe' => 7,
                            ),
                        ),
                    ),
                ),
            ),
            // 文件备份列表，流程：通过ssh链接到服务器->通过rsync同步文件夹到备份中心
            'file' => array(
                array(
                    'host'      => '192.168.2.124',
                    'port'      => '22',
                    'user'      => 'hhzl',
                    'pass'      => '123456',
                    'folders'   => array(
                        '/home/hhzl/www/lge' => 3,
                    ),
                ),
            ),
        ),
    ),
);
