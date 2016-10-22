<?php
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class LiveServer implements MessageComponentInterface {
	protected $clients = array();

	private $_onlineNumbers = array();
	private $_maxOnlineNumbers = array();

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
