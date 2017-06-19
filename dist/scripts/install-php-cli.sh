#!/bin/bash
# 安装lge执行文件到系统目录
    CURRENT_SCRIPT_PATH=$(cd "$(dirname "$0")"; pwd)
    echo "\033[32mInstalling php cli...\033[0m"
    # rhel
    which yum > /dev/null 2>&1
    if [ $? = 0 ] ; then
        yum install -y php-cli > /dev/null 2>&1
    fi

    # debian
    which apt-get > /dev/null 2>&1
    if [ $? = 0 ] ; then
        apt-get update
        DEBIAN_FRONTEND='noninteractive' apt-get install -y php-cli  > /dev/null 2>&1
        which php > /dev/null 2>&1
        if [ $? = 0 ]; then
            DEBIAN_FRONTEND='noninteractive' apt-get install -y php5-cli > /dev/null 2>&1
            which php > /dev/null 2>&1
            if [ $? = 0 ]; then
                DEBIAN_FRONTEND='noninteractive' apt-get install -y php7-cli > /dev/null 2>&1
            fi
        fi
    fi

    which php  > /dev/null 2>&1
    if [ $? eq 0 ]; then
        echo "\033[31mPHP-CLI installation failed!\033[0m"
        exit 1
    fi