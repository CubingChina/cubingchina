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

### Steps

1. Clone this repo to `/path/to/cubingchina`.
2. Put `framework` directory of `Yii` to `/path/to/cubingchina/../framework`.
3. Create four databases, each named `cubingchina`, `cubingchina_dev`, `wca_0` and `wca_1`. Then grant privileges on these to `cubingchina` with empty password.
4. Import `structure.sql` and `data.sql` into `cubingchina` and `cubingchina_dev`.
5. Run `/path/to/cubingchina/protected/commands/shell/wca_data_sync.sh`.
6. `cd /path/to/cubingchina/protected && composer install`.
7. Set *document root* of `Nginx`/`Apache` to `/path/to/cubingchina/public`.
8. Make sure `/path/to/cubingchina/public/assets` and `/path/to/cubingchina/protected/runtime` are writable.
9. If you need to switch to *dev mode*, set `ENV` of `php` to *dev*.
10. Enjoy.


 [Nginx]: http://nginx.org
 [Apache]: http://www.apache.org
 [PHP]: http://php.net
 [Redis Extension]: https://github.com/phpredis/phpredis
 [MySQL]: http://www.mysql.com
 [Yii Framework]: http://www.yiiframework.com
 [Composer]: https://getcomposer.org
 [Redis]: https://redis.io/

