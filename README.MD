# 这是使用PhpMe框架的示例项目

# 环境安装
    ## 基于Ubuntu
    sudo apt-get install mercurial memcached nginx mysql-server redis-server
    sudo apt-get install php5 php5-cli php5-common php5-curl php5-dev php5-gd php5-mysql php5-fpm php5-memcached php5-redis
    ## 附加扩展
    sudo apt-get install php5-odbc php5-sqlite


# 安装配置
    ## Nginx配置示例
    server {
        listen      80;
        root        /media/sf_Workspace/PhpMe/src;
        index       index.html index.htm index.php;
        server_name www.localhost.com;

        location / {
            try_files $uri /index.php?$query_string;
        }

        location ~ \.php$ {
            include      snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php5-fpm.sock;
        }````
    }


# Crontab 定时任务(示例)
    ## 日常任务
    */10 * * * * cd /home/john/Workspace/PhpMe/PhpMe/src && php index.php --s=crontab --c=ip-updater 2>&1 &
    0 1 * * * cd /home/john/Workspace/PhpMe/PhpMe/src && php index.php --s=crontab --c=backupper 2>&1 &

    ## 数据备份(root)
    0 1  * * * mount /dev/sdb1 /media/backup/ > /media/backup/mount.log 2>&1 &
    0 2  * * * rsync -vaur --delete /home /media/backup/ > /media/backup/home_backup.log 2>&1 &
    0 2  * * * rsync -vaur --delete /etc /media/backup/ > /media/backup/etc_backup.log 2>&1 &
    0 10 * * * umount /media/backup/ > /media/backup/umount.log 2>&1 &


