<?php

class EditProfileForm extends CFormModel {
	public $wcaid;
	public $province_id = 0;
	public $city_id = 0;
	public $mobile = '';

	public function rules() {
		return array(
			array('wcaid', 'checkWcaId'),
			array('province_id', 'checkRegion'),
			array('mobile', 'checkMobile'),
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
		);
	}

	public function checkWcaId() {
		$user = Yii::app()->controller->user;
		if ($user->wcaid === '' && $this->wcaid !== '') {
			$person = Persons::model()->findByAttributes(array(
				'id' => $this->wcaid,
				'subid' => 1,
			));
			if ($person !== null) {
				$name = $user->name;
				if ($user->name_zh !== '') {
					$name .= ' (' . $user->name_zh . ')';
				}
				if ($name !== $person->name && $user->name !== $person->name) {
					$this->addError('wcaid', Yii::t('common', 'Wrong WCA ID'));
				}
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
		if ($user->country_id == 1 && !preg_match('{^1[34578]\d{9}$}', $this->mobile)) {
			$this->addError('mobile', Yii::t('common', 'Invalid mobile.'));
		}
	}

	public function update() {
		$user = Yii::app()->controller->user;
		if ($user->wcaid == '') {
			$user->wcaid = $this->wcaid;
		}
		$user->province_id = $this->province_id;
		$user->city_id = $this->city_id;
		$user->mobile = $this->mobile;
		return $user->save();
	}

}