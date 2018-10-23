<?php

class CompetitionController extends ApiController {
	public function actionIndex($year = 'current', $type = '', $province = '', $event = '') {
		$model = new Competition('search');
		$model->unsetAttributes();
		$model->year = $year;
		$model->type = $type;
		$model->province = $province;
		$model->event = $event;
		$model->status = Competition::STATUS_SHOW;
		$dataProvider = $model->search();
		$competitions = $dataProvider->getData();
		$this->ajaxOK(JsonHelper::formatData($competitions));
	}

	public function actionRegistration() {
		if (Yii::app()->session->get('scan_code') === null) {
			$this->ajaxError(Constant::STATUS_FORBIDDEN);
		}
		$competitionId = $this->iGet('competition_id');
		$code = $this->sGet('code');
		if ($code == '') {
			$this->ajaxError(Constant::STATUS_NOT_FOUND);
		}
		$registration = Registration::model()->findByAttributes([
			'code'=>substr($code, 0, 64),
		]);
		if ($registration == null || $registration->competition_id != $competitionId) {
			$this->ajaxError(Constant::STATUS_NOT_FOUND);
		}
		$this->ajaxOK($registration->getDataForSignin());
	}

	public function actionTicket() {
		if (Yii::app()->session->get('scan_code') === null) {
			$this->ajaxError(Constant::STATUS_FORBIDDEN);
		}
		$id = $this->iGet('competition_id');
		$code = $this->sGet('code');
		if ($code == '') {
			$this->ajaxError(Constant::STATUS_NOT_FOUND);
		}
		$userTicket = UserTicket::model()->findByAttributes([
			'code'=>substr($code, 0, 64),
		]);
		if ($userTicket == null || $userTicket->ticket->type_id != $id) {
			$this->ajaxError(Constant::STATUS_NOT_FOUND);
		}
		$this->ajaxOK($userTicket->getDataForSignin());
	}

	public function actionSignin() {
		if (Yii::app()->session->get('scan_code') === null) {
			$this->ajaxError(Constant::STATUS_FORBIDDEN);
		}
		$type = $this->sPost('type');
		$action = $this->sPost('action');
		$id = $this->iPost('id');
		$competitionId = $this->iPost('competition_id');
		if (!$id || !$action || !$type) {
			$this->ajaxError(Constant::STATUS_FORBIDDEN);
		}
		switch ($type) {
			case 'registration':
				$model = Registration::model()->findByPk($id);
				if ($model == null || $model->competition_id != $competitionId) {
					$this->ajaxError(Constant::STATUS_NOT_FOUND);
				}
				break;
			case 'ticket':
				$model = UserTicket::model()->findByPk($id);
				if ($model == null || $model->ticket->type_id != $competitionId) {
					$this->ajaxError(Constant::STATUS_NOT_FOUND);
				}
				break;
			default:
				$this->ajaxError(Constant::STATUS_FORBIDDEN);
				break;
		}
		switch ($action) {
			case 'pay':
				if ($type == 'registration') {
					$model->paid = Registration::PAID;
				}
				break;
			case 'signin':
				$model->signed_in = ActiveRecord::YES;
				$model->signed_date = time();
				$model->signed_scan_code = Yii::app()->session->get('scan_code');
				break;
		}
		if ($model->save()) {
			$this->ajaxOK($model->getDataForSignin());
		} else {
			$this->ajaxError(Constant::STATUS_INTERNAL_ERROR);
		}
	}
}
