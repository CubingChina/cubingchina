<?php

use Endroid\QrCode\QrCode;

class QrCodeController extends Controller {

	public function filters() {
		return array(
			'accessControl',
		);
	}

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function init() {
		if (!DEV) {
			Yii::app()->urlManager->setBaseUrl(Yii::app()->params->baseUrl);
		}
		parent::init();
	}

	public function actionSignin() {
		$code = $this->sGet('code');
		$qrCode = new QrCode();
		$qrCode->setText($this->createUrl(
			'/competition/signin',
			array(
				'code'=>$code,
			)
		))
		->setSize(300)
		->setPadding(10)
		->setErrorCorrection('high')
		// ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
		// ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
		// ->setLabel('My label')
		->setLabelFontSize(16);
		$this->send($qrCode, 'signin');
	}

	public function actionSigninAdmin() {
		$code = $this->sGet('code');
		$auth = ScanAuth::model()->findByAttributes(['code'=>$code]);
		if ($auth === null) {
			throw new CHttpException(404, 'Not found');
		}
		$qrCode = new QrCode();
		$qrCode->setText($this->createUrl(
			'/competition/scan',
			[
				'name'=>$auth->competition->alias,
				'scan_code'=>$code,
			]
		))
		->setSize(300)
		->setPadding(10)
		->setErrorCorrection('high')
		->setLabelFontSize(16);
		$this->send($qrCode, 'signin');
	}

	private function send($qrCode, $name) {
		header('Content-type: image/jpeg');
		header("Content-Disposition: attachment; filename='{$name}.jpg'");
		$qrCode->render();
	}
}
