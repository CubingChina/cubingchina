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
			$model->pay->save(false);
		}
		$this->render('pay', array(
			'model'=>$model->pay,
			'registration'=>$model,
			'competition'=>$model->competition,
		));
	}

	public function actionNotify() {
		$channel = $this->sGet('channel');
		unset($_GET['channel']);
		switch ($channel) {
			case 'nowPay':
				$paramsStr = file_get_contents('php://input');
				parse_str($paramsStr, $params);
				$orderId = isset($params['mhtOrderNo']) ? $params['mhtOrderNo'] : '';
				break;
			default:
				$orderId = $this->sPost('out_trade_no');
				$params = $_POST;
				break;
		}
		$model = Pay::getPayByOrderId($orderId);
		if ($model === null) {
			echo Pay::notifyReturn($channel, false);
			exit;
		}
		$result = $model->validateNowPayNotify($params);
		if ($result) {
			echo Pay::notifyReturn($channel, true);
		} else {
			echo Pay::notifyReturn($channel, false);
		}
	}

	public function actionFrontNotify() {
		$channel = $this->sGet('channel');
		unset($_GET['channel']);
		switch ($channel) {
			case 'nowPay':
				$orderId = $this->sGet('mhtOrderNo');
				break;
			default:
				$orderId = $this->sGet('out_trade_no');
				break;
		}
		$model = Pay::getPayByOrderId($orderId);
		if ($model === null) {
			throw new CHttpException(404, 'Not Found');
		}
		$result = $model->validateNotify($channel, $_GET);
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

	public function actionParams() {
		$id = $this->iGet('id');
		$isMobile = $this->iRequest('is_mobile');
		$channel = $this->sRequest('channel');
		$model = Pay::model()->findByPk($id);
		if ($model === null || $model->user_id !== Yii::app()->user->id) {
			throw new CHttpException(401, 'Unauthorized Access');
		}
		$params = array();
		if ($model->isPaid()) {
			switch ($model->type) {
				case Pay::TYPE_REGISTRATION:
					$competition = $model->competition;
					$params['url'] = $competition->getUrl('registration');
					break;
			}
		} else {
			$params = $model->generateParams($isMobile, $channel);
		}
		$this->ajaxOk($params);
	}
}