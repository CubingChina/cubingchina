<?php

/**
 * Wrap PDO class. Do ping method before every method being called.
 */
class QueryCheckPdo extends PDO {
	public $dsn;
	public $username;
	public $password;
	public $options = array();
	private $_lastActiveTime;
	private $_pdo;

	const TIMEOUT = 10;

	public function __construct($dsn, $username = '', $password = '', $options = array()) {
		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
		$this->options = $options;
		$this->_pdo = new PDO($dsn, $username, $password, $options);
		$this->lastActiveTime = time();
	}

	public function beginTransaction() {
		$this->ping();
		return $this->_pdo->beginTransaction();
	}

	public function commit() {
		$this->ping();
		return $this->_pdo->commit();
	}

	public function errorCode() {
		$this->ping();
		return $this->_pdo->errorCode();
	}

	public function errorInfo() {
		$this->ping();
		return $this->_pdo->errorInfo();
	}

	public function exec($statement) {
		$this->ping();
		return $this->_pdo->exec($statement);
	}

	public function inTransaction() {
		$this->ping();
		return $this->_pdo->inTransaction();
	}

	public function lastInsertId($name = null) {
		$this->ping();
		return $this->_pdo->lastInsertId($name);
	}

	public function prepare($statement, $driverOptions = array()) {
		$this->ping();
		return $this->_pdo->prepare($statement, $driverOptions);
	}

	public function query($statement) {
		$this->ping();
		return $this->_pdo->query($statement);
	}

	public function quote($string, $parameterType = PDO::PARAM_STR) {
		$this->ping();
		return $this->_pdo->quote($string, $parameterType);
	}

	public function rollBack() {
		$this->ping();
		return $this->_pdo->rollBack();
	}

	public function getAttribute($attribute) {
		$this->ping();
		return $this->_pdo->getAttribute($attribute);
	}

	public function setAttribute($attribute, $value) {
		$this->ping();
		return $this->_pdo->setAttribute($attribute, $value);
	}

	public function ping() {
		$now = time();
		if ($now - $this->lastActiveTime > self::TIMEOUT) {
			try {
				$query = $this->_pdo->query('SHOW STATUS');
				if ($query && is_object($query)) {
					$query->execute();
				} else {
					throw new Exception('MySQL server has gone away');
				}
			} catch (Exception $e) {
				if ($e->getCode() !== 'HY000' && !stristr($e->getMessage(), 'server has gone away')) {
					throw $e;
				}
				$this->_pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
				$this->_pdo->exec('SET NAMES utf8');
				$this->lastActiveTime = $now;
			}
		}
	}
}
