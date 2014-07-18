<?php

/**
 * ForgetPasswordForm class.
 */
class ForgetPasswordForm extends CFormModel {
	public $email;
	public $verifyCode;

	private $_user;

	/**
	 * Declares the validation rules.
	 * The rules state that email and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules() {
		return array(
			array('email, verifyCode', 'required'),
			array('email', 'checkEmail'),
			array('verifyCode', 'captcha'),
		);
	}

	public function checkEmail() {
		$user = User::model()->findByAttributes(array(
			'email'=>$this->email,
			'status'=>array(
				User::STATUS_NORMAL,
				User::STATUS_BANNED,
			),
		));
		if ($user === null) {
			$this->addError('email', Yii::t('common', 'Invalid email.'));
		}
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels() {
		return array(
			'email'=>Yii::t('common', 'Email'),
			'verifyCode'=>Yii::t('common', 'Verify Code'),
		);
	}

	public function getUser() {
		if ($this->_user === null) {
			$this->_user = User::model()->findByAttributes(array(
				'email'=>$this->email,
			));
		}
		return $this->_user;
	}

	public function sendMail() {
		return Yii::app()->mailer->sendResetPassword($this->getUser());
	}
}
