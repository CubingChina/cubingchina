<?php

abstract class MsgHandler {
	public $client;
	public $msg;

	public function __construct($client, $msg) {
		$this->client = $client;
		$this->msg = $msg;
	}

	abstract public function process();

	public function __call($method, $args) {
		try {
			if (method_exists($this->client->server, $method)) {
				call_user_func_array(array($this->client->server, $method), $args);
			} elseif (method_exists($this->client, $method)) {
				call_user_func_array(array($this->client, $method), $args);
			}
		} catch (Exception $e) {
			Yii::log($e->getMessage(), 'ws', 'error');
		}
	}
}