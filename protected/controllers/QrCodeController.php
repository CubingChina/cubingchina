<?php

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;

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
		$qrCode = $this->generateQrCode($this->createUrl(
			'/competition/signin',
			array(
				'code'=>$code,
			)
		));
		$this->send($qrCode, 'signin');
	}

	public function actionTicket() {
		$code = $this->sGet('code');
		$qrCode = $this->generateQrCode($this->createUrl(
			'/competition/ticket',
			[
				'code'=>$code,
			]
		));
		$this->send($qrCode, 'ticket');
	}

	public function actionSigninAdmin() {
		$code = $this->sGet('code');
		$auth = ScanAuth::model()->findByAttributes(['code'=>$code]);
		if ($auth === null) {
			throw new CHttpException(404, 'Not found');
		}
		$qrCode = $this->generateQrCode($this->createUrl(
			'/competition/scan',
			[
				'alias'=>$auth->competition->alias,
				'scan_code'=>$code,
			]
		));
		$this->send($qrCode, 'signin');
	}

	public function actionBind() {
		$qrCode = $this->generateQrCode($this->createUrl(
			'/user/bind'
		));
		$this->send($qrCode, 'bind');
	}

	public function actionWechatPayment() {
		$url = $this->sGet('url');
		$qrCode = $this->generateQrCode($url);
		$this->send($qrCode, 'wechatPayment');
	}

	private function generateQrCode($text) {
		$qrCode = new QrCode($text);
		$qrCode->setSize(300);
		$qrCode->setMargin(10);
		$qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));
		$qrCode->setLabelFontSize(16);
		return $qrCode;
	}

	private function send($qrCode, $name) {
		header('Content-type: ' .  $qrCode->getContentType());
		header("Content-Disposition: attachment; filename={$name}.jpg");
		echo $qrCode->writeString();
	}
}
