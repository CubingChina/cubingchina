<h1 align="center">CubingChina</h1>

<p align="center">
    <a href="https://github.com/CubingChina/cubingchina/blob/master/LICENSE"><img alt="GitHub" src="https://img.shields.io/badge/license-GPL2.0-blue"></a>
    <a href="https://cubing.com/"><img alt="Documentation" src="https://img.shields.io/badge/website-Cubingchina-green"></a>
<br>
<a href="https://cubing.com/"><img alt="Documentation" src="https://img.shields.io/badge/Code%20With%20PHP-grey?style=for-the-badge&logo=php&logoSize=samll"></a>
</p>



<h4 align="center">
    <p>
        <a href="https://github.com/CubingChina/cubingchina/blob/master/README_CN.md">简体中文</a> | <b>English</b>
    </p>
</h4>


---
The official website of Cubing China https://cubingchina.com.


# Installation

### Environments
1、[`Nginx`](http://nginx.org/) / [`Apache`](http://www.apache.org/)

2、[`PHP7.0+`](http://php.net/)
You can refer to the official tutorial for installing and deploying PHP.
The subsequent deployment steps are provided for reference.
###### Configuration `php-fpm`
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
###### update ` /usr/local/php-8.1.29/etc/php-fpm.d/www.conf` config
```
listen = /var/run/www/php-cgi.sock
```
###### php-fpm System script
write `/usr/lib/systemd/system/php-fpm.service`
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
- Port: 3306
- Root password: empty
- The host must have the mysql command installed. If deployed via Docker, you can install mysql-client.

6、[`Yii Framework 1.1.20`](http://www.yiiframework.com/)
- Note: Only this version is supported; otherwise, unexpected issues may occur.

7、[`Composer`](https://getcomposer.org/)

8、[`Nodejs`](https://nodejs.org/)



### Steps
1、Clone this repo to `/path/to/cubingchina`.

2、Put `framework` directory of `Yii` to `/path/to/cubingchina/../framework`.

3、Create four databases.
```sql
CREATE DATABASE cubingchina;
CREATE DATABASE cubingchina_dev;
CREATE DATABASE wca_0;
CREATE DATABASE wca_1;
```

4、Initialize the database
- Open the `protected/config` directory in the Cubing project and create an identifier file named `wcaDb`.
  - This file is used to switch to the corresponding database during the automatic update of WCA data.
  - Simply write 0 in the file.
- Execute the SQL scripts: `structure.sql` and `data.sql` in the databases `cubingchina` and `cubingchina_dev`.
- Run the script `cubingchina/protected/commands/shell/wca_data_sync.sh` to synchronize WCA data.

5、Install Composer
```bash
cd cubingchina/protected
composer update
composer install
```

6、Install Node
```bash
npm config set registry http://mirrors.cloud.tencent.com/npm/
npm config set strict-ssl false
cd cubingchina/public/f && npm i --verbose && npm run build
```

7、Configuration Nginx
- config like:
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
Possible permission issues include:
```bash
sudo chown www-data:www-data /var/run/www/php-cgi.sock
sudo chmod 660 /var/run/www/php-cgi.sock
```

8、Configure project read and write permissions
```bash
chmod a+x cubingchina/public/assets
chmod a+x cubingchina/protected/runtime
```

9、Synchronize the database configuration
```
cubingchina/protected/yiic migrate
```

10、If you need to enable live streaming functionality:
```bash
cubingchina/protected/yiic websocket
```

11、If you need to switch to *dev mode*, set `ENV` of `php` to *dev*.

12、Enjoy.

### Suggestions or Possible mistakes

1、Local server environment include Apache, Mysql, PHP such as [WAMP](http://www.wampserver.com/en/), [MAMP](https://www.mamp.info/en/) or [XAMMP](https://www.apachefriends.org/index.html) are recommanded

2、`wca_data_sync.sh` include `grep`, `lftp`, `sed`commands,install them before run this script

3、The rewrite rules of Apache must be configed to `index.php`
