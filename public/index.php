<?php

include __DIR__ . '/../protected/config/bootstrap.php';
$yii = APP_PATH . '/../framework/yii.php';
$config = CONFIG_PATH . '/main.php';

define('YII_DEBUG', DEV);
define('YII_TRACE_LEVEL', 3);
require $yii;
Yii::createWebApplication($config)->run();
