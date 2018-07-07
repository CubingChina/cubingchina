<?php

class OverseaUserVerifyForm extends CFormModel {
	public $verifyCode;

	public function rules() {
		return [
			['verifyCode', 'required'],
			['verifyCode', 'captcha', 'captchaAction'=>'site/captcha'],
		];
	}

	public function attributeLabels() {
		return [
			'verifyCode'=>Yii::t('common', 'Verification Code'),
		];
	}
}
