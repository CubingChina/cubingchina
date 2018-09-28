<?php

class PayController extends Controller {
	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionNotify() {
		$channel = $this->sGet('channel');
		unset($_GET['channel']);
		switch ($channel) {
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
		$result = $model->validateNotify($channel, $params);
		Yii::log(json_encode([
			'params'=>$params,
			'result'=>$result,
		]), 'pay', 'notify');
		echo Pay::notifyReturn($channel, $result);
	}

	public function actionFrontNotify() {
		$channel = $this->sGet('channel');
		unset($_GET['channel']);
		switch ($channel) {
			default:
				$orderId = $this->sGet('out_trade_no');
				break;
		}
		$model = Pay::getPayByOrderId($orderId);
		if ($model === null) {
			throw new CHttpException(404, 'Not Found');
		}
		$result = $model->validateNotify($channel, $_GET);
		Yii::log(json_encode([
			'params'=>$_GET,
			'result'=>$result,
		]), 'pay', 'notify.front');
		if ($result) {
			switch ($model->type) {
				case Pay::TYPE_REGISTRATION:
					Yii::app()->user->setFlash('success', Yii::t('common', 'Paid successfully'));
					$competition = $model->competition;
					$this->redirect($competition->getUrl('registration'));
					break;
				case Pay::TYPE_APPLICATION:
					$params = json_decode($model->params);
					if (!isset($params->return_url)) {
						break;
					}
					$application = $model->application;
					$returnParams = $application->generateReturnParams($model);
					$this->sendForm($params->return_url, $returnParams);
					break;
				case Pay::TYPE_TICKET:
					Yii::app()->user->setFlash('success', Yii::t('common', 'Paid successfully'));
					$ticket = $model->ticket;
					$competition = $ticket->competition;
					$this->redirect($competition->getUrl('ticket'));
					break;
			}
		}
		$this->render('result', array(
			'model'=>$model,
			'result'=>$result,
		));
	}

	public function actionParams() {
		$this->setIsAjaxRequest(true);
		$id = $this->iGet('id');
		$isMobile = $this->iRequest('is_mobile');
		$channel = $this->sRequest('channel');
		$model = Pay::model()->findByPk($id);
		if ($model === null || $model->user_id !== Yii::app()->user->id) {
			throw new CHttpException(401, 'Unauthorized Access');
		}
		$model->reviseAmount();
		$params = array();
		if ($model->isPaid()) {
			switch ($model->type) {
				case Pay::TYPE_REGISTRATION:
					$competition = $model->competition;
					$params['url'] = CHtml::normalizeUrl($competition->getUrl('registration'));
					break;
				case Pay::TYPE_TICKET:
					$ticket = $model->ticket;
					$competition = $ticket->competition;
					$params['url'] = CHtml::normalizeUrl($competition->getUrl('ticket'));
					break;
			}
		} else {
			$params = $model->generateParams($isMobile, $channel);
		}
		$this->ajaxOk($params);
	}
}
