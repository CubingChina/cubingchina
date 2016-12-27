<?php

class Summary {
	public static $years = [2016];
	public static function getInstance($year) {
		if (!in_array($year, self::$years)) {
			return null;
		}
		$class = 'Summary' . $year;
		return new $class();
	}
}