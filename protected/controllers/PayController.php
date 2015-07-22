<?php

class PayController extends Controller {
	public function accessRules() {
		return array(
			array(
				'deny',
				'users'=>array('?'),
				'actions'=>array('registration'),
			),
			array(
				'allow',
				'users'=>array('@'),
				'actions'=>array('reactivate'),
			),
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionRegistration() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null || $model->user_id != Yii::app()->user->id) {
			throw new CHttpException(401, 'Unauthorized Access');
		}
		if ($model->pay === null) {
			$model->pay = $model->createPay();
		}
		if ($model->pay->amount != $model->getTotalFee() * 100) {
			$model->pay->amount = $model->getTotalFee() * 100;
			// $model->pay->save(false);
		}
		$this->render('pay', array(
			'model'=>$model->pay,
		));
	}

	public function actionNotify() {
		$paramsStr = file_get_contents('php://input');
		parse_str($paramsStr, $params);
		$orderId = isset($params['mhtOrderNo']) ? $params['mhtOrderNo'] : '';
		$model = Pay::getPayByOrderId($orderId);
		if ($model === null) {
			echo 'success=N';
			exit;
		}
		$result = $model->validateNotify($params);
		if ($result) {
			echo 'success=Y';
		} else {
			echo 'success=N';
		}
	}

	public function actionFrontNotify() {
		$orderId = $this->sGet('mhtOrderNo');
		$model = Pay::getPayByOrderId($orderId);
		if ($model === null) {
			throw new CHttpException(404, 'Not Found');
		}
		$result = $model->validateNotify($_GET);
		if ($result) {
			switch ($model->type) {
				case Pay::TYPE_REGISTRATION:
					Yii::app()->user->setFlash('success', Yii::t('common', 'Paid successfully'));
					$competition = $model->competition;
					$this->redirect($competition->getUrl('competitors'));
					break;
			}
		}
		$this->render('result', array(
			'model'=>$model,
			'result'=>$result,
		));
	}

	public function actionUrl() {
		$id = $this->iGet('id');
		$isMobile = $this->iRequest('is_mobile');
		$model = Pay::model()->findByPk($id);
		if ($model === null || $model->user_id !== Yii::app()->user->id) {
			throw new CHttpException(401, 'Unauthorized Access');
		}
		$url = '';
		if ($model->isPaid()) {
			switch ($model->type) {
				case Pay::TYPE_REGISTRATION:
					$competition = $model->competition;
					$url = $competition->getUrl('registration');
					break;
			}
		} else {
			$url = $model->generateNowPayUrl($isMobile);
		}
		$this->ajaxOk(array(
			'url'=>$url,
		));
	}
}