/* 初始化数据库SQL文件 */
REPLACE INTO `user`
(`uid`,`puid`,`gid`,`wechat_id`,`passport`,`password`,`nickname`,`status`,`gender`,`from`,`create_time`,`update_time`)
VALUES (1,0,1,0,'admin','f74bd168de2480000a04369ea0ea22b0','超级管理员',1,1,'system',1449295890,unix_timestamp(now()));


DELETE FROM `user_group` WHERE `id` >= 0 AND `group_key`='default_group';

REPLACE INTO `user_group`
(`id`,`name`,`order`,`group_key`,`brief`,`create_time`)
VALUES
(0,'默认用户组',0,'default_group','默认用户组，用户组编号默认为0',unix_timestamp(now())),
(1,'超级管理员',1,'super_admin','初始化管理员，拥有一切权限',unix_timestamp(now()));

UPDATE `user_group` SET `id`=0 WHERE `id` >= 0 AND `group_key`='default_group';




