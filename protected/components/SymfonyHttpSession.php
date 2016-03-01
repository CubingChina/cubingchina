<?php
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

class SymfonyHttpSession extends CHttpSession {
	private $_session;
	private $_handler;
	private $_storage;

	public function init() {
		$pdo = Yii::app()->db->getPdoInstance();
		$this->_handler = new PdoSessionHandler($pdo);
		$this->_storage = new NativeSessionStorage(array(), $this->_handler);
		$this->_session = new Session($this->_storage);

		parent::init();
	}

	public function getUseCustomStorage() {
		return true;
	}

	public function regenerateID($deleteOldSession = false) {
		return $this->_storage->regenerate($deleteOldSession);
	}

	public function openSession($savePath, $sessionName) {
		return $this->_handler->open($savePath, $sessionName);
	}

	public function closeSession() {
		return $this->_handler->close();
	}

	public function readSession($id) {
		return $this->_handler->read($id);
	}

	public function writeSession($id, $data) {
		return $this->_handler->write($id, $data);
	}

	public function destroySession($id) {
		return $this->_handler->destroy($id);
	}

	public function gcSession($maxLifetime) {
		return $this->_handler->gc($maxLifetime);
	}

	public function getSsss() {
		return $this->_session;
	}

	public function getIterator() {
		return $this->_session->getIterator();
	}

	public function getCount() {
		return $this->_session->count();
	}

	public function getKeys() {
		return array_keys($this->_session->all());
	}

	public function get($key, $defaultValue = null) {
		return $this->_session->get($key, $defaultValue);
	}

	public function itemAt($key) {
		return $this->get($key);
	}

	public function add($key, $value) {
		return $this->_session->set($key, $value);
	}

	public function remove($key) {
		return $this->_session->remove($key);
	}

	public function clear() {
		return $this->_session->clear();
	}

	public function contains($key) {
		return $this->_session->has($key);
	}

	public function toArray() {
		return $this->_session->all();
	}

	public function offsetExists($offset) {
		return $this->contains($offset);
	}

	public function offsetGet($offset) {
		return $this->get($offset);
	}

	public function offsetSet($offset, $item) {
		return $this->add($offset, $item);
	}

	public function offsetUnset($offset) {
		return $this->remove($offset);
	}
}
