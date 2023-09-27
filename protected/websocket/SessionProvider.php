<?php
use Ratchet\ConnectionInterface;
use Psr\Http\Message\RequestInterface;

class SessionProvider extends Ratchet\Session\SessionProvider {
	public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
		if ($request === null) {
			$request = $conn->httpRequest;
		}
		return parent::onOpen($conn, $request);
	}
}
