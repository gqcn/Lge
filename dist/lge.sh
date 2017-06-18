#!/bin/bash
# 用于安装lge命令到当前系统
# 注意该脚本只安装lge命令(包括PHP-CLI)，并不会安装完整的PHP运行环境到当前系统
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
        sh $CURRENT_SCRIPT_PATH/scripts/subscripts/install-lge.sh
    else
        PHP_CLI_SCRIPT_PATH=$CURRENT_SCRIPT_PATH/scripts/subscripts/install-php-cli.sh
        echo "\033[31mYou should install php cli first, try $PHP_CLI_SCRIPT_PATH\033[0m"
    fi
