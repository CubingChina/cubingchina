<?php

class EditProfileForm extends CFormModel {
	public $wcaid;
	public $province_id = 0;
	public $city_id = 0;
	public $mobile = '';
	public $passport_name = '';
	public $passport_type = '';
	public $passport_number = '';
	public $repeatPassportNumber = '';

	public $coefficients = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
	public $codes = array(1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2);

	public function rules() {
		return array(
			array('wcaid', 'checkWcaId'),
			array('province_id', 'checkRegion'),
			array('mobile', 'checkMobile'),
			array('passport_name, repeatPassportNumber', 'safe'),
			array('passport_type, passport_number', 'safe'),
			array('passport_type', 'checkPassportType'),
			array('passport_number', 'checkPassportNumber'),
			array('wcaid, province_id, city_id, mobile', 'safe'),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'wcaid' => Yii::t('User', 'Wcaid'),
			'mobile' => Yii::t('User', 'Mobile'),
			'province_id' => Yii::t('User', 'Province'),
			'city_id' => Yii::t('User', 'City'),
			'passport_type' => Yii::t('Registration', 'Type of Identity'),
			'passport_name' => Yii::t('Registration', 'Name of Identity'),
			'passport_number' => Yii::t('Registration', 'Identity Number'),
			'repeatPassportNumber' => Yii::t('Registration', 'Repeat Identity Number'),
		);
	}

	public function checkWcaId() {
		$user = Yii::app()->controller->user;
		if ($user->wcaid === '' && $this->wcaid !== '') {
			$person = Persons::model()->findByAttributes(array(
				'wca_id' => $this->wcaid,
				'sub_id' => 1,
			));
			$existUser = User::model()->findByAttributes(array(
				'wcaid'=>$this->wcaid,
				'status'=>User::STATUS_NORMAL,
			));
			if ($person !== null && $existUser === null) {
				if ($user->getCompetitionName() !== $person->name && $user->name !== $person->name) {
					$this->addError('wcaid', Yii::t('common', 'Wrong WCA ID'));
				}
			} else {
				$this->addError('wcaid', Yii::t('common', 'Wrong WCA ID'));
			}
		}
	}

	public function checkRegion() {
		$user = Yii::app()->controller->user;
		if ($user->country_id == 1) {
			$province = Region::getRegionById($this->province_id);
			if ($province === null || $province->pid != $user->country_id) {
				$this->addError('province_id', Yii::t('common', 'Invalid province.'));
				return false;
			}
			$city = Region::getRegionById($this->city_id);
			if ($city === null || $city->pid != $this->province_id) {
				$this->addError('city_id', Yii::t('common', 'Invalid city.'));
				return false;
			}
		}
	}

	public function checkMobile() {
		$user = Yii::app()->controller->user;
		if ($user->country_id == 1 && !preg_match('{^1\d{10}$}', $this->mobile)) {
			$this->addError('mobile', Yii::t('common', 'Invalid mobile.'));
		}
	}

	public function checkPassportType() {
		if ($this->passport_type == User::PASSPORT_TYPE_OTHER && empty($this->passport_name)) {
			$this->addError('passport_name', Yii::t('yii','{attribute} cannot be blank.', array(
				'{attribute}'=>$this->getAttributeLabel('passport_name'),
			)));
		}
	}

	public function checkPassportNumber() {
		$user = Yii::app()->controller->user;
		switch ($this->passport_type) {
			case User::PASSPORT_TYPE_ID:
				if (!preg_match('|^\d{6}(\d{8})(\d{3})[\dX]$|i', $this->passport_number, $matches)) {
					$this->addError('passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				if (date('Ymd', $user->birthday) != $matches[1]) {
					$this->addError('passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				$sum = 0;
				for ($i = 0; $i < 17; $i++) {
					$sum += $this->passport_number[$i] * $this->coefficients[$i];
				}
				$mod = $sum % 11;
				if (strtoupper($this->passport_number[17]) != $this->codes[$mod]) {
					$this->addError('passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				break;
			case User::PASSPORT_TYPE_PASSPORT:
				if (!preg_match('|^\w+$|i', $this->passport_number, $matches)) {
					$this->addError('passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				break;
			case User::NO:
				$this->passport_number = '';
				break;
		}
		if ($user->canChangePassport() && !empty($this->passport_number) && $this->passport_number != $this->repeatPassportNumber) {
			$this->addError('repeatPassportNumber', Yii::t('common', 'Repeat identity number must be the same as identity number.'));
		}
	}

	public function update() {
		$user = Yii::app()->controller->user;
		if ($user->wcaid == '') {
			$user->wcaid = strtoupper($this->wcaid);
		}
		if ($user->canChangePassport()) {
			$user->passport_type = $this->passport_type;
			$user->passport_name = $this->passport_name;
			$user->passport_number = $this->passport_number;
		}
		$user->province_id = $this->province_id;
		$user->city_id = $this->city_id;
		$user->mobile = $this->mobile;
		return $user->save();
	}
}
