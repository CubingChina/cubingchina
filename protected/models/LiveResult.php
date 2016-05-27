<?php

/**
 * This is the model class for table "live_result".
 *
 * The followings are the available columns in table 'live_result':
 * @property string $id
 * @property string $competition_id
 * @property string $user_id
 * @property integer $user_type
 * @property integer $number
 * @property string $event
 * @property string $round
 * @property string $format
 * @property integer $best
 * @property integer $average
 * @property integer $value1
 * @property integer $value2
 * @property integer $value3
 * @property integer $value4
 * @property integer $value5
 * @property string $regional_single_record
 * @property string $regional_average_record
 * @property string $operator_id
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class LiveResult extends ActiveRecord {

	const USER_TYPE_LIVE = 1;

	public function getShowAttributes() {
		return array(
			'id'=>$this->id,
			'competition_id'=>$this->competition_id,
			'user'=>array(
				'name'=>$this->user->getCompetitionName(),
				'wcaid'=>$this->user->wcaid,
				'region'=>$this->user->country->name,
			),
			'region'=>$this->user->country->name,
			'number'=>$this->number,
			'event'=>$this->event,
			'round'=>$this->round,
			'format'=>$this->format,
			'best'=>intval($this->best),
			'average'=>intval($this->average),
			'value1'=>intval($this->value1),
			'value2'=>intval($this->value2),
			'value3'=>intval($this->value3),
			'value4'=>intval($this->value4),
			'value5'=>intval($this->value5),
			'regional_single_record'=>$this->regional_single_record,
			'regional_average_record'=>$this->regional_average_record,
		);
	}

	public function getUser() {
		return $this->user_type == self::USER_TYPE_LIVE ? $this->liveUser : $this->realUser;
	}

	public function caculateRecord($type) {
		return '';
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_result';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, user_id, number', 'required'),
			array('user_type, number, best, average, value1, value2, value3, value4, value5, status', 'numerical', 'integerOnly'=>true),
			array('competition_id, user_id, operator_id, create_time, update_time', 'length', 'max'=>10),
			array('event', 'length', 'max'=>6),
			array('round, format', 'length', 'max'=>1),
			array('regional_single_record, regional_average_record', 'length', 'max'=>3),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, user_id, user_type, number, event, round, format, best, average, value1, value2, value3, value4, value5, regional_single_record, regional_average_record, operator_id, status, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'liveUser'=>array(self::BELONGS_TO, 'LiveUser', 'user_id'),
			'realUser'=>array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'competition_id' => 'Competition',
			'user_id' => 'User',
			'user_type' => 'User Type',
			'number' => 'Number',
			'event' => 'Event',
			'round' => 'Round',
			'format' => 'Format',
			'best' => 'Best',
			'average' => 'Average',
			'value1' => 'Value1',
			'value2' => 'Value2',
			'value3' => 'Value3',
			'value4' => 'Value4',
			'value5' => 'Value5',
			'regional_single_record' => 'Regional Single Record',
			'regional_average_record' => 'Regional Average Record',
			'operator_id' => 'Operator',
			'status' => 'Status',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
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
		$criteria->compare('user_id', $this->user_id, true);
		$criteria->compare('user_type', $this->user_type);
		$criteria->compare('number', $this->number);
		$criteria->compare('event', $this->event, true);
		$criteria->compare('round', $this->round, true);
		$criteria->compare('format', $this->format, true);
		$criteria->compare('best', $this->best);
		$criteria->compare('average', $this->average);
		$criteria->compare('value1', $this->value1);
		$criteria->compare('value2', $this->value2);
		$criteria->compare('value3', $this->value3);
		$criteria->compare('value4', $this->value4);
		$criteria->compare('value5', $this->value5);
		$criteria->compare('regional_single_record', $this->regional_single_record, true);
		$criteria->compare('regional_average_record', $this->regional_average_record, true);
		$criteria->compare('operator_id', $this->operator_id, true);
		$criteria->compare('status', $this->status);
		$criteria->compare('create_time', $this->create_time, true);
		$criteria->compare('update_time', $this->update_time, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return LiveResult the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
