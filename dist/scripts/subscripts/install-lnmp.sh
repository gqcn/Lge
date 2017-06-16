#!/bin/bash
# 通过lge执行后续lnmp安装
    echo "\033[32mInstalling lnmp using lge\033[0m"
    which lge > /dev/null 2>&1
    if [ $? = 0 ]; then
        lge lnmp
    else
        echo "\033[31mlge not found, exit installation\033[0m"
        exit;
    fi


