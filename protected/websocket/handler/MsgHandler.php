<?php

abstract class MsgHandler {
	public $client;
	public $msg;

	public function __construct($client, $msg) {
		$this->client = $client;
		$this->msg = $msg;
	}

	public function process();

	public function __call($method, $args) {
		try {
			call_user_func_array(array($this->client, $method), $args);
		} catch (Exception $e) {
			
		}
	}
}