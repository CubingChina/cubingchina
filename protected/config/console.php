<?php
define('APP_PATH', dirname(dirname(__DIR__)));
define('ENV', isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'production');
define('DEV', ENV === 'dev');
if (is_file($autoload = APP_PATH . '/protected/vendor/autoload.php')) {
	require $autoload;
}
$config = require APP_PATH . '/protected/config/main.php';
unset($config['components']['log']['routes'][count($config['components']['log']['routes']) - 1]);
$config['commandMap'] = array(
	'migrate'=>array(
		'class'=>'system.cli.commands.MigrateCommand',
		'migrationTable'=>'migration',
		'interactive'=>false,
	),
);
return $config;