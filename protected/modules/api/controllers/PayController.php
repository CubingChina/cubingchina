<?php

class PayController extends ApiController {
	public function actionCreate() {
		$appId = $this->sRequest('app_id');
		$params = $_REQUEST;
		$application = Application::getByKey($appId);
		if ($application === null) {
			$this->ajaxError(Constant::STATUS_NOT_FOUND);
		}
		if ($application->isDisabled()) {
			$this->ajaxError(Constant::STATUS_FORBIDDEN);
		}
		if (!$application->hasScope('pay')) {
			$this->ajaxError(Constant::STATUS_FORBIDDEN);
		}
		if (!$application->checkSignature($params)) {
			$this->ajaxError(Constant::STATUS_WRONG_SIGNATURE);
		}
		foreach (['timestamp', 'amount', 'order_no', 'order_name', 'return_url', 'notify_url'] as $key) {
			if (!isset($params[$key])) {
				$this->ajaxError(Constant::STATUS_MISSING_PARAMS, "Params '{$key}' is missing");
			}
		}
		$isMobile = $params['is_mobile'] ?? false;
		if (abs($params['timestamp'] - time()) > Application::MAX_TIMESTAMP_RANGE) {
			$this->ajaxError(Constant::STATUS_TIMESTAMP_OUT_OF_RANGE);
		}
		if (!ctype_digit($params['amount']) || $params['amount'] <= 0) {
			$this->ajaxError(Constant::STATUS_WRONG_PARAMS, "Params 'amount' must be a positive integer");
		}
		$urlValidator = new CUrlValidator();
		foreach (['return_url', 'notify_url'] as $key) {
			if (!$urlValidator->validateValue($params[$key])) {
				$this->ajaxError(Constant::STATUS_WRONG_PARAMS, "Params '{$key}' must be a validate url");
			}
		}
		$orderNo = $application->getOrderNo($params['order_no']);
		$pay = Pay::model()->findByAttributes([
			'order_no'=>$orderNo,
		]);
		if ($pay === null) {
			$pay = new Pay();
			$pay->user_id = 0;
			$pay->type = Pay::TYPE_APPLICATION;
			$pay->type_id = $application->id;
			$pay->amount = intval($params['amount']);
			$pay->order_no = $orderNo;
			$pay->order_name = "[{$application->name_zh}]{$params['order_name']}";
			$pay->params = json_encode([
				'order_no'=>$params['order_no'],
				'return_url'=>$params['return_url'],
				'notify_url'=>$params['notify_url'],
			]);
			if (!$pay->save()) {
				$this->ajaxError(Constant::STATUS_INTERNAL_ERROR);
			}
		}
		$this->ajaxOK($pay->generateParams($isMobile));
	}
}
