<?php

class CompetitionController extends ApiController {
	public function actionCompetitor() {
		if (Yii::app()->session->get('scan_code') === null) {
			$this->ajaxError(Constant::STATUS_FORBIDDEN);
		}
		$id = $this->iGet('competition_id');
		$code = $this->sGet('code');
		if ($code == '') {
			$this->ajaxError(Constant::STATUS_NOT_FOUND);
		}
		$registration = Registration::model()->findByAttributes([
			'code'=>substr($code, 0, 64),
		]);
		if ($registration == null || $registration->competition_id != $id) {
			$this->ajaxError(Constant::STATUS_NOT_FOUND);
		}
		$this->ajaxOK($registration->getDataForSignin());
	}

	public function actionSignin() {
		if (Yii::app()->session->get('scan_code') === null) {
			$this->ajaxError(Constant::STATUS_FORBIDDEN);
		}
		$action = $this->sPost('action');
		$id = $this->iPost('id');
		if (!$id || !$action) {
			$this->ajaxError(Constant::STATUS_NOT_FOUND);
		}
		$registration = Registration::model()->findByAttributes([
			'id'=>$id,
		]);
		if ($registration === null) {
			$this->ajaxError(Constant::STATUS_NOT_FOUND);
		}
		switch ($action) {
			case 'pay':
				$registration->paid = Registration::PAID;
				break;
			case 'signin':
				$registration->signed_in = Registration::YES;
				$registration->signed_date = time();
				$registration->signed_scan_code = Yii::app()->session->get('scan_code');
				break;
		}
		if ($registration->save()) {
			$this->ajaxOK($registration->getDataForSignin());
		} else {
			$this->ajaxError(Constant::STATUS_INTERNAL_ERROR);
		}
	}
}
