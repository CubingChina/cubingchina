<?php

class WebUser extends CWebUser {

	public function checkAccess($operation, $params = array()) {
		$method = 'check' . ucfirst($operation);
		if (!method_exists($this, $method)) {
			return false;
		}
		return !$this->isGuest && !is_null(Yii::app()->controller) && $this->$method($params);
	}

	public function checkRole($role) {
		$user = Yii::app()->controller->user;
		return !$this->isGuest && $user && $user->role >= $role;
	}

	public function checkPermission($permission, $role = User::ROLE_ADMINISTRATOR) {
		$user = Yii::app()->controller->user;
		return !$this->isGuest && $user && ($user->hasPermission($permission) || $this->checkRole($role));
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