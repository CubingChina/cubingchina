<?php
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class LiveServer implements MessageComponentInterface {
	protected $clients;

	public function __construct() {
		$this->clients = new \SplObjectStorage();
	}

	public function onOpen(ConnectionInterface $conn) {
		$client = new LiveClient($conn);
		$conn->client = $client;
		$this->clients->attach($client);
		Yii::log("New connection: {$conn->resourceId}", 'ws', 'connect');

		$session = $conn->Session;
		$prefix = WebUser::STATE_KEY_PREFIX;
		$key = $prefix . '__id';
		if ($session->has($key)) {
			$id = $session->get($key);
			$user = User::model()->findByPk($id);
			$client->user = $user;
		}
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
}