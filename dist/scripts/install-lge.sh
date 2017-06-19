#!/bin/bash
# 安装lge执行文件到系统目录
    CURRENT_SCRIPT_PATH=$(cd "$(dirname "$0")"; pwd)
    echo -e "\033[32mInstalling lge cli...\033[0m"
    if [ -f ${CURRENT_SCRIPT_PATH}/../lge.phar ]; then
        `which php` ${CURRENT_SCRIPT_PATH}/../lge.phar install
    else
        echo -e "\033[31mInstall lge cli failed, exit installation\033[0m"
        exit 1
    fi