<?php

/**
 * This is the model class for table "live_user".
 *
 * The followings are the available columns in table 'live_user':
 * @property string $id
 * @property string $wcaid
 * @property string $name
 * @property string $name_zh
 * @property string $birthday
 * @property integer $gender
 * @property integer $country_id
 * @property integer $province_id
 * @property integer $city_id
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
			array('name, gender', 'required'),
			array('gender, country_id, province_id, city_id, status', 'numerical', 'integerOnly'=>true),
			array('wcaid', 'length', 'max'=>10),
			array('name, name_zh', 'length', 'max'=>128),
			array('birthday', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, wcaid, name, name_zh, birthday, gender, country_id, province_id, city_id, status', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'country'=>array(self::BELONGS_TO, 'Region', 'country_id'),
			'province'=>array(self::BELONGS_TO, 'Region', 'province_id'),
			'city'=>array(self::BELONGS_TO, 'Region', 'city_id'),
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
			'birthday' => 'Birthday',
			'gender' => 'Gender',
			'country_id' => 'Country',
			'province_id' => 'Province',
			'city_id' => 'City',
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
		$criteria->compare('birthday', $this->birthday, true);
		$criteria->compare('gender', $this->gender);
		$criteria->compare('country_id', $this->country_id);
		$criteria->compare('province_id', $this->province_id);
		$criteria->compare('city_id', $this->city_id);
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
