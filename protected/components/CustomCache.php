<?php

if (DEV) {
	class TempCache extends CDummyCache {
		public $hostname;
		public $port;
		public $database;
		public $options;
		public $hashKey = true;
	}
} else {
	class TempCache extends CFileCache {
		public $hostname;
		public $port;
		public $database;
		public $options;
		public $hashKey = true;
	}
}

class CustomCache extends TempCache {

	public function getData($callback, $params = array(), $expire = 604800) {
		if (!is_array($params)) {
			$params = array($params);
		}
		$cacheKey = $this->makeCacheKey(array($callback, $params));
		if (($data = $this->get($cacheKey)) === false) {
			$data = call_user_func_array($callback, $params);
			$this->set($cacheKey, $data, $expire);
		}
		return $data;
	}

	public function makeCacheKey($params) {
		if (is_string($params) || is_numeric($params)) {
			return $params;
		}
		if (is_array($params)) {
			return implode('_', array_map(array($this, 'makeCacheKey'), $params));
		}
		if (is_object($params)) {
			return get_class($params);
		}
		return serialize($params);
	}
}
