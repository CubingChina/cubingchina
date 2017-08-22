<?php

class WebUser extends CWebUser {
	const STATE_KEY_PREFIX = 'user';

	public function init() {
		$this->setStateKeyPrefix(self::STATE_KEY_PREFIX);
		parent::init();
	}

	public function checkAccess($operation, $params = array(), $allowCaching = true) {
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

	public function getState($key, $defaultValue = null) {
		$key = $this->getStateKeyPrefix() . $key;
		return Yii::app()->session->get($key, $defaultValue);
	}

	public function setState($key, $value, $defaultValue = null) {
		$key = $this->getStateKeyPrefix().$key;
		$session = Yii::app()->session;
		if ($value === $defaultValue) {
			$session->remove($key);
		} else {
			$session->add($key, $value);
		}
	}

	public function hasState($key) {
		$key = $this->getStateKeyPrefix() . $key;
		return Yii::app()->session->contains($key);
	}

	public function getFlashes($delete = true) {
		$session = Yii::app()->session;
		$flashes = array();
		$prefix = $this->getStateKeyPrefix() . self::FLASH_KEY_PREFIX;
		$n = strlen($prefix);
		foreach($session as $key=>$value) {
			if(!strncmp($key, $prefix, $n)) {
				$flashes[substr($key, $n)] = $value;
				if($delete) {
					$session->remove($key);
				}
			}
		}
		if ($delete) {
			$this->setState(self::FLASH_COUNTERS, array());
		}
		return $flashes;
	}

	public function clearStates() {
		$session = Yii::app()->session;
		$prefix = $this->getStateKeyPrefix();
		$n = strlen($prefix);
		foreach ($session as $key=>$value) {
			if (!strncmp($key, $prefix, $n)) {
				$session->remove($key);
			}
		}
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
