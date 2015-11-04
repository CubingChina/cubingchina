<?php

// change the following paths if necessary
define('APP_PATH', dirname(dirname(__FILE__)));
$yii = APP_PATH . '/../framework/yii.php';
$config = APP_PATH . '/protected/config/main.php';

define('ENV', isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'production');
define('DEV', ENV === 'dev');
// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG', DEV);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

require $yii;
if (is_file($autoload = APP_PATH . '/protected/vendor/autoload.php')) {
	require $autoload;
}
Yii::createWebApplication($config)->run();
