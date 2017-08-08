<?php
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\Wamp\WampServerInterface;

class LiveServer implements MessageComponentInterface {
	protected $subscriber;
	protected $clients = array();

	protected $channelCallbacks = [
		'record.computed'=>'onRecordComputed',
	];

	private $_onlineNumbers = array();
	private $_maxOnlineNumbers = array();

	public function initSubscriber($subscriber) {
		$this->subscriber = $subscriber;
		foreach ($this->channelCallbacks as $channel=>$method) {
			$this->subscribe($channel, [$this, $method]);
		}
	}

	public function onOpen(ConnectionInterface $conn) {
		$client = new LiveClient($this, $conn);
		$conn->client = $client;
		$this->clients[$conn->resourceId] = $client;
		Yii::log("New connection: {$conn->resourceId}", 'ws', 'connect');
	}

	public function onMessage(ConnectionInterface $conn, $msg) {
		Yii::log($msg, 'ws', 'msg');
		$conn->client->handleMessage($msg);
	}

	public function onClose(ConnectionInterface $conn) {
		$this->decreaseOnlineNumber($this->clients[$conn->resourceId]->competitionId);
		unset($this->clients[$conn->resourceId]);
		Yii::log("Connection {$conn->resourceId} has disconnected", 'ws', 'disconnect');
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		Yii::log($e->getMessage(), 'ws', 'error');
		$conn->close();
	}

	public function subscribe($channel, $callback) {
		$this->subscriber->subscribe($channel, function($message) use ($channel, $callback) {
			if (!is_array($message)) {
				return;
			}
			$_channel = $message[1] ?? '';
			$message = $message[2] ?? '';
			if ($_channel != $channel) {
				return;
			}
			$message = json_decode($message);
			if (!$message) {
				return;
			}
			call_user_func($callback, $message);
		});
	}

	public function onRecordComputed($message) {
		$competition = Competition::model()->findByPk($message->competitionId);
		foreach ($message->results as $result) {
			$this->broadcastSuccess('result.update', $result, $competition);
		}
	}

	public function addToQueue($channel, $message) {
		var_dump($channel, $message);
		$message = json_encode($message);
		$ret = Yii::app()->cache->redis->rPush($channel, $message);
		var_dump($ret);
	}

	public function broadcast($msg, $competition = null, $exclude = null) {
		foreach ($this->clients as $client) {
			if ($client != $exclude && ($competition === null || $client->competitionId == $competition->id)) {
				$client->send($msg);
			}
		}
	}

	public function broadcastSuccess($type, $data, $competition = null, $exclude = null) {
		foreach ($this->clients as $client) {
			if ($client != $exclude && ($competition === null || $client->competitionId == $competition->id)) {
				$client->success($type, $data);
			}
		}
	}

	public function getOnlineNumber($competitionId) {
		if (!isset($this->_onlineNumbers[$competitionId])) {
			$this->_onlineNumbers[$competitionId] = 0;
		}
		return $this->_onlineNumbers[$competitionId];
	}

	public function increaseOnlineNumber($competitionId) {
		if (!isset($this->_onlineNumbers[$competitionId])) {
			$this->_onlineNumbers[$competitionId] = 0;
			$this->_maxOnlineNumbers[$competitionId] = 0;
		}
		$this->_onlineNumbers[$competitionId]++;
		if ($this->_onlineNumbers[$competitionId] > $this->_maxOnlineNumbers[$competitionId]) {
			$this->_maxOnlineNumbers[$competitionId] = $this->_onlineNumbers[$competitionId];
			Yii::log(sprintf('New max online: %d, competitionId: %d', $this->_maxOnlineNumbers[$competitionId], $competitionId), 'ws', 'online');
		}
	}

	public function decreaseOnlineNumber($competitionId) {
		if (isset($this->_onlineNumbers[$competitionId]) && $this->_onlineNumbers[$competitionId] > 0) {
			$this->_onlineNumbers[$competitionId]--;
		}
	}
}
