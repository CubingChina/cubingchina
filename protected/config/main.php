<?php

Yii::setPathOfAlias('application', BASE_PATH);
Yii::import('application.components.*');

$wcaDb = file_exists(CONFIG_PATH . '/wcaDb') ? intval(file_get_contents(CONFIG_PATH . '/wcaDb')) : 0;
$config = [
	'basePath'=>BASE_PATH,
	'name'=>'Cubing China',
	'language'=>'zh_cn',
	// preloading 'log' component
	'preload'=>[
		'log',
	],
	// autoloading model and component classes
	'import'=>[
		'application.models.*',
		'application.models.wca.*',
		'application.forms.*',
		'application.helpers.*',
		'application.widgets.*',
		'application.statistics.*',
		'application.summary.*',
		'application.extensions.debugtb.*',
		'application.extensions.mail.*',
	],
	'modules'=>[
		'board'=>[
			'defaultController'=>'competition',
		],
		'api',
	],
	// application components
	'components'=>[
		'user'=>[
			'class'=>'WebUser',
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		],
		'session'=>[
			'class'=>'SymfonyHttpSession',
			'options'=>[
				'lock_mode'=>Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler::LOCK_NONE,
			],
			'sessionName'=>'CUBINGCHINA_SID',
			'timeout'=>86400 * 7,
		],
		'urlManager'=>[
			'urlFormat'=>'path',
			'rules'=>[
				// due to ssl cert
				// 'http://ac2016.cubingchina.com/'=>[
				// 	'competition/detail',
				// 	'defaultParams'=>[
				// 		'name'=>'Asian-Championship-2016',
				// 	],
				// ],
				// 'http://ac2016.cubingchina.com/<action:schedule|travel|regulations|competitors|registration|scan>'=>[
				// 	'competition/<action>',
				// 	'defaultParams'=>[
				// 		'name'=>'Asian-Championship-2016',
				// 	],
				// ],
				'<page:\d+>'=>'site/index',
				'faq/<category_id:\d+>'=>[
					'faq/index',
					'urlSuffix'=>'.html'
				],
				''=>'site/index',
				'register/<step:\d>'=>'site/register',
				'<action:login|logout|register|forgetPassword|resetPassword|activate|reactivate|banned>'=>'site/<action>',
				'<view:about|contact|links|disclaimer|please-update-your-browser>'=>[
					'site/page',
					'urlSuffix'=>'.html'
				],
				'competition/<action:signin>'=>'competition/<action>',
				'<controller:competition|post>/<alias:[-A-z0-9]+>'=>'<controller>/detail',
				'competition/<alias:[-A-z0-9]+>/<action:\w+>'=>'competition/<action>',
				'live/<alias:[-A-z0-9]+>/statistics/<type:[-A-z0-9]+>'=>'live/statistics',
				'live/<alias:[-A-z0-9]+>'=>'live/live',
				'live/<alias:[-A-z0-9]+>/<action:\w+>'=>'live/<action>',
				'results/statistics/<page:[0-9]+>'=>'results/statistics',
				'results/statistics/<name:[-A-z0-9]+>'=>'results/statistics',
				'results/person/<id:(1982|20\d\d)[A-z]{4}\d\d>'=>'results/p',
				'results/battle/<ids:(1982|20\d\d)[A-z]{4}\d\d(-(1982|20\d\d)[A-z]{4}\d\d){0,3}>'=>'results/battle',
				'results/competition/<id:\w+\d{4}>/<type:\w+>'=>'results/c',
				'results/competition/<id:\w+\d{4}>'=>'results/c',
				'summary/<year:\d{4}>/<id:(1982|20\d\d)[A-z]{4}\d\d>'=>'summary/person',
				'summary/<year:\d{4}>'=>'summary/index',
				'pay/<action:notify|frontNotify|refundNotify>/<channel:\w+>'=>'pay/<action>',
				'pay/redirect/<channel:\w+>/<order_no:[0-9\-]+>'=>'pay/redirect',
				'qrCode/<action:\w+>/<code:[\w-]+>'=>'qrCode/<action>',
				'board'=>'board/competition/index',
				'api/v0/<controller:\w+>'=>'api/<controller>/index',
				'api/v0/<controller:\w+>/<action:\w+>'=>'api/<controller>/<action>',
				'api/v0/<controller:competition|post>/<alias:[-A-z0-9]+>'=>'api/<controller>/detail',
				'api/v0/competition/<alias:[-A-z0-9]+>/<action:\w+>'=>'api/competition/<action>',
				'<controller:\w+>'=>'<controller>/index',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			],
			'appendParams'=>false,
			'showScriptName'=>false,
		],
		'cache'=>[
			'class'=>'CustomCache',
			// 'hashKey'=>false,
			'hostname'=>'127.0.0.1',
			'port'=>6379,
			'database'=>1,
		],
		'db'=>[
			'connectionString'=>'mysql:host=localhost;dbname=cubingchina' . (DEV ? '_dev' : ''),
			'pdoClass'=>'QueryCheckPdo',
			'emulatePrepare'=>true,
			'username'=>'cubingchina',
			'password'=>'',
			'charset'=>'utf8',
			'enableParamLogging'=>YII_DEBUG,
			'enableProfiling'=>YII_DEBUG,
			'schemaCachingDuration'=>DEV ? 0 : 10800,
		],
		'wcaDb'=>[
			'class'=>'system.db.CDbConnection',
			'pdoClass'=>'QueryCheckPdo',
			'connectionString'=>'mysql:host=localhost;dbname=wca_' . $wcaDb,
			'emulatePrepare'=>true,
			'username'=>'cubingchina',
			'password'=>'',
			'charset'=>'utf8',
			'schemaCachingDuration'=>'3600',
			'enableParamLogging'=>true,
		],
		'errorHandler'=>[
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
			'discardOutput'=>false,
		],
		'log'=>[
			'class'=>'CLogRouter',
			'routes'=>[
				[
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
					'logFile'=>'application.error.log',
					'maxFileSize'=>102400,
				],
				[
					'class'=>'CFileLogRoute',
					'levels'=>'info, trace, profile',
					'logFile'=>'application.access.log',
					'maxFileSize'=>102400,
				],
				[
					'class'=>'CFileLogRoute',
					'levels'=>'pay',
					'logFile'=>'application.pay.log',
					'maxFileSize'=>102400,
				],
				[
					'class'=>'CFileLogRoute',
					'levels'=>'ws',
					'logFile'=>'application.ws.log',
					'maxFileSize'=>102400,
				],
				[
					'class'=>'CFileLogRoute',
					'levels'=>'debug',
					'logFile'=>'application.debug.log',
					'maxFileSize'=>102400,
				],
				[
					'class'=>'CFileLogRoute',
					'levels'=>'git',
					'logFile'=>'application.git.log',
					'maxFileSize'=>102400,
				],
				[
					'class'=>'CDbLogRoute',
					'levels'=>'test',
					'connectionID'=>'db',
					'autoCreateLogTable'=>DEV,
					'logTableName'=>'logs',
				],
				[ // configuration for the toolbar
					'class'=>'XWebDebugRouter',
					'config'=>'alignLeft, opaque, fixedPos, collapsed, yamlStyle',
					'levels'=>'error, warning, trace, profile, info',
					'allowedIPs'=>['127.0.0.1', '^10\.\d+\.\d+\.\d+'],
				],
			],
		],
		'clientScript'=>[
			'defaultScriptFilePosition'=>CClientScript::POS_END,
			'coreScriptPosition'=>CClientScript::POS_END,
			'packages'=>[
				'jquery'=>[
					'baseUrl'=>'',
					'js'=>['js/jquery.min.js'],
				],
				'board'=>[
					'baseUrl'=>'b',
					'js'=>[
						'js/plugins/bootstrap/bootstrap.min.js',
						'js/plugins/hisrc/hisrc.js',
						'js/flex.js',
						'js/main.js?v=20170523',
					],
					'depends'=>['jquery', 'event-icon'],
				],
				'event-icon'=>[
					'baseUrl'=>'f',
					'css'=>[
						'plugins/event-icon/style.css',
					],
				],
				'main'=>[
					'baseUrl'=>'',
					// 'depends'=>['jquery'],
				],
				'datepicker'=>[
					'baseUrl'=>'f/plugins/bootstrap-datepicker',
					'css'=>[
						'css/datepicker.css',
					],
					'js'=>[
						'js/bootstrap-datepicker.js',
					],
				],
				'datetimepicker'=>[
					'baseUrl'=>'b',
					'css'=>[
						'css/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css',
					],
					'js'=>[
						'js/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js',
					],
				],
				'tokenfield'=>[
					'baseUrl'=>'b',
					'css'=>[
						'css/plugins/bootstrap-tokenfield/tokenfield-typeahead.min.css',
						'css/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.css',
					],
					'js'=>[
						'js/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.js',
						'js/plugins/bootstrap-tokenfield/typeahead.bundle.min.js',
					],
				],
				'tagsinput'=>[
					'baseUrl'=>'b',
					'css'=>[
						'css/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css',
						'css/plugins/bootstrap-tagsinput/bootstrap-tagsinput-typeahead.css',
					],
					'js'=>[
						'js/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js',
					],
					'depends'=>['typeahead'],
				],
				'typeahead'=>[
					'baseUrl'=>'b',
					'js'=>[
						'js/plugins/bootstrap-typeahead/bootstrap-typeahead.min.js',
					],
				],
				'switch'=>[
					'baseUrl'=>'b',
					'css'=>[
						'css/plugins/bootstrap-switch/bootstrap-switch.min.css',
					],
					'js'=>[
						'js/plugins/bootstrap-switch/bootstrap-switch.min.js',
					],
				],
				'morris'=>[
					'baseUrl'=>'b',
					'css'=>[
						'css/plugins/morris/morris.css',
					],
					'js'=>[
						'js/plugins/morris/raphael-2.1.0.min.js',
						'js/plugins/morris/morris.js',
					],
				],
				'pinyin'=>[
					'baseUrl'=>'f',
					'js'=>[
						'js/pinyin.min.js',
					],
				],
			],
		],
		'mailer'=>[
			'class'=>'Mailer',
			'from'=>'noreply@cubingchina.com',
			'fromname'=>'请勿回复DO NOT REPLY',
			'api'=>[
				'user'=>'cubingchina',
				'key'=>Env::get('MAILER_KEY'),
				'baseUrl'=>'http://api.sendcloud.net/apiv2/',
			],
		],
	],
	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>[
		// this is used in contact page
		'adminEmail'=>'admin@cubingchina.com',
		'baseUrl'=>'https://cubingchina.com',
		'languages'=>['en', 'zh_cn', 'zh_tw'],
		'author'=>'Baiqiang Dong',
		'description'=>'The Chinese speedcubing website.',
		'keywords'=>[
			'Cubing China',
			'Cubing',
			'CubingChina Website',
			"Rubik's Cube",
			'Speedcubing',
		],
		'weiboSharePic'=>'https://cubingchina.com/f/images/logo2x.png',
		'staticPath'=>PUBLIC_PATH . '/static/',
		'staticUrlPrefix'=>DEV ? '/static/' : 'https://i.cubingchina.com/',
		'avatar'=>[
			'size'=>2097152,
			'height'=>1200,
			'width'=>1200,
		],
		'payments'=>[
			'balipay'=>[
				'gateway'=>'https://openapi.alipay.com/gateway.do?charset=UTF-8',
				'app_id'=>'2016062701556602',
				'private_key_path'=>Env::get('PAYMENT_BALIPAY_PRIVATE_KEY'),
				'alipay_public_key_path'=>Env::get('PAYMENT_BALIPAY_PUBLIC_KEY'),
				'img'=>'/f/images/pay/alipay.png',
			],
			'wechat'=>[
				'img'=>'/f/images/pay/wechat.png',
				'app_id'=>Env::get('WECHAT_APP_ID'),
				'mch_id'=>Env::get('PAYMENT_WECHAT_MCH_ID'),
				'key'=>Env::get('PAYMENT_WECHAT_KEY'),
				'cert_path'=>Env::get('PAYMENT_WECHAT_CERT_PATH'),
				'key_path'=>Env::get('PAYMENT_WECHAT_KEY_PATH'),
				'sandBox'=>DEV,
			],
		],
		'regulations'=>[
			'common'=>[
				'All competitors must be familiar with the WCA regulations. Regulations can be found at <a href="https://www.worldcubeassociation.org/regulations/" target="_blank">https://www.worldcubeassociation.org/regulations/</a>.',
				'Competitors are required to carry their competitor ID with them.',
				'Please inform us by email before the competition if you will not be able to participate so we can cancel your registration.',
				'Please pay attention to the schedule and be on time for your events. Competitors showing up late to events may be disqualified.',
				'"Time limit" means, if you exceeds the time limit, your current attempt will be stopped and recorded as DNF. "Cut-off" means, you are allowed to finish all five attempts if at least one of your first two attempts fits in the cut-off, otherwise the remaining three attempts will be cancelled. (The first attempt has to be below the cut-off for 6x6, 7x7 and 3x3 with feet.)',
			],
			'common_zh'=>[
				'请所有参赛选手必须熟知WCA规则，详见<a href="https://www.worldcubeassociation.org/regulations/" target="_blank">https://www.worldcubeassociation.org/regulations/</a>',
				'请妥善保管参赛证，如若丢失，将失去比赛资格',
				'若有故不能前来，请于比赛前发邮件联系主办方告知，否则将被主办方记录在案，影响以后的参赛资格',
				'所有项目不得晚于指定时间检录，否则视为放弃该项目比赛资格',
				'还原时限：指选手的单次还原超过该时限，WCA代表和主裁判有权利停止当次比赛并记DNF。及格线：指选手五次还原的前二次须至少有一次进入及格线，否则无后三次还原机会（对于六阶、七阶和脚拧，第一次还原为及格线）。',
			],
			'special'=>[
				'333ft'=>'Competitors participating in 3x3 with feet must provide their own Speedstack timer and mat.',
				'bf'=>'Competitors participating in all blindfolded events must provide their own blindfold.',
				'lbf'=>'For 4x4 blindfolded, 5x5 blindfolded and 3x3 multiple blindfolded events, cubes must be provided for scrambling when requested by the organizers.',
				'bbf'=>'For 4x4 blindfolded and 5x5 blindfolded, “cumulative time limit” means that the total solving time of N attempts (N≤3) mustn’t exceeds the given time limit. If your total time exceeds the time limit, your current attempt will be stopped and recorded as DNF and any remaining attempts will be recorded as DNS. Attempt time for incomplete solves will be still recorded and added to the cumulative time.',
				'clock'=>"For the Clock event, clocks will be disqualified if the four pins can't stay upright, such that the pins fall down if the clock is held horizontal or the pins fail to control the rotation of gears.",
			],
			'special_zh'=>[
				'333ft'=>'参加脚拧项目自备垫子及Speedstacks计时器，否则将被取消该项目参赛资格',
				'bf'=>'参加盲拧项目自备眼罩，否则将被取消该项目参赛资格',
				'lbf'=>'请参加高盲、多盲项目的选手于指定时间内上交比赛用魔方，否则视为放弃该项目本次比赛资格',
				'bbf'=>'高盲累计时限：在一轮中，N次(N≤3)还原的累计时间不能超过规定的时限。当选手累计时间到达时限时，裁判可以直接叫停选手的复原，本次复原将被记为DNF，之后复原将被记为DNS。对于成绩为DNF的复原，裁判也将记录所用时间并计入累计时间内',
				'clock'=>'对于磨损过于严重及低质量魔表，若控制齿轮转动的针不能维持凸起的状态，即重力下针会松动下落或者无法控制其齿轮转动。主办方可能禁止其在比赛中使用',
			],
		],
		'summaryDaysToYearEnd'=>357,
		'exchangeRate'=>[
			'usd'=>7.0,
		],
	],
];

if (is_file(CONFIG_PATH . '/' . ENV . '.php') && ENV !== 'main') {
	include CONFIG_PATH . '/' . ENV . '.php';
}

return $config;
