<?php

class ResultHandler extends MsgHandler {
	private $_allowedCommand = array(
		'result', //update a specified result
		'attribute', //change cut off, time limit or format
		'round', //add/remove round or remove a round's result
		'event', //add/remove event
		'person', //add/remove person and/or his event or result
	);

	public function process() {
		$command = $this->getCommand();
		if ($command !== '') {
			$method = 'process' . ucfirst($command);
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}
	}

	public function processResult() {
	}

	public function processAttribute() {

	}

	public function processRound() {

	}

	public function processEvent() {

	}

	public function processPerson() {

	}

	private function getCommand() {
		if (isset($this->msg->command) && in_array($this->msg->command, $this->_allowedCommand)) {
			return $this->msg->command;
		}
		return '';
	}
}
