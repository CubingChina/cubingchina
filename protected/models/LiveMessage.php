<?php

/**
 * This is the model class for table "live_message".
 *
 * The followings are the available columns in table 'live_message':
 * @property string $id
 * @property string $competition_id
 * @property string $user_id
 * @property string $event
 * @property string $round
 * @property string $content
 * @property string $create_time
 */
class LiveMessage extends ActiveRecord {

	public function getShowAttributes() {
		return array(
			'id'=>$this->id,
			'competitionId'=>$this->competition_id,
			'user'=>array(
				'name'=>$this->user->getCompetitionName(),
				'wcaid'=>$this->user->wcaid,
			),
			'event'=>$this->event,
			'round'=>$this->round,
			'content'=>$this->content,
			'time'=>intval($this->create_time),
		);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_message';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, user_id, content', 'required'),
			array('competition_id, user_id, create_time', 'length', 'max'=>10),
			array('event', 'length', 'max'=>6),
			array('round', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, user_id, event, round, content, create_time', 'safe', 'on'=>'search'),
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
			'event' => 'Event',
			'round' => 'Round',
			'content' => 'Content',
			'create_time' => 'Create Time',
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
		$criteria->compare('event', $this->event, true);
		$criteria->compare('round', $this->round, true);
		$criteria->compare('content', $this->content, true);
		$criteria->compare('create_time', $this->create_time, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return LiveMessage the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
