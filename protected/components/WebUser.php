<?php

class WebUser extends CWebUser {
	public function checkAccess($operation, $params = array()) {
		return !$this->isGuest && !is_null(Yii::app()->controller) && Yii::app()->controller->user->role >= $operation;
	}

	protected function afterLogin($fromCookie) {
		$loginHistory = new LoginHistory();
		$loginHistory->user_id = $this->id;
		$loginHistory->ip = Yii::app()->request->getUserHostAddress();
		$loginHistory->date = time();
		$loginHistory->from_cookie = intval($fromCookie);
		$loginHistory->save(false);
	}
}