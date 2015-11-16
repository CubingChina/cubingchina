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

	public function loginRequired() {
		$app = Yii::app();
		$request = $app->getRequest();

		if (!$request->getIsAjaxRequest()) {
			$this->setReturnUrl($request->getBaseUrl(true) . $request->getUrl());
			if (($url = $this->loginUrl) !== null) {
				if (is_array($url)) {
					$route = isset($url[0]) ? $url[0] : $app->defaultController;
					$url = $app->createUrl($route, array_splice($url, 1));
				}
				$request->redirect($url);
			}
		} elseif (isset($this->loginRequiredAjaxResponse)) {
			echo $this->loginRequiredAjaxResponse;
			Yii::app()->end();
		}

		throw new CHttpException(403, Yii::t('yii', 'Login Required'));
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