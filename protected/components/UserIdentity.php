<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {
	private $_id;

	public function authenticate() {
		$user = User::model()->findByAttributes(array(
			'email'=>$this->username,
			'status'=>array(
				User::STATUS_NORMAL,
				User::STATUS_BANNED,
			),
		));
		if($user === null || CPasswordHelper::verifyPassword($this->password, $user->password) === false) {
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		} else {
			$this->id = $user->id;
			$this->errorCode = self::ERROR_NONE;
		}
		return !$this->errorCode;
	}

	public function getId() {
		return $this->_id;
	}

	public function setId($id) {
		$this->_id = $id;
	}
}