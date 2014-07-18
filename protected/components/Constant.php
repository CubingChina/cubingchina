<?php

class Constant {
	const AJAX_OK = 0;

	public static $ajaxMessage = array(
		self::AJAX_OK=>'OK!',
	);

	public static function getAjaxMessage($status) {
		return isset(self::$ajaxMessage[$status]) ? self::$ajaxMessage[$status] : '';
	}
}