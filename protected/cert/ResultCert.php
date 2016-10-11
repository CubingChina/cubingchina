<?php
use JonnyW\PhantomJs\Client;

abstract class ResultCert {

	public $cert;
	public $competition;
	public $user;
	public $name;
	public $hash;
	public $hasParticipations = false;

	public function __construct($cert) {
		$this->cert = $cert;
		$this->competition = $cert->competition;
		$this->user = $cert->user;
		$this->name = strtolower(substr(get_class($this), 4));
		$this->hash = md5('competition-' . $cert->competition_id . '-' . $cert->user_id);
	}

	public function run() {
		$this->generateCert('results');
		if ($this->hasParticipations) {
			$this->generateCert('participations');
		}
	}

	public function generateCert($type = 'results') {
		$data = $this->getData($type);
		$html = $this->render($type, $data);
		if ($html === false) {
			return false;
		}
		$temp = $this->getHtmlPath($type) . $this->hash . '.html';
		file_put_contents($temp, $html);
		try {
			$client = Client::getInstance();
			$client->getEngine()->setPath(Yii::getPathOfAlias('application.bin.phantomjs'));
			$request  = $client->getMessageFactory()->createCaptureRequest('file://' . $temp);
			$response = $client->getMessageFactory()->createResponse();
			$request->setOutputFile($this->getCertFile($type));
			$client->send($request, $response);

			$cert = $this->cert;
			$cert->hash = $this->hash;
			$cert->has_participations = intval($this->hasParticipations);
			$cert->update_time = time();
			$cert->save();
		} catch (Exception $e) {

		}
		unlink($temp);
	}

	public function render($name = 'results', $_data_ = null) {
		$file = $this->getTemplateFile($name);
		if (!is_file($file)) {
			return false;
		}
		if (is_array($_data_)) {
			extract($_data_);
		}
		ob_start();
		ob_implicit_flush(false);
		require $file;
		return ob_get_clean();
	}

	public function getTemplatePath() {
		return Yii::getPathOfAlias('application.data.certs.' . $this->name);
	}

	public function getTemplateFile($name) {
		return $this->getTemplatePath() . '/' . $name . '.php';
	}

	public function getHtmlPath($type = 'results') {
		return $this->getTemplatePath() . '/html/' . $type . '/';
	}

	public function getCertFile($type = 'results') {
		$file = sprintf('%s%s/%s/%s/%s.jpg', Yii::app()->params->staticPath, 'certs', $this->name, $type, $this->hash);
		$dir = dirname($file);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		return $file;
	}

	abstract public function getData($type = 'results');
}
