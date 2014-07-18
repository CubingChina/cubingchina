<?php

// change the following paths if necessary
define('APP_PATH', dirname(dirname(__FILE__)));
$yii = APP_PATH . '/../framework/yii.php';
$config = APP_PATH . '/protected/config/main.php';

define('ENV', isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'production');
define('DEV', ENV === 'dev');
define('SUPER_DEV', isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === '127.0.0.1');
// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG', DEV);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);

require $yii;
require APP_PATH . '/protected/vendor/autoload.php';
Yii::createWebApplication($config)->run();
