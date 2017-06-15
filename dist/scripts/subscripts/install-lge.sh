#!/bin/bash
# 安装lge执行文件到系统目录
echo "Installing lge cli..."
if [ -f ${PWD}/index.php ]; then
    `which php` ${PWD}/index.php install > /dev/null 2>&1
elif [ -f ${PWD}/install-php.sh ]; then
    `which php` ${PWD}/../lge.phar install > /dev/null 2>&1
fi