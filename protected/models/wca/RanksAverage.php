<?php

/**
 * This is the model class for table "RanksAverage".
 *
 * The followings are the available columns in table 'RanksAverage':
 * @property integer $id
 * @property string $personId
 * @property string $eventId
 * @property integer $best
 * @property integer $worldRank
 * @property integer $continentRank
 * @property integer $countryRank
 */
class RanksAverage extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'RanksAverage';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('best, worldRank, continentRank, countryRank', 'numerical', 'integerOnly'=>true),
			array('personId', 'length', 'max'=>10),
			array('eventId', 'length', 'max'=>6),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, personId, eventId, best, worldRank, continentRank, countryRank', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'person'=>array(self::BELONGS_TO, 'Persons', 'personId', 'on'=>'person.subid=1'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('RanksAverage', 'ID'),
			'personId' => Yii::t('RanksAverage', 'Person'),
			'eventId' => Yii::t('RanksAverage', 'Event'),
			'best' => Yii::t('RanksAverage', 'Best'),
			'worldRank' => Yii::t('RanksAverage', 'World Rank'),
			'continentRank' => Yii::t('RanksAverage', 'Continent Rank'),
			'countryRank' => Yii::t('RanksAverage', 'Country Rank'),
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

		$criteria->compare('id',$this->id);
		$criteria->compare('personId',$this->personId,true);
		$criteria->compare('eventId',$this->eventId,true);
		$criteria->compare('best',$this->best);
		$criteria->compare('worldRank',$this->worldRank);
		$criteria->compare('continentRank',$this->continentRank);
		$criteria->compare('countryRank',$this->countryRank);

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
	 * @return RanksAverage the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
