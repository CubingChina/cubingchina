<?php

class GitController extends Controller {
	protected $logAction = false;

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionDeploy() {
		$this->setIsAjaxRequest(true);
		$event = $this->getHeader('event');
		$signature = $this->getHeader('signature');
		$delivery = $this->getHeader('delivery');
		$payload = file_get_contents('php://input');
		try {
			if ($event == '' || $delivery == '') {
				throw new CHttpException(403, 'Forbidden');
			}
			if (!$this->validateSignature($signature, $payload)) {
				throw new CHttpException(401, 'Unauthorized');
			}
			Yii::log($payload, 'git');
			$data = json_decode($payload);
			switch ($event) {
				case 'ping':
					$this->ajaxOK('pong');
					break;
				case 'push':
					$ref = $data->ref;
					if ($ref == 'refs/heads/master') {
						$path = APP_PATH;
					} else {
						$path = Env::get('DEVELOP_PATH');
						if (!is_dir($path)) {
							$this->ajaxOK('success');
						}
					}
					exec('sh ' . $path . '/protected/commands/shell/deploy.sh 2>&1', $output, $ret);
					Yii::log(implode(PHP_EOL, $output), 'git', 'deploy');
					if ($ret == 0) {
						$this->ajaxOK('success');
					} else {
						Yii::log($ret, 'git', 'deploy');
						throw new CHttpException(500, 'Intenal Error');
					}
					break;
			}
		} catch (CHttpException $e) {
			$code = $e->statusCode;
			$message = $e->getMessage();
			header("HTTP/1.1 $code $message");
			$this->ajaxError($code, $message);
		}
	}

	private function getHeader($name) {
		$request = Yii::app()->request;
		$prefix = 'github';
		if ($name == 'signature') {
			$prefix = 'hub';
		}
		$key = implode('_', array_map('strtoupper', ['http', 'x', $prefix, $name]));
		return isset($_SERVER[$key]) ? $_SERVER[$key]  : '';
	}

	private function validateSignature($signature, $payload) {
		if (strpos($signature, '=') === false) {
			return false;
		}
		list($algo, $signature) = explode('=', $signature);
		$hash = hash_hmac($algo, $payload, Env::get('GITHUB_SECRET'));
		return hash_equals($hash, $signature);
	}
}
