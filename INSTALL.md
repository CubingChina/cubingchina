部署说明
====

##环境

1. [Nginx][]/[Apache][]
2. [PHP][] *7.0+*
  * [Redis Extension][]
3. [MySQL][] *5.1+*
4. [Yii Framework][] *1.1.20*
5. [Composer][]
6. [Redis][]
7. [Nodejs][]

##步骤

1. clone本项目至cubingchina目录
2. 部署yii至与cubingchina同级的framework目录
3. 数据库创建cubingchina, cubingchina_dev, wca_0, wca_1四个数据库，并赋予空密码用户cubingchina相关权限
4. 在/path/to/cubingchina/protected/config 新建文件wcaDb,文件内容设置成0或者1
5. 导入项目下structure.sql及data.sql到cubingchina和cubingchina_dev
6. 执行/path/to/cubingchina/protected/commands/shell/wca_data_sync.sh
7. 在/path/to/cubingchina/protected目录下执行composer install
8. 在/path/to/cubingchina/public/f 目录下执行npm i 和 npm run build
9. nginx/apache的document root指向/path/to/cubingchina/public
10. 给/path/to/cubingchina/public/assets及/path/to/cubingchina/protected/runtime可写权限
11. 若需要开启DEV模式，设置php环境变量ENV为dev
12. 运行


##建议和可能出现的问题
1. 推荐使用例如[WAMP](http://www.wampserver.com/en/), [MAMP](https://www.mamp.info/en/) 或者 [XAMMP](https://www.apachefriends.org/index.html)这种已经包含Apache, Mysql, PHP的本地服务器开发环境
2. wca_data_sync.sh 脚本中包含例如grep, lftp, sed等指令，先安装在执行脚本
3. Apache 的 rewrite规则需要配置，定向到index.php


[Nginx]: http://nginx.org/
[Apache]: http://www.apache.org/
[PHP]: http://php.net/
[Redis Extension]: https://github.com/phpredis/phpredis
[MySQL]: http://www.mysql.com/
[Yii Framework]: http://www.yiiframework.com/
[Composer]: https://getcomposer.org/
[Redis]: https://redis.io/
[Nodejs]: https://nodejs.org

