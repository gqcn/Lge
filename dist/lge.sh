#!/bin/bash
# 用于安装lge命令到当前系统
# 注意该脚本只安装lge命令(包括PHP-CLI)，并不会安装完整的PHP运行环境到当前系统
    # 获取当前执行脚本的绝对路径
    CURRENT_SCRIPT_PATH=$(cd "$(dirname "$0")"; pwd)

# 必需使用root用户执行
    if [ "$(id -u)" != "0" ]; then
       echo -e "\033[31mThis script must be running as root\033[0m"
       exit 1
    fi

# 修改子级脚本的可执行权限
    chmod +x $CURRENT_SCRIPT_PATH/scripts/*
    
# 先判断本地是否已经安装好了php cli
    which php > /dev/null 2>&1
    if [ $? = 1 ]; then
        which apt-get > /dev/null 2>&1
        if [ $? = 0 ] ; then
            apt-get update
            $CURRENT_SCRIPT_PATH/scripts/install-php-cli.sh
        fi
    fi

    $CURRENT_SCRIPT_PATH/scripts/install-lge.sh
