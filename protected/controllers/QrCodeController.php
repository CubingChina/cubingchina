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
		$qrCode = new QrCode($this->createUrl(
			'/competition/signin',
			array(
				'code'=>$code,
			)
		));
		$qrCode->setSize(300);
		$qrCode->setMargin(10);
		$qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));
		$qrCode->setLabelFontSize(16);
		$this->send($qrCode, 'signin');
	}

	public function actionTicket() {
		$code = $this->sGet('code');
		$qrCode = new QrCode($this->createUrl(
			'/competition/ticket',
			[
				'code'=>$code,
			]
		));
		$qrCode->setSize(300);
		$qrCode->setMargin(10);
		$qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));
		$qrCode->setLabelFontSize(16);
		$this->send($qrCode, 'ticket');
	}

	public function actionSigninAdmin() {
		$code = $this->sGet('code');
		$auth = ScanAuth::model()->findByAttributes(['code'=>$code]);
		if ($auth === null) {
			throw new CHttpException(404, 'Not found');
		}
		$qrCode = new QrCode($this->createUrl(
			'/competition/scan',
			[
				'alias'=>$auth->competition->alias,
				'scan_code'=>$code,
			]
		));
		$qrCode->setSize(300);
		$qrCode->setMargin(10);
		$qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));
		$qrCode->setLabelFontSize(16);
		$this->send($qrCode, 'signin');
	}

	public function actionBind() {
		$qrCode = new QrCode($this->createUrl(
			'/user/bind'
		));
		$qrCode->setSize(300);
		$qrCode->setMargin(10);
		$qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));
		$qrCode->setLabelFontSize(16);
		$this->send($qrCode, 'bind');
	}

	private function send($qrCode, $name) {
		header('Content-type: ' .  $qrCode->getContentType());
		header("Content-Disposition: attachment; filename='{$name}.jpg'");
		echo $qrCode->writeString();
	}
}
