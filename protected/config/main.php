<?php
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
$config = array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'Cubing China',
	'language' => 'zh_cn',
	// preloading 'log' component
	'preload' => array(
		'log',
	),
	// autoloading model and component classes
	'import' => array(
		'application.models.*',
		'application.models.wca.*',
		'application.forms.*',
		'application.components.*',
		'application.levels.*',
		'application.widgets.*',
		'application.extensions.debugtb.*',
		'application.extensions.mail.*',
	),
	'modules' => array(
		'board' => array(
			'defaultController' => 'competition',
		),
		// uncomment the following to enable the Gii tool
		'gii' => array(
			'class' => 'system.gii.GiiModule',
			'password' => '123',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters' => array(
				'10.*.*.*',
				'127.0.0.1',
				'::1'
			),
		),
	),
	// application components
	'components' => array(
		'user' => array(
			'class' => 'WebUser',
			// enable cookie-based authentication
			'allowAutoLogin' => true,
		),
		'urlManager' => array(
			'urlFormat' => 'path',
			'rules' => array(
				'<page:\d+>' => 'site/index',
				'' => 'site/index',
				'register/<step:\d>' => 'site/register',
				'<action:login|logout|register|forgetPassword|resetPassword|activate|reactivate|banned>' => 'site/<action>',
				'<view:about|contact|links|please-update-your-browser>' => array(
					'site/page',
					'urlSuffix' => '.html'
				),
				'competition/<name:[-A-z0-9]+>/<action:schedule|travel|regulations|competitors|registration>'=>'competition/<action>',
				'competition/<name:[-A-z0-9]+>'=>'competition/detail',
				'board'=>'board/competition/index',
				'<controller:\w+>'=>'<controller>/index',
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
			),
			'appendParams' => false,
			'showScriptName' => false,
		),
		'cache' => array(
			'class' => DEV ? 'CDummyCache' : 'CFileCache',
		),
		'db' => array(
			'connectionString' => 'mysql:host=localhost;dbname=cubingchina' . (DEV ? '_dev' : ''),
			'emulatePrepare' => true,
			'username' => 'cubingchina',
			'password' => '',
			'charset' => 'utf8',
			'enableParamLogging' => YII_DEBUG,
			'enableProfiling' => YII_DEBUG,
			'schemaCachingDuration' => DEV ? 0 : 10800,
		),
		'wcaDb'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'mysql:host=localhost;dbname=wca_' . intval(file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'wcaDb')),
			'emulatePrepare'=>true,
			'username'=>'cubingchina',
			'password'=>'',
			'charset'=>'utf8',
			'schemaCachingDuration'=>'3600',
			'enableParamLogging'=>true,
		),
		'errorHandler' => array(
			// use 'site/error' action to display errors
			'errorAction' => 'site/error',
			'discardOutput' => false,
		),
		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'error, warning',
					'logFile' => 'application.error.log',
					'maxFileSize' => 102400,
				),
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'info, trace, profile',
					'logFile' => 'application.access.log',
					'maxFileSize' => 102400,
				),
				array(
					'class' => 'CDbLogRoute',
					'levels' => 'test',
					'connectionID' => 'db',
					'autoCreateLogTable' => DEV,
					'logTableName' => 'logs',
					// 'logFile' => 'application.test.log',
					// 'maxFileSize' => 102400,
				),
				array( // configuration for the toolbar
					'class' => 'XWebDebugRouter',
					'config' => 'alignLeft, opaque, fixedPos, collapsed, yamlStyle',
					'levels' => 'error, warning, trace, profile, info',
					'allowedIPs'=>array('127.0.0.1', '^10\.\d+\.\d+\.\d+'),
				),
			),
		),
		'clientScript' => array(
			'defaultScriptFilePosition' => CClientScript::POS_END,
			'coreScriptPosition' => CClientScript::POS_END,
			'packages' => array(
				'jquery' => false,
			),
		),
		'mailer' => array(
			'class' => 'Mailer',
			'from' => 'noreply@cubingchina.com',
			'fromname' => '请勿回复DO NOT REPLY',
			'api' => array(
				'user' => '',
				'key' => '',
			),
		),
	),
	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params' => array(
		// this is used in contact page
		'adminEmail' => 'admin@cubingchina.com',
		'author' => 'Baiqiang Dong',
		'description' => 'The Chinese speedcubing website.',
		'keywords' => array(
			'Cubing China',
			'Cubing',
			'CubingChina Website',
			"Rubik's Cube",
			'Speedcubing',

		),
		'weiboSharePic' => 'http://cubingchina.com/f/images/logo2x.png',
		'staticPath' => dirname(dirname(__DIR__)) . '/public/static/',
		'staticUrlPrefix' => 'http://s.cubingchina.com/',
		'jsVer' => '20140826',
		'cssVer' => '20141208',
	),
);

if (is_file(dirname(__FILE__) . '/' . ENV . '.php') && ENV !== 'main') {
	include dirname(__FILE__) . '/' . ENV . '.php';
}

return $config;