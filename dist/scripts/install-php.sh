#!/bin/bash
# 用于在本地Linux环境下安装PHP运行环境 (PHP+Nginx+MySQL)

# 先判断本地是否已经安装好了php cli
#    which php > /dev/null 2>&1
#    if [ $? = 0 ]; then
#        echo "You've installed php cli"
#        exit;
#    fi

# 必需使用root用户执行
    if [ "$(id -u)" != "0" ]; then
       echo "This script must be run as root" 1>&2
       exit 1
    fi

# 首先判断并安装php cli工具
    # rhel
    which yum > /dev/null 2>&1
    if [ $? = 0 ] ; then
        yum install -y php-cli
    fi

    # debian
    which apt-get > /dev/null 2>&1
    if [ $? = 0 ] ; then
        apt-get update
        DEBIAN_FRONTEND='noninteractive' apt-get install -y php-cli
        DEBIAN_FRONTEND='noninteractive' apt-get install -y php5-cli
        DEBIAN_FRONTEND='noninteractive' apt-get install -y php7-cli
    fi

    which php  > /dev/null 2>&1
    if [ $? = 0 ]; then

        if [ $? = 0 ]; then
            `which php`
        else
            echo "PHP-CLI installation failed!"
        fi
    else
        echo "PHP-CLI installation failed!"
    fi



#    if [ "$OS" == "debian" ] ; then
#        echo ":: Installing packages"
#        apt-get update
#        DEBIAN_FRONTEND='noninteractive' apt-get install -y php
#
#        php php-fpm php-mysql php-mbstring php-mcrypt php-memcache php-memcached php-mongodb php-redis php-soap php-ssh2 || exit 1
#    fi

