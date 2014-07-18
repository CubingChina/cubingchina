<?php

class ResetPasswordForm extends ActionFormModel {
	public $password;
	public $repeatPassword;

	/**
	 * Declares the validation rules.
	 * The rules state that email and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules() {
		return array(
			// email and password are required
			array('password, repeatPassword', 'required'),
			array('password', 'length', 'min'=>6),
			array('repeatPassword', 'compare', 'compareAttribute'=>'password'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels() {
		return array(
			'password'=>Yii::t('common', 'Password'),
			'repeatPassword'=>Yii::t('common', 'Repeat Password'),
		);
	}

	public function getUser() {
		return $this->getUserAction()->user;
	}

	public function changePassword() {
		$user = $this->getUser();
		$user->password = CPasswordHelper::hashPassword($this->password);
		if ($user->save()) {
			$this->clear();
			return true;
		} else {
			return false;
		}
	}
}