<?php
unset($config['components']['errorHandler']['errorAction']);
$config['modules']['gii'] = array(
	'class'=>'system.gii.GiiModule',
	'password'=>'123',
	// If removed, Gii defaults to localhost only. Edit carefully to taste.
	'ipFilters'=>array(
		'10.*.*.*',
		'127.0.0.1',
		'::1'
	),
);
$config['components']['urlManager']['rules'] = array_slice($config['components']['urlManager']['rules'], 2, null, true);
$config['params']['payments']['balipay'] = [
	'gateway'=>'https://openapi.alipaydev.com/gateway.do?charset=UTF-8',
	'app_id'=>'2016080500173765',
	'private_key_path'=>'/data/security/cubingchina.test.private.pem',
	'alipay_public_key_path'=>'/data/security/alipay.test.public.pem',
	'img'=>'/f/images/pay/alipay.png',
];
