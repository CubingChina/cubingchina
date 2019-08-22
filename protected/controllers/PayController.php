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
			case Pay::CHANNEL_BALIPAY:
				$orderNo = $this->sPost('out_trade_no');
				$params = $_POST;
				$model = Pay::getByOrderNo($orderNo);
				if ($model === null) {
					echo Pay::notifyReturn($channel, false);
					exit;
				}
				$result = $model->validateAlipayNotify($params);
				Yii::log(json_encode([
					'params'=>$params,
					'result'=>$result,
					'channel'=>$channel,
				]), 'pay', 'notify');
				echo Pay::notifyReturn($channel, $result);
				break;
			case Pay::CHANNEL_WECHAT:
				$wechatPayment = Pay::getWechatPayment();
				$response = $wechatPayment->handlePaidNotify(function ($message, $fail) use ($wechatPayment) {
					$result = ($message['result_code'] ?? '') === 'SUCCESS';
					$orderNo = $message['out_trade_no'] ?? '';
					Yii::log(json_encode([
						'message'=>$message,
						'result'=>$result,
						'channel'=>Pay::CHANNEL_WECHAT,
					]), 'pay', 'notify');
					$model = Pay::getByOrderNo($orderNo);
					if ($model === null) {
						return $fail('Unknown payment');
					}
					if ($result) {
						$model->updateStatus(Pay::STATUS_PAID, $message['total_fee'] ?? 0);
					} else {
						$model->resetOrder();
					}
					return true;
				});
				$response->send();
				break;
		}
	}

	public function actionRefundNotify() {
		$channel = $this->sGet('channel');
		unset($_GET['channel']);
		switch ($channel) {
			case Pay::CHANNEL_WECHAT:
				$wechatPayment = Pay::getWechatPayment();
				$response = $wechatPayment->handleRefundedNotify(function ($message, $reqInfo, $fail) use ($wechatPayment) {
					Yii::log(json_encode([
						'message'=>$message,
						'reqInfo'=>$reqInfo,
						'channel'=>Pay::CHANNEL_WECHAT,
					]), 'pay', 'notify.refund');
					return true;
				});
				$response->send();
				break;
		}
	}

	public function actionFrontNotify() {
		$channel = $this->sGet('channel');
		unset($_GET['channel']);
		$orderNo = $this->sGet('out_trade_no');
		$model = Pay::getByOrderNo($orderNo);
		if ($model === null) {
			throw new CHttpException(404, 'Not Found');
		}
		$result = $model->validateAlipayNotify($_GET);
		Yii::log(json_encode([
			'params'=>$_GET,
			'result'=>$result,
		]), 'pay', 'notify.front');
		if ($result) {
			switch ($model->type) {
				case Pay::TYPE_REGISTRATION:
				case Pay::TYPE_TICKET:
					Yii::app()->user->setFlash('success', Yii::t('common', 'Paid successfully'));
					$this->redirect($model->getUrl());
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
			}
		}
		$this->render('result', array(
			'model'=>$model,
			'result'=>$result,
		));
	}

	public function actionCheck() {
		$this->setIsAjaxRequest(true);
		$id = $this->iPost('id');
		$model = Pay::model()->findByPk($id);
		if ($model === null || $model->user_id !== Yii::app()->user->id) {
			throw new CHttpException(401, 'Unauthorized Access');
		}
		if (!$model->isPaid()) {
			$model->updateOrderStatus();
		}
		$this->ajaxOk([
			'url'=>$model->url,
		]);
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
		$params = [];
		if ($model->isPaid()) {
			$params['type'] = 'paid';
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
			$params = $model->generateParams($channel, $isMobile, $this->isInWechat);
		}
		$this->ajaxOk($params);
	}
}
