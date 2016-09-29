<?php

/**
 * This is the model class for table "heat_schedule_user".
 *
 * The followings are the available columns in table 'heat_schedule_user':
 * @property string $id
 * @property string $heat_id
 * @property string $user_id
 * @property string $competition_id
 */
class HeatScheduleUser extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'heat_schedule_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('heat_id, user_id, competition_id', 'required'),
			array('heat_id, user_id, competition_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, heat_id, user_id, competition_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'schedule'=>[self::BELONGS_TO, 'HeatSchedule', 'heat_id'],
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'heat_id' => 'Heat',
			'user_id' => 'User',
			'competition_id' => 'Competition',
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
		$criteria->compare('heat_id', $this->heat_id, true);
		$criteria->compare('user_id', $this->user_id, true);
		$criteria->compare('competition_id', $this->competition_id, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return HeatScheduleUser the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
