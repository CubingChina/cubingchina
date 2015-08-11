<?php

/**
 * This is the model class for table "login_history".
 *
 * The followings are the available columns in table 'login_history':
 * @property string $id
 * @property string $user_id
 * @property string $ip
 * @property string $date
 * @property integer $from_cookie
 */
class LoginHistory extends ActiveRecord {
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return LoginHistory the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'login_history';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, date', 'required'),
			array('from_cookie', 'numerical', 'integerOnly'=>true),
			array('user_id, date', 'length', 'max'=>10),
			array('ip', 'length', 'max'=>15),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, ip, date, from_cookie', 'safe', 'on'=>'search'),
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
			'id'=>'ID',
			'user_id'=>'User',
			'ip'=>'Ip',
			'date'=>'Date',
			'from_cookie'=>'From Cookie',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search() {
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('user_id', $this->user_id);
		$criteria->compare('ip', $this->ip, true);
		$criteria->compare('date', $this->date, true);
		$criteria->compare('from_cookie', $this->from_cookie);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}