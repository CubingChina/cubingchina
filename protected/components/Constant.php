<?php

class Constant {
	const STATUS_OK = 0;

	const STATUS_FORBIDDEN = 403;
	const STATUS_NOT_FOUND = 404;
	const STATUS_INTERNAL_ERROR = 500;

	const STATUS_MISSING_PARAMS = 1000;
	const STATUS_TIMESTAMP_OUT_OF_RANGE = 1001;
	const STATUS_WRONG_SIGNATURE = 1002;
	const STATUS_WRONG_PARAMS = 1003;
	const STATUS_PAYMENT_ALREADY_MADE = 1004;

	const WECHAT_SESSION_KEY = 'wechat_user';
	const CURRENT_URL_KEY = 'current_url';

	public static $ajaxMessage = array(
		self::STATUS_OK=>'OK!',
		self::STATUS_FORBIDDEN=>'Forbidden',
		self::STATUS_NOT_FOUND=>'Not Found',
		self::STATUS_INTERNAL_ERROR=>'Internal Error',

		self::STATUS_MISSING_PARAMS=>'Missing Params',
		self::STATUS_TIMESTAMP_OUT_OF_RANGE=>'Timestamp out of Range',
		self::STATUS_WRONG_SIGNATURE=>'Wrong Signature',
		self::STATUS_WRONG_PARAMS=>'Wrong Params',
		self::STATUS_PAYMENT_ALREADY_MADE=>'Payment has already been made',

	);

	public static function getAjaxMessage($status) {
		return self::$ajaxMessage[$status] ?? '';
	}
}
