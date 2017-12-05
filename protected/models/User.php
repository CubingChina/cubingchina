<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property string $id
 * @property string $wcaid
 * @property string $name
 * @property string $name_zh
 * @property string $email
 * @property string $password
 * @property string $avatar_id
 * @property string $birthday
 * @property integer $gender
 * @property string $mobile
 * @property integer $country_id
 * @property integer $province_id
 * @property integer $city_id
 * @property integer $role
 * @property integer $identity
 * @property string $reg_time
 * @property string $reg_ip
 * @property integer $status
 */
class User extends ActiveRecord {

	const GENDER_MALE = 0;
	const GENDER_FEMALE = 1;

	const ROLE_UNCHECKED = 0;
	const ROLE_CHECKED = 1;
	const ROLE_ORGANIZER = 2;
	const ROLE_DELEGATE = 3;
	const ROLE_ADMINISTRATOR = 4;

	const IDENTITY_NONE = 0;
	const IDENTITY_WCA_DELEGATE = 1;
	const IDENTITY_CCA_DELEGATE = 2;

	const STATUS_NORMAL = 0;
	const STATUS_BANNED = 1;
	const STATUS_DELETED = 2;

	const PASSPORT_TYPE_ID = 1;
	const PASSPORT_TYPE_PASSPORT = 2;
	const PASSPORT_TYPE_OTHER = 3;

	private $_hasCerts;
	private $_preferredEvents;

	public static function getDailyUser() {
		$data = Yii::app()->db->createCommand()
			->select('FROM_UNIXTIME(MIN(reg_time), "%Y-%m-%d") as day, COUNT(1) AS user')
			->from('user')
			->where('status=' . self::STATUS_NORMAL . ' AND reg_time>=' . strtotime('today 180 days ago'))
			->group('FROM_UNIXTIME(reg_time, "%Y-%m-%d")')
			->queryAll();
		return $data;
	}

	public static function getHourlyUser() {
		$data = Yii::app()->db->createCommand()
			->select('FROM_UNIXTIME(MIN(reg_time), "%k") as hour, COUNT(1) AS user ')
			->from('user')
			->where('status=' . self::STATUS_NORMAL)
			->group('FROM_UNIXTIME(reg_time, "%k")')
			->queryAll();
		return $data;
	}

	public static function getUserRegion() {
		$data = Yii::app()->db->createCommand()
			->select('CASE WHEN r.id IS NULL THEN "海外" ELSE r.name_zh END AS label, count(1) AS value')
			->from('user u')
			->where('u.status=' . self::STATUS_NORMAL)
			->leftJoin('region r', 'u.province_id=r.id')
			->group('u.province_id')
			->queryAll();
		// $other = array(
		// 	'label'=>'其他',
		// 	'value'=>0,
		// );
		// foreach ($data as $key=>$value) {
		// 	if ($value['value'] < 3 || $value['label'] == '其他') {
		// 		$other['value'] += $value['value'];
		// 		unset($data[$key]);
		// 	}
		// }
		// $data[] = $other;
		usort($data, function($a, $b) {
			return $b['value'] - $a['value'];
		});
		return $data;
	}

	public static function getUserGender() {
		$data = Yii::app()->db->createCommand()
			->select('gender AS label, COUNT(1) AS value')
			->from('user')
			->where('status=' . self::STATUS_NORMAL)
			->group('gender')
			->queryAll();
		$genders = self::getGenders();
		foreach ($data as $key=>$value) {
			$data[$key]['label'] = isset($genders[$value['label']]) ? $genders[$value['label']] : Yii::t('common', 'Unknown');
		}
		return $data;
	}

	public static function getUserAge() {
		$data = Yii::app()->db->createCommand()
			->select('FROM_UNIXTIME(UNIX_TIMESTAMP() - birthday, "%Y") - 1969 AS age, COUNT(1) AS count')
			->from('user')
			->where('status=' . self::STATUS_NORMAL)
			->group('FROM_UNIXTIME(UNIX_TIMESTAMP() - birthday, "%Y")')
			->queryAll();
		return $data;
	}

	public static function getUserWca() {
		$data = Yii::app()->db->createCommand()
			->select('CASE WHEN wcaid="" THEN "非WCA" ELSE "WCA" END AS label, COUNT(1) AS value')
			->from('user')
			->where('status=' . self::STATUS_NORMAL)
			->group('(wcaid="")')
			->queryAll();
		return $data;
	}

	public static function getOrganizers() {
		if (Yii::app()->user->checkRole(self::ROLE_DELEGATE)) {
			$attributes = array(
				'role'=>array(
					self::ROLE_ORGANIZER,
					self::ROLE_DELEGATE,
					self::ROLE_ADMINISTRATOR,
				),
			);
		} else {
			$attributes = array(
				'id'=>Yii::app()->user->id,
			);
		}
		return self::model()->findAllByAttributes($attributes);
	}

