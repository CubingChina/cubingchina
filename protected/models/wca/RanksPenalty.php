<?php

/**
 * This is the model class for table "RanksPenalty".
 *
 * The followings are the available columns in table 'RanksPenalty':
 * @property string $id
 * @property string $eventId
 * @property string $countryId
 * @property string $type
 * @property integer $penalty
 */
class RanksPenalty extends ActiveRecord {
	private static $_panalties = [];

	public static function getPenlties($type, $countryId) {
		if (Region::isContinent($countryId)) {
			$countryId = '_' . $countryId;
		}
		if (isset(self::$_panalties[$type][$countryId])) {
			return self::$_panalties[$type][$countryId];
		}
		$ranksPenalties = self::model()->findAllByAttributes(array(
			'type'=>$type,
			'countryId'=>$countryId,
		));
		$penalties = array();
		foreach ($ranksPenalties as $ranksPenalty) {
			$penalties[$ranksPenalty->eventId] = $ranksPenalty->penalty;
		}
		//some countries dont' have penalty for some events
		//because no person attend the events
		foreach (Events::getNormalEvents() as $eventId=>$eventName) {
			if (!isset($penalties[$eventId])) {
				$penalties[$eventId] = 1;
			}
		}
		return self::$_panalties[$type][$countryId] = $penalties;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'RanksPenalty';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('penalty', 'numerical', 'integerOnly'=>true),
			array('eventId, type', 'length', 'max'=>10),
			array('countryId', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, eventId, countryId, type, penalty', 'safe', 'on'=>'search'),
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
			'eventId' => 'Event',
			'countryId' => 'Country',
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
		$criteria->compare('eventId', $this->eventId, true);
		$criteria->compare('countryId', $this->countryId, true);
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
	 * @return RanksPenalty the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
