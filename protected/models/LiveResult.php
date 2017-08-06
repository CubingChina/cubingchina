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

	public static $records = array();
	public static $liveRecords = array();

	public $pos;
	public $subEventTitle;

	public static function formatTime($result, $eventId) {
		if ($result == -1) {
			return 'DNF';
		}
		if ($result == -2) {
			return 'DNS';
		}
		if ($result == 0) {
			return '';
		}
		if ($eventId === '333fm') {
			if ($result > 1000) {
				$time = sprintf('%.2f', $result / 100);
			} else {
				$time = $result;
			}
		} elseif ($eventId === '333mbf') {
			$time = substr($result, 3, -2);
		} else {
			$msecond = substr($result, -2);
			$second = substr($result, 0, -2);
			$time = $second . '.' . $msecond;
		}
		return $time;
	}

	public function getShowAttributes($calcPos = false) {
		//use the first letter to save traffic
		$attributes = array(
			'i'=>$this->id,
			'c'=>$this->competition_id,
			// 'user'=>array(
			// 	'type'=>$this->user_type,
			// 	'id'=>$this->user_id,
			// 	'name'=>$this->user->getCompetitionName(),
			// 	'wcaid'=>$this->user->wcaid,
			// 	'region'=>$this->user->country->name,
			// ),
			// 'r'=>$this->user->country->name,
			'n'=>$this->number,
			'e'=>$this->event,
			'r'=>$this->round,
			'f'=>$this->format,
			// 'p'=>'',
			'b'=>intval($this->best),
			'a'=>intval($this->average),
			'v'=>[
				intval($this->value1),
				intval($this->value2),
				intval($this->value3),
				intval($this->value4),
				intval($this->value5),
			],
			'sr'=>$this->regional_single_record,
			'ar'=>$this->regional_average_record,
		);
		if ($calcPos) {
			$attributes['p'] = $this->getCalculatedPos();
			$attributes['nb'] = $attributes['na'] = false;
		}
		return $attributes;
	}

	public function getEvents() {
		return [$this->event];
	}

	public function getCalculatedPos() {
		if ($this->best == 0) {
			return '-';
		}
		$attributes = array(
			'competition_id'=>$this->competition_id,
			'event'=>$this->event,
			'round'=>$this->round,
		);
		$best = $this->best;
		$average = $this->average;
		$format = $this->eventRound === null ? 'a' : $this->eventRound->format;
		if ($format == 'a' || $format == 'm') {
			if ($average > 0) {
				$condition = '(average>0 AND average<:average) OR (average=:average AND best<:best)';
			} elseif ($average < 0) {
				if ($best > 0) {
					$condition = 'average>0 OR (average<0 AND best>0 AND best<:best)';
				} else {
					$condition = 'average>0 OR (average<0 AND best>0)';
				}
			} else {
				if ($best > 0) {
					$condition = 'average!=0 OR (average=0 AND best>0 AND best<:best)';
				} else {
					$condition = 'average!=0 OR (average=0 AND best>0)';
				}
			}
		} else {
			if ($best > 0) {
				$condition = 'best>0 AND best<:best';
			} else {
				$condition = 'best>0';
			}
		}
		$params = array();
		if (($format == 'a' || $format == 'm') && $average > 0) {
			$params[':average'] = $average;
			$params[':best'] = $best;
		} elseif ($best > 0) {
			$params[':best'] = $best;
		}
		return self::model()->countByAttributes($attributes, array(
			'condition'=>$condition,
			'params'=>$params,
		)) + 1;
	}

	// public function getUser() {
	// 	return $this->user_type == self::USER_TYPE_LIVE ? $this->liveUser : $this->realUser;
	// }

	public function isProbablyRecord($date) {
		foreach (['best', 'average'] as $type) {
			$NR = Results::getRecord($this->user->country->name, $this->event, $type, $date);
			if ($this->$type <= $NR[$type]) {
				return true;
			}
		}
		return false;
	}

	public function getDetail() {
		$data = $this->attributes;
		$data['eventId'] = $data['event'];
		return Results::getDisplayDetail($data);
	}

	public function getRecord($type) {
		$attribute = 'regional_' . $type . '_record';
		if ($this->$attribute) {
			$record = strtolower($this->$attribute);
			$record = in_array($record, ['nr', 'wr']) ? $record : 'cr';
			return CHtml::tag('span', ['class'=>'record record-' . $record], $this->$attribute);
		}
	}

	public function getSortClass() {
		$eventRound = $this->eventRound;
		switch ($eventRound->format) {
			case '1':
			case '2':
			case '3':
				return 'sort-by-best';
			default:
				return 'sort-by-average';
		}
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
			'user'=>array(self::BELONGS_TO, 'User', 'user_id'),
			'eventRound'=>array(self::BELONGS_TO, 'LiveEventRound', array(
				'competition_id'=>'competition_id',
				'event'=>'event',
				'round'=>'round',
			)),
			'wcaEvent'=>array(self::BELONGS_TO, 'Events', 'event'),
			'wcaRound'=>array(self::BELONGS_TO, 'RoundTypes', 'round'),
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
