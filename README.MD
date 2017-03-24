# LGE(Lightweight Glue Engine)轻量级开发框架
## 特点
    1. 轻量级，核心代码不足800K；
    2. 逻辑简单，结构清晰，运行高效；
    3. 易于集成到其他框架，并互不影响；

# 环境安装(Debian/Ubuntu)
    1. 基础环境
    sudo apt-get install memcached nginx mysql-server redis-server
    sudo apt-get install php5 php5-cli php5-common php5-curl php5-dev php5-gd php5-mysql php5-fpm php5-memcached php5-redis
    2. 附加扩展
    sudo apt-get install php5-odbc php5-sqlite


# 环境配置
    Nginx配置示例
    server {
        listen      80;
        root        本地LGE项目根目录/src;
        index       index.html index.htm index.php;
        server_name 服务器域名;
        #伪静态配置
        location / {
            try_files $uri /index.php?$query_string;
        }
        #PHP解析配置
        location ~ \.php$ {
            include      snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php5-fpm.sock;
        }
    }
    
# 其他
    1. 框架使用文档请参考 /doc/Lge.pdf；
    2. 代码示例请参考 Lge_Playard 项目；
    3. 框架使用文档没有完整说明框架提供的每个类的用法，请自行查看 /_frm/core/component 及 /_frm/library 下的源代码；

