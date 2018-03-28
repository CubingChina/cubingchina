<?php

class DefaultController extends ApiController {
	public function actionError() {
		if ($error = Yii::app()->errorHandler->error) {
			$this->ajaxError($error['code'] ?? Constant::STATUS_INTERNAL_ERROR, $error['message'] ?? '');
		} else {
			$this->ajaxError(Constant::STATUS_INTERNAL_ERROR);
		}
	}
}