	public static function getDelegates($identity = null) {
		if ($identity === null) {
			$identity = array(
				self::IDENTITY_WCA_DELEGATE,
				self::IDENTITY_CCA_DELEGATE,
			);
		}
		return self::model()->findAllByAttributes(array(
			'identity'=>$identity,
		));
	}

	public static function getShowDelegates() {
		return self::model()->findAllByAttributes([
			'identity'=>self::IDENTITY_WCA_DELEGATE,
			'show_as_delegate'=>self::YES,
		]);
	}

	public static function getRoles() {
		return array(
			self::ROLE_UNCHECKED=>Yii::t('common', 'Inactive User'),
			self::ROLE_CHECKED=>Yii::t('common', 'Normal User'),
			self::ROLE_ORGANIZER=>Yii::t('common', 'Organizer'),
			self::ROLE_DELEGATE=>Yii::t('common', 'Delegate'),
			self::ROLE_ADMINISTRATOR=>Yii::t('common', 'Administrator'),
		);
	}

	public static function getIdentities() {
		return array(
			self::IDENTITY_NONE=>Yii::t('common', 'None'),
			self::IDENTITY_WCA_DELEGATE=>Yii::t('common', 'WCA Delegate'),
			self::IDENTITY_CCA_DELEGATE=>Yii::t('common', 'CCA Delegate'),
		);
	}

	public static function getGenders() {
		return array(
			self::GENDER_MALE=>Yii::t('common', 'Male'),
			self::GENDER_FEMALE=>Yii::t('common', 'Female'),
		);
	}

	public static function getPassportTypes() {
		return array(
			self::PASSPORT_TYPE_ID=>Yii::t('common', 'ID Card (Chinese Citizen)'),
			self::PASSPORT_TYPE_PASSPORT=>Yii::t('common', 'Passport'),
			self::PASSPORT_TYPE_OTHER=>Yii::t('common', 'Other'),
		);
	}

	public static function getHasAvatars() {
		return array(
			'>0'=>'有',
			'0'=>'无',
		);
	}

	public function getGenderText() {
		$genders = self::getGenders();
		return isset($genders[$this->gender]) ? $genders[$this->gender] : Yii::t('common', 'Unknown');
	}

	public function getPassportTypeText() {
		$types = self::getPassportTypes();
		$text = $types[$this->passport_type] ?? $this->passport_type;
		if ($this->passport_type == self::PASSPORT_TYPE_OTHER) {
			$text .= "($this->passport_name)";
		}
		return $text;
	}

	public function canChangePassport() {
		if ($this->passport_type == self::NO) {
			return true;
		}
		$registrations = Registration::model()->with([
			'competition'=>[
				'together'=>true,
				'condition'=>'competition.fill_passport=1 AND competition.date>UNIX_TIMESTAMP()',
			],
		])->findAllByAttributes([
			'user_id'=>$this->id,
		]);
		if ($registrations == []) {
			return true;
		}
		return false;
	}

	public function isUnchecked() {
		return $this->role == self::ROLE_UNCHECKED;
	}

	public function isOrganizer() {
		return $this->role == self::ROLE_ORGANIZER;
	}

	public function isAdministrator() {
		return $this->role == self::ROLE_ADMINISTRATOR;
	}

	public function isDelegate() {
		return $this->identity != self::IDENTITY_NONE;
	}

	public function isWCADelegate() {
		return $this->identity == self::IDENTITY_WCA_DELEGATE;
	}

	public function isBanned() {
		return $this->status != self::STATUS_NORMAL;
	}

	public function isGreaterChinese() {
		return $this->country_id <= 4;
	}

	public function hasPermission($permission) {
		return in_array($permission, CHtml::listData($this->permissions, 'id', 'permission'));
	}

	public function hasSuccessfulRegistration() {
		return Registration::model()->countByAttributes([
			'user_id'=>$this->id,
			'status'=>Registration::STATUS_ACCEPTED,
		]) > 0;
	}

	public function getPreferredEvents() {
		if ($this->_preferredEvents == null) {
			$this->_preferredEvents = PreferredEvent::getUserEvents($this);
		}
		return $this->_preferredEvents;
	}

	public function setPreferredEvents($events) {
		$this->_preferredEvents = $events;
	}

	public function getHasCerts() {
		if ($this->_hasCerts !== null) {
			return $this->_hasCerts;
		}
		if ($this->wcaid == '') {
			return $this->_hasCerts = false;
		}
		$competitions = Competition::model()->cache(86400)->findAllByAttributes([
			'type'=>Competition::TYPE_WCA,
			'status'=>Competition::STATUS_SHOW,
		], [
			'condition'=>'cert_name!=""',
		]);
		if ($competitions === []) {
			return $this->_hasCerts = false;
		}
		$wcaIds = CHtml::listData($competitions, 'id', 'wca_competition_id');
		return $this->_hasCerts = (Results::model()->countByAttributes([
			'competitionId'=>$wcaIds,
			'personId'=>$this->wcaid,
		]) > 0);
	}

