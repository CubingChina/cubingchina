<?php

Yii::import('application.websocket.handler.*');

class LiveClient {
	const CODE_OK = 200;
	const CODE_UNAUTHORIZED = 401;
	const CODE_FORBIDDEN = 403;
	const CODE_NOT_FOUND = 404;
	const CODE_INTERNAL_ERROR = 500;

	public $server;
	public $conn;
	public $user;
	public $competitionId;

	public static $competitions = array();

	public function __construct($server, $conn) {
		$this->server = $server;
		$this->conn = $conn;
		$session = $conn->Session;
		$prefix = WebUser::STATE_KEY_PREFIX;
		$key = $prefix . '__id';
		if ($session->has($key)) {
			$id = $session->get($key);
			$user = User::model()->findByPk($id);
			$this->user = $user;
		}
	}

	public function getCompetition() {
		if ($this->competitionId !== null) {
			if (isset(self::$competitions[$this->competitionId])) {
				return self::$competitions[$this->competitionId];
			}
			$competition = self::$competitions[$this->competitionId] = Competition::model()->findByPk($this->competitionId);
			return $competition;
		}
	}

	public function handleMessage($msg) {
		$msg = $this->preProcess($msg);
		if ($msg === 'ping') {
			return $this->send('pong');
		}
		if (is_object($msg) && isset($msg->type)) {
			$handlerClass = ucfirst($msg->type) . 'Handler';
			try {
				$handler = new $handlerClass($this, $msg);
				$handler->process();
				unset($handler);
			} catch (Exception $e) {
				Yii::log($e->getMessage(), 'ws', 'handler.error');
			}
		}
	}

	private function preProcess($msg) {
		$temp = json_decode($msg);
		if ($temp === false || $temp === null) {
			return $msg;
		}
		return $temp;
	}

	public function success($type, $data) {
		return $this->send(array(
			'code'=>self::CODE_OK,
			'type'=>$type,
			'data'=>$data,
			'onlineNumber'=>$this->server->getOnlineNumber($this->competitionId),
		));
	}

	public function error($code) {
		return $this->send(array(
			'code'=>$code,
			'data'=>null,
		));
	}

	public function send($msg) {
		$msg = json_encode($msg);
		if (YII_DEBUG) {
			Yii::log($msg, 'ws', 'respond');
		}
		return $this->conn->send($msg);
	}
}
