<?php

class Env {
	const PREFIX = 'CUBINGCHINA_';

	private static $envs;

	public static function get($name) {
		$key = self::PREFIX . $name;
		$envs = self::getEnvs();
		return $_SERVER[$key] ?? $envs[$key] ?? '';
	}

	public static function getEnvs() {
		if (self::$envs === null) {
			self::$envs = [];
			$envFile = CONFIG_PATH . '/env.php';
			if (file_exists($envFile)) {
				self::$envs = require $envFile;
			}
		}
		return self::$envs;
	}
}
