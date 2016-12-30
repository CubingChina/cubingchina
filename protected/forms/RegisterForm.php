<?php

class RegisterForm extends CFormModel {
	public $step = 1;
	public $wcaid;
	public $name;
	public $local_name;
	public $gender;
	public $birthday;
	public $country_id;
	public $province_id = 0;
	public $city_id = 0;
	public $mobile = '';
	public $email;
	public $password;
	public $repeatPassword;
	public $verifyCode;

	public static $dateFormat = 'Y-m-d';

	const REGISTER_WCAID = 'registerWCAID';

	/**
	 * Declares the validation rules.
	 * The rules state that email and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules() {
		return array(
			array('gender, country_id, birthday, name, email, password, repeatPassword, verifyCode', 'required', 'on'=>'step2'),
			array('local_name, province_id, city_id', 'safe', 'on'=>'step2'),
			array('email', 'email'),
			array('email', 'match', 'pattern'=>'{^www\..+@.+$}i', 'not'=>true),
			array('email', 'match', 'pattern'=>'{@pp\.com$}i', 'not'=>true),
			array('email', 'match', 'pattern'=>'{\.con$}i', 'not'=>true),
			array('email', 'match', 'pattern'=>'{qq\.com\.cn$}i', 'not'=>true),
			array('wcaid', 'checkWcaId'),
			array('birthday', 'checkBirthday', 'on'=>'step2'),
			array('name', 'checkName', 'on'=>'step2'),
			array('country_id', 'checkRegion', 'on'=>'step2'),
			array('mobile', 'checkMobile', 'on'=>'step2'),
			array('gender', 'checkGender', 'on'=>'step2'),
			array('email', 'unique', 'className'=>'User', 'attributeName'=>'email'),
			array('password', 'length', 'min'=>6),
			array('repeatPassword', 'compare', 'compareAttribute'=>'password'),
			array('verifyCode', 'captcha', 'on'=>'step2'),
		);
	}

	public function isLastStep() {
		return $this->step === 2;
	}

	public function loadData() {
		$session = Yii::app()->session;
		switch ($this->step) {
			case 1:
				break;
			case 2:
				$wcaid = $session->get(self::REGISTER_WCAID, '');
				if ($wcaid !== '') {
					$this->wcaid = $wcaid;
					$person = Persons::model()->findByAttributes(array(
						'id'=>$wcaid,
					));
					preg_match('{^([^(]+)(.*\(([^)]+)\))?$}iu', $person->name, $matches);
					if ($matches !== array()) {
						$this->name = trim($matches[1]);
						$this->local_name = isset($matches[3]) ? trim($matches[3]) : '';
					} else {
						$this->name = $person->name;
					}
					$genders = array(
						''=>'',
						'm'=>User::GENDER_MALE,
						'f'=>User::GENDER_FEMALE,
					);
					$this->gender = $genders[strtolower($person->gender)];
					$this->country_id = Region::getRegionIdByName($person->countryId);
				}
				break;
			case 3:
				break;
		}
	}

	public function checkName() {
		if ($this->country_id == 1) {
			$user = User::model()->findByAttributes(array(
				'name_zh'=>$this->local_name,
				'birthday'=>$this->birthday,
				'status'=>User::STATUS_NORMAL,
			), array(
				'condition'=>'role!=' . User::ROLE_UNCHECKED,
			));
			if ($user !== null) {
				$this->addError('local_name', Yii::t('common', 'Please <b>DO NOT</b> repeat registration!'));
			}
		}
	}

	public function checkWcaId() {
		$this->wcaid = strtoupper($this->wcaid);
		if ($this->wcaid !== '') {
			switch ($this->step) {
				case 1:
					$person = Persons::model()->findByAttributes(array(
						'id'=>$this->wcaid,
					));
					if ($person === null) {
						$this->addError('wcaid', Yii::t('common', 'Wrong WCA ID'));
						return false;
					}
					Yii::app()->session->add(self::REGISTER_WCAID, $this->wcaid);
				case 2:
					$user = User::model()->findByAttributes(array(
						'wcaid'=>$this->wcaid,
						'status'=>User::STATUS_NORMAL,
					));
					if ($user !== null) {
						$this->addError('wcaid', Yii::t('common', 'The WCA ID {wcaid} has been registered.', array(
							'{wcaid}'=>$this->wcaid,
						)));
						return false;
					}
					break;
			}
		}
	}

	public function checkBirthday() {
		$this->birthday = strtotime($this->birthday);
		if ($this->birthday === false) {
			$this->addError('birthday', Yii::t('common', 'Invalid birthday format'));
			return false;
		}
		if ($this->birthday < strtotime('today -120 years')) {
			$this->addError('birthday', Yii::t('common', 'Please re-check your date of birth and ensure the consistency of ID cards.'));
			return false;
		}
		if ($this->birthday > strtotime('today -1 year')) {
			$this->addError('birthday', Yii::t('common', 'Please re-check your date of birth and ensure the consistency of ID cards.'));
			return false;
		}
	}

	public function checkRegion() {
		$region = Region::getRegionById($this->country_id);
		if ($region === null) {
			$this->addError('country_id', Yii::t('common', 'Invalid region.'));
			return false;
		}
		if ($this->country_id == 1) {
			if (!preg_match('{^[\x{4e00}-\x{9fc0}]+$}u', $this->local_name)) {
				$this->addError('local_name', Yii::t('common', 'Invalid local name.'));
				return false;
			}
			$province = Region::getRegionById($this->province_id);
			if ($province === null || $province->pid != $this->country_id) {
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

	public function checkGender() {
		$genders = User::getGenders();
		if (!array_key_exists($this->gender, $genders)) {
			$this->addError('gender', Yii::t('common', 'Invalid gender.'));
		}
	}

	public function checkMobile() {
		if ($this->country_id == 1 && !preg_match('{^1[34578]\d{9}$}', $this->mobile)) {
			$this->addError('mobile', Yii::t('common', 'Invalid mobile.'));
		}
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels() {
		return array(
			'wcaid'=>Yii::t('common', 'WCA ID'),
			'name'=>Yii::t('common', 'Name'),
			'local_name'=>Yii::t('common', 'Name in Local Characters (for Chinese, Japanese, Korean, etc)'),
			'gender'=>Yii::t('common', 'Gender'),
			'birthday'=>Yii::t('common', 'Birthday'),
			'country_id'=>Yii::t('common', 'Region'),
			'province_id'=>Yii::t('common', 'Province'),
			'city_id'=>Yii::t('common', 'City'),
			'mobile'=>Yii::t('common', 'Mobile Number'),
			'email'=>Yii::t('common', 'Email'),
			'password'=>Yii::t('common', 'Password'),
			'repeatPassword'=>Yii::t('common', 'Repeat Password'),
			'verifyCode'=>Yii::t('common', 'Verify Code'),
		);
	}

	public function register() {
		$user = new User();
		$user->wcaid = strtoupper($this->wcaid);
		$user->email = strtolower($this->email);
		$user->password = CPasswordHelper::hashPassword($this->password);
		$user->name = trim(strip_tags($this->name));
		$user->name_zh = trim(strip_tags($this->local_name));
		$user->gender = $this->gender;
		$user->mobile = $this->mobile;
		$user->birthday = $this->birthday;
		$user->country_id = $this->country_id;
		$user->province_id = $this->province_id;
		$user->city_id = $this->city_id;
		$user->role = User::ROLE_UNCHECKED;
		$user->reg_time = time();
		$user->reg_ip = Yii::app()->request->getUserHostAddress();
		$recentUserCount = User::model()->countByAttributes(array(
			'reg_ip'=>$user->reg_ip,
		), 'reg_time > :reg_time', array(
			'reg_time'=>time() - 86400 * 7,
		));
		if ($user->save()) {
			$identity = new UserIdentity($this->email,$this->password);
			$identity->id = $user->id;
			Yii::app()->user->login($identity);
			Yii::app()->mailer->sendActivate($user);
			return true;
		} else {
			return false;
		}
	}
}
