<?php

class ActionFormModel extends CFormModel {

	private $_userAction;

	public function getUserAction($action = null) {
		if ($this->_userAction !== null) {
			return $this->_userAction;
		}
		$code = Yii::app()->controller->sGet('c');
		if ($action === null) {
			$action = $this->getAction();
		}
		$userAction = UserAction::model()->findByAttributes(array(
			'code'=>$code,
			'action'=>$action,
		));
		return $this->_userAction = $userAction;
	}

	public function getAction() {
		return Yii::app()->controller->action->id;
	}

	public function checkCode($time = 24) {
		$userAction = $this->getUserAction();
		if ($userAction === null) {
			return false;
		}
		return $userAction->status == UserAction::STATUS_INIT && $userAction->date + $time * 3600 > time();
	}

	public function clear($clearAll = true) {
		$userAction = $this->getUserAction();
		if ($userAction === null) {
			return;
		}
		$userAction->status = UserAction::STATUS_USED;
		$userAction->save();
		if ($clearAll) {
			UserAction::model()->updateAll(array(
				'status'=>UserAction::STATUS_USED,
			), 'user_id=:user_id AND action=:action', array(
				':user_id'=>$userAction->user_id,
				':action'=>$userAction->action,
			));
		}
	}

}