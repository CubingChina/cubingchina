<?php

/**
 * This is the model class for table "live_event_round".
 *
 * The followings are the available columns in table 'live_event_round':
 * @property string $id
 * @property string $competition_id
 * @property string $event
 * @property string $round
 * @property string $format
 * @property string $cut_off
 * @property string $time_limit
 * @property string $number
 * @property string $operator_id
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class LiveEventRound extends ActiveRecord {

	const STATUS_OPEN = 0;
	const STATUS_FINISHED = 1;
	const STATUS_LIVE = 2;

	public static function getAllStatus() {
		return array(
			self::STATUS_OPEN=>Yii::t('live', 'Open'),
			self::STATUS_FINISHED=>Yii::t('live', 'Finished'),
			self::STATUS_LIVE=>Yii::t('live', 'Live'),
		);
	}

	public function getLastRound() {
		$rounds = self::model()->findAllByAttributes(array(
			'competition_id'=>$this->competition_id,
			'event'=>$this->event,
		));
		usort($rounds, function($roundA, $roundB) {
			return $roundA->wcaRound->rank - $roundB->wcaRound->rank;
		});
		foreach ($rounds as $key=>$round) {
			if ($round->id == $this->id && isset($rounds[$key - 1])) {
				return $rounds[$key - 1];
			}
		}
	}

	public function removeResults() {
		LiveResult::model()->deleteAllByAttributes(array(
			'competition_id'=>$this->competition_id,
			'event'=>$this->event,
			'round'=>$this->round,
		));
	}

	public function getBroadcastAttributes() {
		return array(
			'i'=>$this->round,
			'e'=>$this->event,
			'f'=>$this->format,
			'co'=>$this->cut_off,
			'tl'=>$this->time_limit,
			'n'=>$this->number,
			's'=>$this->status,
			'rn'=>$this->resultsNumber,
		);
	}

	public function getResultsNumber() {
		return LiveResult::model()->countByAttributes(array(
			'competition_id'=>$this->competition_id,
			'event'=>$this->event,
			'round'=>$this->round,
		), array(
			'condition'=>'best!=0',
		));
	}

	public function getResults() {
		if ($this->format == 'a' || $this->format == 'm') {
			$order = 'average>0 DESC, average ASC, best>0 DESC, best ASC';
		} else {
			$order = 'best>0 DESC, best ASC';
		}
		return LiveResult::model()->findAllByAttributes(array(
			'competition_id'=>$this->competition_id,
			'event'=>$this->event,
			'round'=>$this->round,
		), array(
			'order'=>$order,
			'condition'=>'best>0',
		));
	}

	public function getIsClosed() {
		return $this->status == self::STATUS_FINISHED;
	}

	public function getStatusText() {
		$allStatus = self::getAllStatus();
		return isset($allStatus[$this->status]) ? $allStatus[$this->status] : $this->status;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_event_round';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('competition_id, cut_off, time_limit, number, operator_id, create_time, update_time', 'length', 'max'=>10),
			array('event', 'length', 'max'=>6),
			array('round, format', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, event, round, format, cut_off, time_limit, number, operator_id, status, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'wcaEvent'=>array(self::BELONGS_TO, 'Events', 'event'),
			'wcaRound'=>array(self::BELONGS_TO, 'Rounds', 'round'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'competition_id' => 'Competition',
			'event' => 'Event',
			'round' => 'Round',
			'format' => 'Format',
			'cut_off' => 'Cut Off',
			'time_limit' => 'Time Limit',
			'number' => 'Number',
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
		$criteria->compare('event', $this->event, true);
		$criteria->compare('round', $this->round, true);
		$criteria->compare('format', $this->format, true);
		$criteria->compare('cut_off', $this->cut_off, true);
		$criteria->compare('time_limit', $this->time_limit, true);
		$criteria->compare('number', $this->number, true);
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
	 * @return LiveEventRound the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
