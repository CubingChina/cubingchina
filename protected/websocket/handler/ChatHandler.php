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

	public function actionDisable() {
		if (!$this->checkAccess()) {
			return;
		}
		if ($this->competition != null) {
			$this->competition->formatEvents();
			$this->competition->formatDate();
			$this->competition->disable_chat = (int)$this->msg->disable_chat;
			$this->competition->save();
			$this->broadcastSuccess('message.disable', $this->msg->disable_chat);
		}
	}

	public function actionFetch() {
		if ($this->competition != null) {
			if (!isset(self::$messages[$this->competition->id])) {
				self::$messages[$this->competition->id] = array_reverse(LiveMessage::model()->findAllByAttributes(array(
					'competition_id'=>$this->competition->id,
				), array(
					'order'=>'create_time DESC',
					'limit'=>self::RECENT_MESSAGE_NUM,
				)));
			}
			$this->success('message.disable', !!$this->competition->disable_chat);
			$this->success('message.recent', array_map(function($message) {
				return $message->getShowAttributes();
			}, self::$messages[$this->competition->id]));
		}
	}

	public function actionSend() {
		if (!empty($this->msg->content) && $this->competition != null && $this->user != null) {
			$message = new LiveMessage();
			$message->competition_id = $this->competition->id;
			$message->user_id = $this->user->id;
			$message->event = $this->msg->params->e;
			$message->round = $this->msg->params->r;
			$message->content = $this->msg->content;
			$message->create_time = time();
			$message->save();
			self::$messages[$this->competition->id][] = $message;
			self::$messages[$this->competition->id] = array_slice(self::$messages[$this->competition->id], -self::RECENT_MESSAGE_NUM);
			$this->broadcastSuccess('message.new', $message->getShowAttributes(), $this->competition, $this->client);
		}
	}
}
