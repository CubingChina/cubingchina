<?php

/**
 * This is the model class for table "live_h2h_round".
 *
 * The followings are the available columns in table 'live_h2h_round':
 * @property string $id
 * @property string $competition_id
 * @property string $event
 * @property string $round
 * @property integer $places
 * @property string $stage
 * @property integer $sets_to_win
 * @property integer $points_to_win_set
 * @property integer $status
 * @property string $operator_id
 * @property string $create_time
 * @property string $update_time
 */
class LiveH2HRound extends ActiveRecord {

	const STATUS_NOT_STARTED = 0;
	const STATUS_IN_PROGRESS = 1;
	const STATUS_FINISHED = 2;

	public static function getAllStatus() {
		return array(
			self::STATUS_NOT_STARTED => Yii::t('live', 'Not Started'),
			self::STATUS_IN_PROGRESS => Yii::t('live', 'In Progress'),
			self::STATUS_FINISHED => Yii::t('live', 'Finished'),
		);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_h2h_round';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('competition_id, event, round, places', 'required'),
			array('places, sets_to_win, points_to_win_set, status', 'numerical', 'integerOnly'=>true),
			array('places', 'in', 'range'=>array(4, 8, 12, 16)),
			array('sets_to_win', 'in', 'range'=>array(1, 2, 3)),
			array('points_to_win_set', 'in', 'range'=>array(3, 4, 5)),
			array('competition_id, operator_id, create_time, update_time', 'length', 'max'=>10),
			array('event', 'length', 'max'=>32),
			array('round', 'length', 'max'=>1),
			array('stage', 'length', 'max'=>20),
			array('id, competition_id, event, round, places, stage, sets_to_win, points_to_win_set, status, operator_id, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array(
			'competition'=>array(self::BELONGS_TO, 'Competition', 'competition_id'),
			'eventRound'=>array(self::BELONGS_TO, 'LiveEventRound', array(
				'competition_id'=>'competition_id',
				'event'=>'event',
				'round'=>'round',
			)),
			'matches'=>array(self::HAS_MANY, 'LiveH2HMatch', 'h2h_round_id'),
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
			'places' => 'Places',
			'stage' => 'Stage',
			'sets_to_win' => 'Sets To Win',
			'points_to_win_set' => 'Points To Win Set',
			'status' => 'Status',
			'operator_id' => 'Operator',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
		);
	}

	/**
	 * Returns the static model of the specified AR class.
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function getBroadcastAttributes() {
		return array(
			'i'=>$this->id,
			'c'=>$this->competition_id,
			'e'=>$this->event,
			'r'=>$this->round,
			'p'=>$this->places,
			's'=>$this->stage,
			'stw'=>$this->sets_to_win,
			'ptws'=>$this->points_to_win_set,
			'st'=>$this->status,
		);
	}
}
