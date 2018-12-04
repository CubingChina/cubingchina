Cubingchina
===========

The official website of Cubing China https://cubingchina.com.

Installation
------------

### Environments

1. [Nginx][]/[Apache][]
2. [PHP][] *7.0+*
  * [Redis Extension][]
3. [MySQL][] *5.1+*
4. [Yii Framework][] *1.1.20*
5. [Composer][]
6. [Redis][]
7. [Nodejs][]

### Steps

1. Clone this repo to `/path/to/cubingchina`.
2. Put `framework` directory of `Yii` to `/path/to/cubingchina/../framework`.
3. Create four databases, each named `cubingchina`, `cubingchina_dev`, `wca_0` and `wca_1`. Then grant privileges on these to `cubingchina` with empty password.
4. `cd /path/to/cubingchina/protected/config` and Create one file named `wcaDb` then Set the content as `0` or `1`.
5. Import `structure.sql` and `data.sql` into `cubingchina` and `cubingchina_dev`.
6. Run `/path/to/cubingchina/protected/commands/shell/wca_data_sync.sh`.
7. `cd /path/to/cubingchina/protected && composer install`.
8. `cd /path/to/cubingchina/public/f && npm i && npm run build`.
9. Set *document root* of `Nginx`/`Apache` to `/path/to/cubingchina/public`.
10. Make sure `/path/to/cubingchina/public/assets` and `/path/to/cubingchina/protected/runtime` are writable.
11. If you need to switch to *dev mode*, set `ENV` of `php` to *dev*.
12. Enjoy.

### Suggestions or Possible mistakes

1. Local server environment include Apache, Mysql, PHP such as [WAMP](http://www.wampserver.com/en/), [MAMP](https://www.mamp.info/en/) or [XAMMP](https://www.apachefriends.org/index.html) are recommanded
2. `wca_data_sync.sh` include `grep`, `lftp`, `sed`commands,install them before run this script
3. The rewrite rules of Apache must be configed to `index.php`



 [Nginx]: http://nginx.org
 [Apache]: http://www.apache.org
 [PHP]: http://php.net
 [Redis Extension]: https://github.com/phpredis/phpredis
 [MySQL]: http://www.mysql.com
 [Yii Framework]: http://www.yiiframework.com
 [Composer]: https://getcomposer.org
 [Redis]: https://redis.io/
 [Nodejs]: https://nodejs.org

