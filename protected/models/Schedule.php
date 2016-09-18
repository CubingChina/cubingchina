<?php

/**
 * This is the model class for table "schedule".
 *
 * The followings are the available columns in table 'schedule':
 * @property integer $id
 * @property integer $competition_id
 * @property integer $day
 * @property string $stage
 * @property string $start_time
 * @property string $end_time
 * @property string $event
 * @property string $group
 * @property string $format
 * @property string $round
 * @property string $number
 * @property string $cut_off
 * @property string $time_limit
 */
class Schedule extends ActiveRecord {

	public static function getStages() {
		return array(
			'main'=>Yii::t('Schedule', 'Main Stage'),
			'side'=>Yii::t('Schedule', 'Side Stage'),
			'long'=>Yii::t('Schedule', 'Long-time Event Stage'),
			'room'=>Yii::t('Schedule', 'Classroom'),
		);
	}

	public static function getStageText($stage) {
		$stages = self::getStages();
		return isset($stages[$stage]) ? $stages[$stage] : $stage;
	}

	public static function getStagetWeight($stage) {
		return array_search($stage, array_keys(self::getStages()));
	}

	public function getTime($offset) {
		$chineseTime = $this->start_time;
		$time = $chineseTime + $offset;
		$time = date('H:i', $time);
		return $time;
	}

	public function getRealFormat() {
		$formats = explode('/', $this->format);
		$format = isset($formats[1]) ? $formats[1] : $formats[0];
		if (empty($format)) {
			$format = 'a';
		}
		return $format;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'schedule';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, start_time, end_time, event, format, round, number, cut_off, time_limit', 'required'),
			array('competition_id, day, cumulative', 'numerical', 'integerOnly'=>true),
			array('stage, start_time, end_time, group, format, round, cut_off, time_limit', 'length', 'max'=>10),
			array('event', 'length', 'max'=>32),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, day, stage, start_time, end_time, event, group, format, round, number, cut_off, time_limit', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'competition'=>array(self::BELONGS_TO, 'Competition', 'competition_id'),
			'wcaEvent'=>array(self::BELONGS_TO, 'Events', 'event'),
			'wcaRound'=>array(self::BELONGS_TO, 'Rounds', 'round'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Schedule', 'ID'),
			'competition_id' => Yii::t('Schedule', 'Competition'),
			'day' => Yii::t('Schedule', 'Day'),
			'stage' => Yii::t('Schedule', 'Stage'),
			'start_time' => Yii::t('Schedule', 'Start Time'),
			'end_time' => Yii::t('Schedule', 'End Time'),
			'event' => Yii::t('Schedule', 'Event'),
			'group' => Yii::t('Schedule', 'Group'),
			'format' => Yii::t('Schedule', 'Format'),
			'round' => Yii::t('Schedule', 'Round'),
			'number' => Yii::t('Schedule', 'Competitors'),
			'cut_off' => Yii::t('Schedule', 'Cut Off'),
			'time_limit' => Yii::t('Schedule', 'Time Limit'),
			'cumulative' => Yii::t('Schedule', 'Cumulative'),
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

		$criteria->compare('id', $this->id);
		$criteria->compare('competition_id', $this->competition_id);
		$criteria->compare('day', $this->day);
		$criteria->compare('stage', $this->stage, true);
		$criteria->compare('start_time', $this->start_time, true);
		$criteria->compare('end_time', $this->end_time, true);
		$criteria->compare('event', $this->event, true);
		$criteria->compare('group', $this->group, true);
		$criteria->compare('format', $this->format, true);
		$criteria->compare('round', $this->round, true);
		$criteria->compare('number', $this->number, true);
		$criteria->compare('cut_off', $this->cut_off, true);
		$criteria->compare('time_limit', $this->time_limit, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Schedule the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
