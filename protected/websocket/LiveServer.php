<?php
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Wamp\WampServerInterface;
use React\EventLoop\StreamSelectLoop;
use Psr\Http\Message\RequestInterface;

class LiveServer implements HttpServerInterface {
	protected $subscriber;
	protected $loop;
	protected $clients = array();

	protected $channelCallbacks = [
		'record.computed'=>'onRecordComputed',
	];

	private $_onlineNumbers = array();
	private $_maxOnlineNumbers = array();

	public function __construct($loop) {
		$this->loop = $loop;
		set_error_handler(function($errno, $errstr) {
			throw new Exception($errstr);
		}, E_ALL ^ E_DEPRECATED);
	}

	public function initSubscriber() {
		$this->loop->addPeriodicTimer(0.2, function() {
			$redis = Yii::app()->cache->redis;
			foreach ($this->channelCallbacks as $channel=>$method) {
				while (($message = $redis->lPop($channel)) !== false) {
					$message = json_decode($message);
					call_user_func(array($this, $method), $message);
				}
			}
		});
	}

	public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
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
		var_dump($e);
		exit;
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
		$message = json_encode($message);
		$ret = Yii::app()->cache->redis->rPush($channel, $message);
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

	public function broadcastSuccessToDataTaker($type, $data, $competition = null, $exclude = null) {
		foreach ($this->clients as $client) {
			$user = $client->user;
			if (!$competition->checkPermission($user)) {
				continue;
			}
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
