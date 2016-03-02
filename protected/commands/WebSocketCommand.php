<?php

use Ratchet\Session\SessionProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\StreamSelectLoop;
use React\Socket\Server as Reactor;

class WebSocketCommand extends CConsoleCommand {
	const PORT = 8080;
	const ADDRESS = '127.0.0.1';

	public function actionIndex() {
		$db = Yii::app()->db;
		$pdo = $db->getPdoInstance();

		$session = new SessionProvider(
			new TestServer(),
			new PdoSessionHandler($pdo, array(
				'lock_mode'=>PdoSessionHandler::LOCK_NONE,
			)),
			array(
				'name'=>'CUBINGCHINA_SID'
			)
		);
		$app = new HttpServer(new WsServer($session));
		$loop = new StreamSelectLoop();
		$socket = new Reactor($loop);
		$socket->listen(self::PORT, self::ADDRESS);
		$server = new IoServer($app, $socket, $loop);
		$server->run();
	}
}

class TestServer implements MessageComponentInterface {
	protected $clients;

	public function __construct() {
		$this->clients = new \SplObjectStorage();
	}

	public function onOpen(ConnectionInterface $conn) {
		$this->clients->attach($conn);
		$session = $conn->Session;
		$prefix = WebUser::STATE_KEY_PREFIX;
		$key = $prefix . '__id';
		if ($session->has($key)) {
			$id = $session->get($key);
			$user = User::model()->findByPk($id);
			var_dump($user->attributes);
		}

		echo date('Y-m-d H:i:s ') . "New connection! ({$conn->resourceId})\n";
	}

	public function onMessage(ConnectionInterface $conn, $msg) {
		if ($msg === 'ping') {
			$conn->send('pong');
			return;
		}
	}

	public function onClose(ConnectionInterface $conn) {
		$this->clients->detach($conn);
		echo date('Y-m-d H:i:s ') . "Connection {$conn->resourceId} has disconnected\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo date('Y-m-d H:i:s ') . "An error has occurred: {$e->getMessage()}\n";
		$conn->close();
	}
}