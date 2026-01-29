<?php

/**
 * This is the model class for table "ranks_penalty".
 *
 * The followings are the available columns in table 'ranks_penalty':
 * @property string $id
 * @property string $event_id
 * @property string $country_id
 * @property string $type
 * @property integer $penalty
 */
class RanksPenalty extends ActiveRecord {
	private static $_panalties = [];

	public static function getPenlties($type, $country_id) {
		if (Region::isContinent($country_id)) {
			$country_id = '_' . $country_id;
		}
		if (isset(self::$_panalties[$type][$country_id])) {
			return self::$_panalties[$type][$country_id];
		}
		$ranksPenalties = self::model()->findAllByAttributes(array(
			'type'=>$type,
			'country_id'=>$country_id,
		));
		$penalties = array();
		foreach ($ranksPenalties as $ranksPenalty) {
			$penalties[$ranksPenalty->event_id] = $ranksPenalty->penalty;
		}
		//some countries dont' have penalty for some events
		//because no person attend the events
		foreach (Events::getNormalEvents() as $event_id=>$eventName) {
			if (!isset($penalties[$event_id])) {
				$penalties[$event_id] = 1;
			}
		}
		return self::$_panalties[$type][$country_id] = $penalties;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'ranks_penalty';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('penalty', 'numerical', 'integerOnly'=>true),
			array('event_id, type', 'length', 'max'=>10),
			array('country_id', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, event_id, country_id, type, penalty', 'safe', 'on'=>'search'),
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
			'event_id' => 'Event',
			'country_id' => 'Country',
			'type' => 'Type',
			'penalty' => 'Penalty',
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
		$criteria->compare('event_id', $this->event_id, true);
		$criteria->compare('country_id', $this->country_id, true);
		$criteria->compare('type', $this->type, true);
		$criteria->compare('penalty', $this->penalty);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @return CDbConnection the database connection used for this class
	 */
	public function getDbConnection() {
		return Yii::app()->wcaDb;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ranks_penalty the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
