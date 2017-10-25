<?php

define('APP_PATH', dirname(dirname(__DIR__)));
define('CONFIG_PATH', __DIR__);
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', APP_PATH . '/public');

define('ENV', isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'production');
define('DEV', ENV === 'dev');

if (is_file($autoload = BASE_PATH . '/vendor/autoload.php')) {
	require $autoload;
}
