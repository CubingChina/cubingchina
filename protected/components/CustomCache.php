<?php
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\ChainCache;

class CustomCache extends CCache {
	public $hostname;
	public $port;
	public $database;
	public $options;

	private $_cache;
	private $_redis;

	public function init() {
		parent::init();
		$this->_redis = $redis = new Redis();
		$redis->connect($this->hostname, $this->port);
		$redis->select($this->database);
		$redisCache = new RedisCache();
		$redisCache->setRedis($redis);
		$fileCache = new FilesystemCache(APP_PATH . '/protected/runtime/cache');
		$chain = [];
		if (!Yii::app() instanceof CConsoleApplication) {
			$chain[] = new ArrayCache();
		}
		if (!DEV || Yii::app() instanceof CConsoleApplication) {
			$chain[] = $redisCache;
		}
		$chainCache = new ChainCache($chain);
		$this->_cache = $chainCache;
	}

	public function getRedis() {
		return $this->_redis;
	}

	public function getValue($key) {
		return $this->_cache->fetch($key);
	}

	public function setValue($key, $value, $expire) {
		return $this->_cache->save($key, $value, $expire);
	}

	public function addValue($key ,$value, $expire) {
		return $this->_cache->save($key, $value, $expire);
	}

	public function deleteValue($key) {
		return $this->_cache->delete($key);
	}

	public function getValues($keys) {
		return $this->_cache->fetchMultiple($key);
	}

	public function flushValues() {
		return $this->_cache->flushAll();
	}

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
		return md5(serialize($params));
	}
}
