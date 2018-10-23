<?php

class JsonHelper {
	public static function formatData($data) {
		switch (true) {
			case is_array($data):
				foreach ($data as $key=>$value) {
					$data[$key] = self::formatData($value);
				}
				return $data;
			case is_object($data):
				if (method_exists($data, '__toJson')) {
					return self::formatData($data->__toJson());
				}
				return $data;
			case ctype_digit($data):
				return (int)$data;
			case is_numeric($data):
				return (float)($data);
			default:
				return $data;
		}
	}
}
