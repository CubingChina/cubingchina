<?php

/**
 * This is the model class for table "live_user".
 *
 * The followings are the available columns in table 'live_user':
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
class LiveUser extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, email, password, gender', 'required'),
			array('gender, country_id, province_id, city_id, role, identity, status', 'numerical', 'integerOnly'=>true),
			array('wcaid, avatar_id', 'length', 'max'=>10),
			array('name, name_zh, email, password', 'length', 'max'=>128),
			array('birthday, mobile', 'length', 'max'=>20),
			array('reg_time', 'length', 'max'=>11),
			array('reg_ip', 'length', 'max'=>15),
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
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'wcaid' => 'Wcaid',
			'name' => 'Name',
			'name_zh' => 'Name Zh',
			'email' => 'Email',
			'password' => 'Password',
			'avatar_id' => 'Avatar',
			'birthday' => 'Birthday',
			'gender' => 'Gender',
			'mobile' => 'Mobile',
			'country_id' => 'Country',
			'province_id' => 'Province',
			'city_id' => 'City',
			'role' => 'Role',
			'identity' => 'Identity',
			'reg_time' => 'Reg Time',
			'reg_ip' => 'Reg Ip',
			'status' => 'Status',
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

		$criteria->compare('id', $this->id, true);
		$criteria->compare('wcaid', $this->wcaid, true);
		$criteria->compare('name', $this->name, true);
		$criteria->compare('name_zh', $this->name_zh, true);
		$criteria->compare('email', $this->email, true);
		$criteria->compare('password', $this->password, true);
		$criteria->compare('avatar_id', $this->avatar_id, true);
		$criteria->compare('birthday', $this->birthday, true);
		$criteria->compare('gender', $this->gender);
		$criteria->compare('mobile', $this->mobile, true);
		$criteria->compare('country_id', $this->country_id);
		$criteria->compare('province_id', $this->province_id);
		$criteria->compare('city_id', $this->city_id);
		$criteria->compare('role', $this->role);
		$criteria->compare('identity', $this->identity);
		$criteria->compare('reg_time', $this->reg_time, true);
		$criteria->compare('reg_ip', $this->reg_ip, true);
		$criteria->compare('status', $this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return LiveUser the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
