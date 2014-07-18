<?php

class Helper {
	public static function formatTime($second) {
		if ($second <= 0) {
			return '';
		} elseif ($second < 60) {
			return $second . '秒';
		} elseif ($second < 3600) {
			return floor($second / 60) . '分' . self::formatTime($second % 60);
		} else {
			return floor($second / 3600) . '小时' . self::formatTime($second % 3600);
		}
	}
}