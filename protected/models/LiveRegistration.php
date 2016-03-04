<?php

/**
 * This is the model class for table "live_registration".
 *
 * The followings are the available columns in table 'live_registration':
 * @property string $id
 * @property string $competition_id
 * @property integer $location_id
 * @property string $user_id
 * @property string $events
 * @property integer $total_fee
 * @property string $comments
 * @property integer $paid
 * @property string $ip
 * @property string $date
 * @property integer $status
 */
class LiveRegistration extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_registration';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, user_id, events, date', 'required'),
			array('location_id, total_fee, paid, status', 'numerical', 'integerOnly'=>true),
			array('competition_id, user_id, date', 'length', 'max'=>10),
			array('events', 'length', 'max'=>512),
			array('comments', 'length', 'max'=>2048),
			array('ip', 'length', 'max'=>15),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, location_id, user_id, events, total_fee, comments, paid, ip, date, status', 'safe', 'on'=>'search'),
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
			'competition_id' => 'Competition',
			'location_id' => 'Location',
			'user_id' => 'User',
			'events' => 'Events',
			'total_fee' => 'Total Fee',
			'comments' => 'Comments',
			'paid' => 'Paid',
			'ip' => 'Ip',
			'date' => 'Date',
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
		$criteria->compare('competition_id', $this->competition_id, true);
		$criteria->compare('location_id', $this->location_id);
		$criteria->compare('user_id', $this->user_id, true);
		$criteria->compare('events', $this->events, true);
		$criteria->compare('total_fee', $this->total_fee);
		$criteria->compare('comments', $this->comments, true);
		$criteria->compare('paid', $this->paid);
		$criteria->compare('ip', $this->ip, true);
		$criteria->compare('date', $this->date, true);
		$criteria->compare('status', $this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return LiveRegistration the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