	public function getRoleName() {
		$roles = self::getRoles();
		return isset($roles[$this->role]) ? $roles[$this->role] : Yii::t('common', 'Unknown');
	}

	public function getIdentityName() {
		$identities = self::getIdentities();
		return isset($identities[$this->identity]) ? $identities[$this->identity] : Yii::t('common', 'Unknown');
	}

	public function getCompetitionName() {
		$name = $this->name;
		if ($this->name_zh != '') {
			$name .= " ({$this->name_zh})";
		}
		return $name;
	}

	public function getWcaGender() {
		return $this->gender == self::GENDER_FEMALE ? 'f' : 'm';
	}

	public function getWcaLink($name = null) {
		if ($name === null) {
			$name = $this->getCompetitionName();
		}
		if ($this->wcaid === '' || $name === '') {
			return $name;
		}
		return Persons::getLinkByNameNId($name, $this->wcaid);
	}

	public function getEmailLink() {
		return CHtml::mailto($this->email, $this->email);
	}

	public function getOperationButton() {
		$buttons = array();
		$buttons[] = CHtml::tag('button', array(
			'class'=>'btn btn-xs btn-blue btn-square js-user-registration',
			'data-id'=>$this->id,
		), '报名管理');
		$buttonGroups = array();
		$buttonGroups[] = '<div class="btn-group">';
		$buttonGroups[] = CHtml::tag(
			'button',
			array(
				'class'=>'btn btn-default btn-square btn-xs dropdown-toggle',
				'type'=>'button',
				'data-toggle'=>'dropdown',
			),
			'更多 <span class="caret"></span>'
		);
		$buttonGroups[] = '<ul class="dropdown-menu" role="menu">';
		$buttonGroups[] = CHtml::tag('li', array(), CHtml::link('编辑', array('/board/user/edit', 'id'=>$this->id)));
		switch ($this->status) {
			case self::STATUS_BANNED:
				$buttonGroups[] = CHtml::tag('li', array(), CHtml::link('洗白', array('/board/user/enable', 'id'=>$this->id)));
				break;
			case self::STATUS_NORMAL:
				$buttonGroups[] = CHtml::tag('li', array(), CHtml::link('拉黑', array('/board/user/disable', 'id'=>$this->id)));
				$buttonGroups[] = CHtml::tag('li', array(), CHtml::link('删除', array('/board/user/delete', 'id'=>$this->id), array('class'=>'delete')));
				break;
			// case self::STATUS_DELETED:
			// 	$buttons[] = CHtml::link('恢复', array('/board/user/enable', 'id'=>$this->id), array('class'=>'btn btn-xs btn-purple btn-square'));
			// 	break;
		}
		$buttonGroups[] = CHtml::tag('li', array(
			'class'=>'js-user-login-history',
			'data-id'=>$this->id,
		), CHtml::link('登录记录', '#'));
		$buttonGroups[] = '</ul>';
		$buttonGroups[] = '</div>';
		$buttons[] = implode("\n", $buttonGroups);
		return implode(' ', $buttons);
	}

	public function getRegionName($region) {
		return $region === null ? '' : $region->getAttributeValue('name');
	}

	public function handleDate() {
		foreach (array('birthday') as $attribute) {
			if ($this->$attribute != '') {
				$date = strtotime($this->$attribute);
				if ($date !== false) {
					$this->$attribute = $date;
				} else {
					$this->$attribute = 0;
				}
			} else {
				$this->$attribute = 0;
			}
		}
	}

	public function formatDate() {
		foreach (array('birthday') as $attribute) {
			if (!empty($this->$attribute)) {
				$this->$attribute = date('Y-m-d', $this->$attribute);
			} else {
				$this->$attribute = '';
			}
		}
	}

	public function getMailUrl($action) {
		$userAction = new UserAction();
		$userAction->user_id = $this->id;
		$userAction->action = $action;
		$userAction->date = time();
		$userAction->code = $userAction->generateCode();
		$userAction->save();
		switch ($action) {
			default:
				$url = Yii::app()->createUrl('/site/' . $action, array('c'=>$userAction->code));
				break;
		}
		return $url;
	}

