
<h1 align="center">CubingChina</h1>
<p align="center">
    <a href="https://github.com/CubingChina/cubingchina/blob/master/LICENSE"><img alt="GitHub" src="https://img.shields.io/badge/license-GPL2.0-blue"></a>
    <a href="https://cubing.com/"><img alt="Documentation" src="https://img.shields.io/badge/website-Cubingchina-green"></a>
    <br>
<a href="https://cubing.com/"><img alt="Documentation" src="https://img.shields.io/badge/Code%20With%20PHP-grey?style=for-the-badge&logo=php&logoSize=samll"></a>
</p>

<h4 align="center">
    <p>
        <b>简体中文</b> | <a href="https://github.com/CubingChina/cubingchina/blob/master/README.md">English</a>
    </p>
</h4>

---
可查看粗饼网 https://cubing.com 或 https://cubingchina.com

# 安装
### 环境依赖
1、[`Nginx`](http://nginx.org/) / [`Apache`](http://www.apache.org/)

2、[`PHP7.0+`](http://php.net/)
​	可参考官网教程安装部署`PHP,` 后续部署步骤供参考。
###### 配置`php-fpm`
```bash
sudo mkdir /usr/local/php
sudo cp php.ini-development /usr/local/php-8.1.29/etc/php.ini
cp /usr/local/php-8.1.29/etc/php-fpm.conf.default /usr/local/php-8.1.29/etc/php-fpm.conf

cp /usr/local/php-8.1.29/etc/php-fpm.d/www.conf.default /usr/local/php-8.1.29/etc/php-fpm.d/www.conf

sudo groupadd www-data
sudo useradd -g www-data www-data
sudo mkdir /var/run/www/
sudo chown -R www-data:www-data /var/run/www
```
###### 修改` /usr/local/php-8.1.29/etc/php-fpm.d/www.conf`配置
```
listen = /var/run/www/php-cgi.sock
```
###### php-fpm系统脚本
写入 `/usr/lib/systemd/system/php-fpm.service`
```systemverilog
[Unit]
Description=The PHP FastCGI Process Manager
After=syslog.target network.target

[Service]
Type=simple
PIDFile=/usr/local/php-8.1.29/var/run/php-fpm.pid
ExecStart=/usr/local/php-8.1.29/sbin/php-fpm --nodaemonize --fpm-config /usr/local/php-8.1.29/etc/php-fpm.conf
ExecReload=/bin/kill -USR2 $MAINPID

[Install]
WantedBy=multi-user.target
```
```bash
systemctl enable php-fpm.service
systemctl restart php-fpm.service
```
3、[`Redis`](https://redis.io/)

4、 [`Redis Extension`](https://github.com/phpredis/phpredis)


```bash
git clone https://github.com/phpredis/phpredis.git
phpize
./configure --with-php-config=/usr/local/php-7.4.33/bin/php-config --enable-redis
sudo make
sudo make install
```

5、[`MySQL5.1+`](http://www.mysql.com/)
- 端口3306
- root密码为空
- 主机需要有`mysql`命令，如果为docker部署可安装`mysql-client`

6、[`Yii Framework 1.1.20`](http://www.yiiframework.com/)
- 注意只能为该版本，否则可能出现意外

7、[`Composer`](https://getcomposer.org/)

8、[`Nodejs`](https://nodejs.org/)



### 部署步骤

1、下载本项目到`/path/to/cubingchina`.

2、 拷贝`framework` 目录到  `Yii`所在工作目录 ， `/path/to/cubingchina/../framework`.

3、创建数据库

```sql
CREATE DATABASE cubingchina;
CREATE DATABASE cubingchina_dev;
CREATE DATABASE wca_0;
CREATE DATABASE wca_1;
```
4、初始化数据库
- 打开粗饼项目中`protected/config`目录，创建一个`wcaDb`的标识文件
  - 这个文件应该是用于自动更新wca数据的时候，切换对应的数据库。
  - 写0即可
- 执行`sql脚本 `： `structure.sql` 和`data.sql` 到`cubingchina`、 `cubingchina_dev`两个数据库
  - 注意顺序
- 执行`cubingchina/protected/commands/shell/wca_data_sync.sh`脚本以同步`wca`数据。

5、安装composer依赖
```bash
cd cubingchina/protected
composer update
composer install
```

6、安装node依赖
```bash
npm config set registry http://mirrors.cloud.tencent.com/npm/
npm config set strict-ssl false
cd cubingchina/public/f && npm i --verbose && npm run build
```

7、配置`Nginx`
- 参考配置如下

```bash
map $http_upgrade $connection_upgrade {
    default upgrade;
    '' close;
}

server {
    listen 8001 backlog=4096;
    server_name localhost;
    root /path/to/cubingchina/public;

    access_log /var/log/cubingchina/access.log;
    error_log /var/log/cubingchina/error.log;

    location / {
        index index.php;
        try_files $uri $uri/ /index.php?$args;
    }

    location /ws {
        proxy_pass http://127.0.0.1:8081;
        proxy_http_version 1.1;
        proxy_set_header Cookie $http_cookie;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_set_header Host $host;
    }

    # PHP 文件处理
    location ~ \.php$ {
        fastcgi_pass   unix:/var/run/www/php-cgi.sock;
        fastcgi_index  index.php;
		fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include        fastcgi_params;
    }
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg)$ {
        expires max;
        log_not_found off;
    }
}
```
可能存在的权限问题时：
```bash
sudo chown www-data:www-data /var/run/www/php-cgi.sock
sudo chmod 660 /var/run/www/php-cgi.sock
```

8、配置项目读写权限
```bash
chmod a+x cubingchina/public/assets
chmod a+x cubingchina/protected/runtime
```

9、同步数据库配置
```
cubingchina/protected/yiic migrate
```

10、如果需要开启直播功能时
```bash
cubingchina/protected/yiic websocket
```

11、如果需要切换开发模式，可通过`ENV`将`php`切换到`dev`

12、结束

### 建议或可能的错误

1、本地服务器环境包括` Apache`、`Mysql`、`PHP`，例如建议使用 [`WAMP`](http://www.wampserver.com/en/)、[`MAMP`](https://www.mamp.info/en/) 或 [`XAMMP`](https://www.apachefriends.org/index.html)

2、`wca_data_sync.sh` 包含 `grep`, `lftp`, `sed` 命令，请在运行此脚本之前安装它们。

3、Apache 的重写规则必须配置到 `index.php`。
