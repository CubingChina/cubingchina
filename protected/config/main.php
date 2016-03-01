<?php

Yii::setPathOfAlias('application', APP_PATH . '/protected');
Yii::import('application.components.*');

$config = array(
	'basePath'=>dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name'=>'Cubing China',
	'language'=>'zh_cn',
	// preloading 'log' component
	'preload'=>array(
		'log',
	),
	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.models.wca.*',
		'application.forms.*',
		'application.levels.*',
		'application.widgets.*',
		'application.extensions.debugtb.*',
		'application.extensions.mail.*',
	),
	'modules'=>array(
		'board'=>array(
			'defaultController'=>'competition',
		),
		// uncomment the following to enable the Gii tool
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'123',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array(
				'10.*.*.*',
				'127.0.0.1',
				'::1'
			),
		),
	),
	// application components
	'components'=>array(
		'user'=>array(
			'class'=>'WebUser',
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		'session'=>array(
			'class'=>'SymfonyHttpSession',
			// 'connectionID'=>'db',
			// 'sessionTableName'=>'session',
			// 'autoCreateSessionTable'=>false,
			'cookieParams'=>DEV ? array() : array(
				'domain'=>'.cubingchina.com',
			),
			'sessionName'=>'CUBINGCHINA_SID',
		),
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'http://ac2016.cubingchina.com/'=>array(
					'competition/detail',
					'defaultParams'=>array(
						'name'=>'Asian-Championship-2016',
					),
				),
				'http://ac2016.cubingchina.com/<action:schedule|travel|regulations|competitors|registration>'=>array(
					'competition/<action>',
					'defaultParams'=>array(
						'name'=>'Asian-Championship-2016',
					),
				),
				'<page:\d+>'=>'site/index',
				'faq/<category_id:\d+>'=>array(
					'faq/index',
					'urlSuffix'=>'.html'
				),
				''=>'site/index',
				'register/<step:\d>'=>'site/register',
				'<action:login|logout|register|forgetPassword|resetPassword|activate|reactivate|banned>'=>'site/<action>',
				'<view:about|contact|links|please-update-your-browser>'=>array(
					'site/page',
					'urlSuffix'=>'.html'
				),
				'competition/<name:[-A-z0-9]+>/<action:schedule|travel|regulations|competitors|registration>'=>'competition/<action>',
				'competition/<name:[-A-z0-9]+>'=>'competition/detail',
				'results/statistics/<name:[-A-z0-9]+>'=>'results/statistics',
				'results/person/<id:(1982|20\d\d)[A-z]{4}\d\d>'=>'results/p',
				'results/battle/<ids:(1982|20\d\d)[A-z]{4}\d\d(-(1982|20\d\d)[A-z]{4}\d\d){0,3}>'=>'results/battle',
				'results/competition/<id:\w+\d{4}>'=>'results/c',
				'pay/<action:notify|frontNotify>/<channel:nowPay|alipay>'=>'pay/<action>',
				'board'=>'board/competition/index',
				'<controller:\w+>'=>'<controller>/index',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
			'appendParams'=>false,
			'showScriptName'=>false,
			// 'baseUrl'=>DEV ? null : 'http://cubingchina.com',
		),
		'cache'=>array(
			'class'=>'CustomCache',
		),
		'db'=>array(
			'connectionString'=>'mysql:host=localhost;dbname=cubingchina' . (DEV ? '_dev' : ''),
			'emulatePrepare'=>true,
			'username'=>'cubingchina',
			'password'=>'',
			'charset'=>'utf8',
			'enableParamLogging'=>YII_DEBUG,
			'enableProfiling'=>YII_DEBUG,
			'schemaCachingDuration'=>DEV ? 0 : 10800,
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
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
			'discardOutput'=>false,
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
					'logFile'=>'application.error.log',
					'maxFileSize'=>102400,
				),
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'info, trace, profile',
					'logFile'=>'application.access.log',
					'maxFileSize'=>102400,
				),
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'pay',
					'logFile'=>'application.pay.log',
					'maxFileSize'=>102400,
				),
				array(
					'class'=>'CDbLogRoute',
					'levels'=>'test',
					'connectionID'=>'db',
					'autoCreateLogTable'=>DEV,
					'logTableName'=>'logs',
				),
				array( // configuration for the toolbar
					'class'=>'XWebDebugRouter',
					'config'=>'alignLeft, opaque, fixedPos, collapsed, yamlStyle',
					'levels'=>'error, warning, trace, profile, info',
					'allowedIPs'=>array('127.0.0.1', '^10\.\d+\.\d+\.\d+'),
				),
			),
		),
		'clientScript'=>array(
			'defaultScriptFilePosition'=>CClientScript::POS_END,
			'coreScriptPosition'=>CClientScript::POS_END,
			'packages'=>array(
				'jquery'=>array(
					'baseUrl'=>'',
					'js'=>array('js/jquery.min.js'),
				),
				'board'=>array(
					'baseUrl'=>'b',
					'js'=>array(
						'js/plugins/bootstrap/bootstrap.min.js',
						'js/plugins/hisrc/hisrc.js',
						'js/flex.js',
						'js/main.js?v=20150729',
					),
					'depends'=>array('jquery'),
				),
				'main'=>array(
					'baseUrl'=>'f',
					'depends'=>array('jquery'),
				),
				'datepicker'=>array(
					'baseUrl'=>'f/plugins/bootstrap-datepicker',
					'css'=>array(
						'css/datepicker.css',
					),
					'js'=>array(
						'js/bootstrap-datepicker.js',
					),
				),
				'datetimepicker'=>array(
					'baseUrl'=>'b',
					'css'=>array(
						'css/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css',
					),
					'js'=>array(
						'js/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js',
					),
				),
				'tokenfield'=>array(
					'baseUrl'=>'b',
					'css'=>array(
						'css/plugins/bootstrap-tokenfield/tokenfield-typeahead.min.css',
						'css/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.css',
					),
					'js'=>array(
						'js/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.js',
						'js/plugins/bootstrap-tokenfield/typeahead.bundle.min.js',
					),
				),
				'morris'=>array(
					'baseUrl'=>'b',
					'css'=>array(
						'css/plugins/morris/morris.css',
					),
					'js'=>array(
						'js/plugins/morris/raphael-2.1.0.min.js',
						'js/plugins/morris/morris.js',
					),
				),
				'pinyin'=>array(
					'baseUrl'=>'f',
					'js'=>array(
						'js/pinyin.min.js',
					),
				),
				'leaflet'=>array(
					'baseUrl'=>'f',
					'css'=>array(
						'leaflet/leaflet.css',
						'leaflet/plugins/MarkerCluster/MarkerCluster.css',
						'leaflet/plugins/MarkerCluster/MarkerCluster.Default.css',
					),
					'js'=>array(
						'leaflet/leaflet.js',
						'leaflet/plugins/MarkerCluster/leaflet.markercluster.js',
					),
				),
			),
		),
		'mailer'=>array(
			'class'=>'Mailer',
			'from'=>'noreply@cubingchina.com',
			'fromname'=>'请勿回复DO NOT REPLY',
			'api'=>array(
				'user'=>'cubingchina',
				'key'=>Env::get('MAILER_KEY'),
			),
		),
	),
	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'admin@cubingchina.com',
		'languages'=>array('en', 'zh_cn', 'zh_tw'),
		'author'=>'Baiqiang Dong',
		'description'=>'The Chinese speedcubing website.',
		'keywords'=>array(
			'Cubing China',
			'Cubing',
			'CubingChina Website',
			"Rubik's Cube",
			'Speedcubing',
		),
		'weiboSharePic'=>'http://cubingchina.com/f/images/logo2x.png',
		'staticPath'=>dirname(dirname(__DIR__)) . '/public/static/',
		'staticUrlPrefix'=>0 ? '/static/' : 'http://s.cubingchina.com/',
		'jsVer'=>'20151230',
		'cssVer'=>'20160229',
		'avatar'=>array(
			'size'=>2097152,
			'height'=>1200,
			'width'=>1200,
		),
		'nowPay'=>array(
			'baseUrl'=>'http://api.ipaynow.cn/',
			'types'=>array(
				'pc'=>array(
					'appId'=>'1436844603321385',
					'securityKey'=>Env::get('PAYMENT_NOWPAY_PC'),
					'deviceType'=>'02',
				),
				'mobile'=>array(
					'appId'=>'1436844653265386',
					'securityKey'=>Env::get('PAYMENT_NOWPAY_MOBILE'),
					'deviceType'=>'06',
				),
			),
		),
		'alipay'=>array(
			'gateway'=>'https://mapi.alipay.com/gateway.do',
			'partner'=>'2088002487607846',
			'seller_email'=>'qiyuuu@gmail.com',
			'key'=>Env::get('PAYMENT_ALIPAY'),
		),
	),
);

if (is_file(dirname(__FILE__) . '/' . ENV . '.php') && ENV !== 'main') {
	include dirname(__FILE__) . '/' . ENV . '.php';
}

return $config;
