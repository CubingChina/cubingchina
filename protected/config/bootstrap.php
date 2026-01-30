<?php

define('APP_PATH', dirname(dirname(__DIR__)));
define('CONFIG_PATH', __DIR__);
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', APP_PATH . '/public');

define('ENV', isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'production');
define('DEV', ENV === 'dev');

// mysql connection params
if (!defined('DB_HOST')) {
	define('DB_HOST', getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost');
}
if (!defined('DB_USER')) {
	define('DB_USER', getenv('DB_USER') !== false ? getenv('DB_USER') : 'cubingchina');
}
if (!defined('DB_PASSWORD')) {
	define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');
}

if (is_file($autoload = BASE_PATH . '/vendor/autoload.php')) {
	require $autoload;
}
