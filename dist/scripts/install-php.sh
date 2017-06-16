#!/bin/bash
# 用于在本地Linux环境下安装PHP运行环境 (PHP+Nginx+MySQL)
    # 获取当前执行脚本的绝对路径
    CURRENT_SCRIPT_PATH=$(cd "$(dirname "$0")"; pwd)

# 必需使用root用户执行
    if [ "$(id -u)" != "0" ]; then
       echo "\033[31mThis script must be running as root\033[0m"
       exit 1
    fi

# 先判断本地是否已经安装好了php cli
    which php > /dev/null 2>&1
    if [ $? = 0 ]; then
        # 更新源
        apt-get update
        # 安装lge
        sh $CURRENT_SCRIPT_PATH/subscripts/install-lge.sh
        # 通过lge执行后续lnmp安装
        sh $CURRENT_SCRIPT_PATH/subscripts/install-lnmp.sh
        exit;
    fi

# 首先判断并安装php cli工具
    sh $CURRENT_SCRIPT_PATH/subscripts/install-php-cli.sh

    which php  > /dev/null 2>&1
    if [ $? = 0 ]; then
        # 安装lge
        sh $CURRENT_SCRIPT_PATH/subscripts/install-lge.sh
        # 通过lge执行后续lnmp安装
        sh $CURRENT_SCRIPT_PATH/subscripts/install-lnmp.sh
    fi