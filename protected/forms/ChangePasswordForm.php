<?php

class ChangePasswordForm extends CFormModel {
	public $password;
	public $newPassword;
	public $repeatPassword;

	/**
	 * Declares the validation rules.
	 * The rules state that email and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules() {
		return array(
			// email and password are required
			array('password, newPassword, repeatPassword', 'required'),
			array('password', 'authenticate'),
			array('newPassword', 'length', 'min'=>6),
			array('repeatPassword', 'compare', 'compareValue'=>'repeat password', 'message'=>'Please input repeat password'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels() {
		return array(
			'password'=>'密码',
			'newPassword'=>'新密码',
			'repeatPassword'=>'重复密码',
		);
	}

	public function authenticate() {
		$user = Yii::app()->controller->user;
		if (CPasswordHelper::verifyPassword($this->password, $user->password) === false) {
			$this->addError('password', 'Error password');
		}
	}

	public function changePassword() {
		$user = Yii::app()->controller->user;
		$user->password = CPasswordHelper::hashPassword($this->newPassword);
		if ($user->save()) {
			return true;
		} else {
			return false;
		}
	}
}