<?php

class ChatHandler extends MsgHandler {
	public static $messages = array();

	const RECENT_MESSAGE_NUM = 50;

	public function process() {
		$action = $this->getAction();
		if ($action !== '') {
			$method = 'action' . ucfirst($action);
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}
	}

	public function actionFetch() {
		if ($this->competition != null) {
			$this->success('message.recent', array_map(function($message) {
				return $message->getShowAttributes();
			}, isset(self::$messages[$this->competition->id]) ? self::$messages[$this->competition->id] : array()));
		}
	}

	public function actionSend() {
		if (!empty($this->msg->content) && $this->competition != null && $this->user != null) {
			$message = new LiveMessage();
			$message->competition_id = $this->competition->id;
			$message->user_id = $this->user->id;
			$message->event = $this->msg->params->event;
			$message->round = $this->msg->params->round;
			$message->content = $this->msg->content;
			$message->create_time = time();
			$message->save();
			self::$messages[$this->competition->id][] = $message;
			self::$messages[$this->competition->id] = array_slice(self::$messages[$this->competition->id], 0, self::RECENT_MESSAGE_NUM);
			$this->broadcastSuccess('message.new', $message->getShowAttributes(), $this->competition, $this->client);
		}
	}
}
