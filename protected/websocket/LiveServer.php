<?php
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class LiveServer implements MessageComponentInterface {
	protected $clients;

	public function __construct() {
		$this->clients = new \SplObjectStorage();
	}

	public function onOpen(ConnectionInterface $conn) {
		$client = new LiveClient($this, $conn);
		$conn->client = $client;
		$this->clients->attach($client);
		Yii::log("New connection: {$conn->resourceId}", 'ws', 'connect');
	}

	public function onMessage(ConnectionInterface $conn, $msg) {
		Yii::log($msg, 'ws', 'msg');
		$conn->client->handleMessage($msg);
	}

	public function onClose(ConnectionInterface $conn) {
		$this->clients->detach($conn->client);
		Yii::log("Connection {$conn->resourceId} has disconnected", 'ws', 'disconnect');
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		Yii::log($e->getMessage(), 'ws', 'error');
		$conn->close();
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
}