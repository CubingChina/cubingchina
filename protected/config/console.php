<?php
define('ENV', isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'production');
define('DEV', ENV === 'dev');
require dirname(dirname(__FILE__)) . '/vendor/autoload.php';
$config = require dirname(__FILE__) . '/main.php';
unset($config['components']['log']['routes'][count($config['components']['log']['routes']) - 1]);
return $config;