部署说明
====

##环境##

1. [Nginx][]/[Apache][]
2. [PHP][] *7.0+*
  * [Redis Extension][]
3. [MySQL][] *5.1+*
4. [Yii Framework][] *1.1.20*
5. [Composer][]
6. [Redis][]

##步骤##

1. clone本项目至cubingchina目录
2. 部署yii至与cubingchina同级的framework目录
3. 数据库创建cubingchina, cubingchina_dev, wca_0, wca_1四个数据库，并赋予空密码用户cubingchina相关权限
4. 导入项目下structure.sql及data.sql到cubingchina和cubingchina_dev
5. 执行/path/to/cubingchina/protected/commands/shell/wca_data_sync.sh
6. 在/path/to/cubingchina/protected目录下执行composer install
7. nginx/apache的document root指向/path/to/cubingchina/public
8. 给/path/to/cubingchina/public/assets及/path/to/cubingchina/protected/runtime可写权限
9. 若需要开启DEV模式，设置php环境变量ENV为dev


[1]: http://nginx.org/
[2]: http://www.apache.org/
[3]: http://php.net/
[4]: https://github.com/phpredis/phpredis
[5]: http://www.mysql.com/
[6]: http://www.yiiframework.com/
[7]: https://getcomposer.org/
[8]: https://redis.io/
