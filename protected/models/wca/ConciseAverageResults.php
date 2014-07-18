<?php

/**
 * This is the model class for table "ConciseAverageResults".
 *
 * The followings are the available columns in table 'ConciseAverageResults':
 * @property string $id
 * @property integer $average
 * @property string $valueAndId
 * @property string $personId
 * @property string $eventId
 * @property string $countryId
 * @property string $continentId
 * @property integer $year
 * @property integer $month
 * @property integer $day
 */
class ConciseAverageResults extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'ConciseAverageResults';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('average, year, month, day', 'numerical', 'integerOnly'=>true),
			array('id, personId', 'length', 'max'=>10),
			array('valueAndId', 'length', 'max'=>21),
			array('eventId', 'length', 'max'=>6),
			array('countryId, continentId', 'length', 'max'=>50),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, average, valueAndId, personId, eventId, countryId, continentId, year, month, day', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('ConciseAverageResults', 'ID'),
			'average' => Yii::t('ConciseAverageResults', 'Average'),
			'valueAndId' => Yii::t('ConciseAverageResults', 'Value And'),
			'personId' => Yii::t('ConciseAverageResults', 'Person'),
			'eventId' => Yii::t('ConciseAverageResults', 'Event'),
			'countryId' => Yii::t('ConciseAverageResults', 'Country'),
			'continentId' => Yii::t('ConciseAverageResults', 'Continent'),
			'year' => Yii::t('ConciseAverageResults', 'Year'),
			'month' => Yii::t('ConciseAverageResults', 'Month'),
			'day' => Yii::t('ConciseAverageResults', 'Day'),
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('average',$this->average);
		$criteria->compare('valueAndId',$this->valueAndId,true);
		$criteria->compare('personId',$this->personId,true);
		$criteria->compare('eventId',$this->eventId,true);
		$criteria->compare('countryId',$this->countryId,true);
		$criteria->compare('continentId',$this->continentId,true);
		$criteria->compare('year',$this->year);
		$criteria->compare('month',$this->month);
		$criteria->compare('day',$this->day);

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
	 * @return ConciseAverageResults the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