	public function getAvatarList() {
		$avatars = $this->avatars;
		$avatarList = array(
			0=>'无',
		);
		foreach ($avatars as $avatar) {
			$avatarList[$avatar->id] = CHtml::image($avatar->fullUrl, '', array(
				'class'=>'user-avatar img-thumbnail',
			));
		}
		return $avatarList;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, country_id, birthday, email, password, gender', 'required'),
			array('gender, country_id, province_id, city_id, role, identity, status, passport_type, show_as_delegate', 'numerical', 'integerOnly'=>true),
			array('wcaid, avatar_id', 'length', 'max'=>10),
			array('name, name_zh, email, password, passport_name, passport_number', 'length', 'max'=>128),
			array('birthday, mobile', 'length', 'max'=>20),
			array('reg_time', 'length', 'max'=>11),
			array('reg_ip', 'length', 'max'=>15),
			['preferredEvents', 'safe'],
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, wcaid, name, name_zh, email, password, avatar_id, birthday, gender, mobile, country_id, province_id, city_id, role, identity, reg_time, reg_ip, status', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
			'country'=>[self::BELONGS_TO, 'Region', 'country_id'],
			'province'=>[self::BELONGS_TO, 'Region', 'province_id'],
			'city'=>[self::BELONGS_TO, 'Region', 'city_id'],
			'avatar'=>[self::BELONGS_TO, 'UserAvatar', 'avatar_id'],
			'avatars'=>[self::HAS_MANY, 'UserAvatar', 'user_id'],
			'permissions'=>[self::HAS_MANY, 'UserPermission', 'user_id'],
			'wechatUser'=>[self::HAS_ONE, 'WechatUser', 'user_id'],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('User', 'ID'),
			'wcaid' => Yii::t('User', 'Wcaid'),
			'name' => Yii::t('User', 'Name'),
			'name_zh' => Yii::t('User', 'Local Name'),
			'email' => Yii::t('User', 'Email'),
			'password' => Yii::t('User', 'Password'),
			'avatar_id' => Yii::t('User', 'Avatar'),
			'birthday' => Yii::t('User', 'Birthday'),
			'gender' => Yii::t('User', 'Gender'),
			'mobile' => Yii::t('User', 'Mobile'),
			'country_id' => Yii::t('User', 'Region'),
			'province_id' => Yii::t('User', 'Province'),
			'city_id' => Yii::t('User', 'City'),
			'role' => Yii::t('User', 'Role'),
			'identity' => Yii::t('User', 'Identity'),
			'reg_time' => Yii::t('User', 'Reg Time'),
			'reg_ip' => Yii::t('User', 'Reg Ip'),
			'status' => Yii::t('User', 'Status'),
			'preferredEvents' => Yii::t('common', 'Preferred Events'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search() {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;

		$criteria->with = array('country', 'province', 'city');

		$criteria->compare('t.id', $this->id);
		$criteria->compare('t.wcaid', $this->wcaid, true);
		$criteria->compare('t.name', $this->name, true);
		$criteria->compare('t.name_zh', $this->name_zh, true);
		$criteria->compare('t.email', $this->email, true);
		$criteria->compare('t.password', $this->password, true);
		$criteria->compare('t.avatar_id', $this->avatar_id, true);
		$criteria->compare('t.gender', $this->gender);
		$criteria->compare('t.mobile', $this->mobile, true);
		$criteria->compare('t.country_id', $this->country_id);
		$criteria->compare('t.province_id', $this->province_id);
		$criteria->compare('t.city_id', $this->city_id);
		$criteria->compare('t.role', $this->role);
		$criteria->compare('t.identity', $this->identity);
		$criteria->compare('t.reg_time', $this->reg_time, true);
		$criteria->compare('t.reg_ip', $this->reg_ip, true);
		$criteria->compare('t.status', $this->status);
		foreach (['reg_time', 'birthday'] as $attribute) {
			$value = $this->$attribute;
			if (is_array($value)) {
				if (isset($value[0]) && ($temp = strtotime($value[0])) !== false) {
					$criteria->compare('t.' . $attribute, '>=' . $temp);
				}
				if (isset($value[1]) && ($temp = strtotime($value[1])) !== false) {
					$criteria->compare('t.' . $attribute, '<=' . $temp);
				}
			}
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'t.reg_time DESC',
			),
			'pagination'=>array(
				'pageSize'=>50,
			),
		));
	}

	public function searchRepeat() {
		$repeatedUsers = $this->findAllByAttributes([
			'status'=>self::STATUS_NORMAL,
		], [
			'select'=>'GROUP_CONCAT(id) AS id',
			'condition'=>'name_zh != ""',
			'group'=>'name_zh, birthday',
			'having'=>'count(DISTINCT id) > 1',
		]);

		foreach ($repeatedUsers as $user) {
			foreach (explode(',', $user->id) as $id) {
				$ids[] = $id;
			}
		}
		$criteria = new CDbCriteria;
		$criteria->addInCondition('id', $ids);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'name_zh',
			),
			'pagination'=>array(
				'pageSize'=>50,
			),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
