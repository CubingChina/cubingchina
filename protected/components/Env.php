<?php

class Env {
	const PREFIX = 'CUBINGCHINA_';

	public static function get($name) {
		$key = self::PREFIX . $name;
		return isset($_SERVER[$key]) ? $_SERVER[$key] : '';
	}
}
