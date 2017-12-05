<?php

class Constant {
	const AJAX_OK = 0;

	const WECHAT_SESSION_KEY = 'wechat_user';
	const CURRENT_URL_KEY = 'current_url';

	public static $ajaxMessage = array(
		self::AJAX_OK=>'OK!',
	);

	public static function getAjaxMessage($status) {
		return isset(self::$ajaxMessage[$status]) ? self::$ajaxMessage[$status] : '';
	}
}
