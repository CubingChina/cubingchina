<?php

class WebUser extends CWebUser {
	public function checkAccess($operation, $params = array()) {
		return !$this->isGuest && !is_null(Yii::app()->controller) && Yii::app()->controller->user->role >= $operation;
	}
}