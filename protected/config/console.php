<?php

include __DIR__ . '/bootstrap.php';
$config = require CONFIG_PATH . '//main.php';
unset($config['components']['log']['routes'][count($config['components']['log']['routes']) - 1]);
$config['commandMap'] = [
	'migrate'=>[
		'class'=>'system.cli.commands.MigrateCommand',
		'migrationTable'=>'migration',
		'interactive'=>false,
	],
];
return $config;
