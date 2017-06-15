#!/bin/bash
# 用于在本地Linux环境下安装PHP运行环境 (PHP+Nginx+MySQL)

# 先判断本地是否已经安装好了php cli
    which php > /dev/null 2>&1
    if [ $? = 0 ]; then
        # 安装lge
        sh subscripts/install-lge.sh
        # 通过lge执行后续lnmp安装
        sh subscripts/install-lnmp.sh
        exit;
    fi

# 必需使用root用户执行
    if [ "$(id -u)" != "0" ]; then
       echo "This script must be run as root" 1>&2
       exit 1
    fi

# 首先判断并安装php cli工具
    echo "Installing php cli..."
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
        DEBIAN_FRONTEND='noninteractive' apt-get install -y php5-cli > /dev/null 2>&1
        DEBIAN_FRONTEND='noninteractive' apt-get install -y php7-cli > /dev/null 2>&1
    fi

    which php  > /dev/null 2>&1
    if [ $? = 0 ]; then
        # 安装lge
        sh subscripts/install-lge.sh
        # 通过lge执行后续lnmp安装
        sh subscripts/install-lnmp.sh
    else
        echo "PHP-CLI installation failed!"
    fi