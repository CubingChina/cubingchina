<?php

/**
 * ActivateForm class.
 */
class ActivateForm extends CFormModel {
	public $verifyCode;
	public $email;

	/**
	 * Declares the validation rules.
	 * The rules state that email and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules() {
		return array(
			array('verifyCode', 'required'),
			array('verifyCode', 'captcha'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels() {
		return array(
			'verifyCode'=>Yii::t('common', 'Verification Code'),
			'email'=>Yii::t('common', 'Email'),
		);
	}

	public function sendMail() {
		return Yii::app()->mailer->sendActivate(Yii::app()->controller->getUser());
	}
}
