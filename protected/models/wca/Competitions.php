<?php

/**
 * This is the model class for table "Competitions".
 *
 * The followings are the available columns in table 'Competitions':
 * @property string $id
 * @property string $name
 * @property string $cityName
 * @property string $countryId
 * @property string $information
 * @property integer $year
 * @property integer $month
 * @property integer $day
 * @property integer $endMonth
 * @property integer $endDay
 * @property string $eventSpecs
 * @property string $wcaDelegate
 * @property string $organiser
 * @property string $venue
 * @property string $venueAddress
 * @property string $venueDetails
 * @property string $website
 * @property string $cellName
 * @property integer $latitude
 * @property integer $longitude
 */
class Competitions extends ActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'Competitions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('eventSpecs', 'required'),
			array('year, month, day, endMonth, endDay, latitude, longitude', 'numerical', 'integerOnly'=>true),
			array('id', 'length', 'max'=>32),
			array('name, cityName, countryId', 'length', 'max'=>50),
			array('wcaDelegate, venue', 'length', 'max'=>240),
			array('organiser, website', 'length', 'max'=>200),
			array('venueAddress, venueDetails', 'length', 'max'=>120),
			array('cellName', 'length', 'max'=>45),
			array('information', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, cityName, countryId, information, year, month, day, endMonth, endDay, eventSpecs, wcaDelegate, organiser, venue, venueAddress, venueDetails, website, cellName, latitude, longitude', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('Competitions', 'ID'),
			'name' => Yii::t('Competitions', 'Name'),
			'cityName' => Yii::t('Competitions', 'City Name'),
			'countryId' => Yii::t('Competitions', 'Country'),
			'information' => Yii::t('Competitions', 'Information'),
			'year' => Yii::t('Competitions', 'Year'),
			'month' => Yii::t('Competitions', 'Month'),
			'day' => Yii::t('Competitions', 'Day'),
			'endMonth' => Yii::t('Competitions', 'End Month'),
			'endDay' => Yii::t('Competitions', 'End Day'),
			'eventSpecs' => Yii::t('Competitions', 'Event Specs'),
			'wcaDelegate' => Yii::t('Competitions', 'Wca Delegate'),
			'organiser' => Yii::t('Competitions', 'Organiser'),
			'venue' => Yii::t('Competitions', 'Venue'),
			'venueAddress' => Yii::t('Competitions', 'Venue Address'),
			'venueDetails' => Yii::t('Competitions', 'Venue Details'),
			'website' => Yii::t('Competitions', 'Website'),
			'cellName' => Yii::t('Competitions', 'Cell Name'),
			'latitude' => Yii::t('Competitions', 'Latitude'),
			'longitude' => Yii::t('Competitions', 'Longitude'),
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('cityName',$this->cityName,true);
		$criteria->compare('countryId',$this->countryId,true);
		$criteria->compare('information',$this->information,true);
		$criteria->compare('year',$this->year);
		$criteria->compare('month',$this->month);
		$criteria->compare('day',$this->day);
		$criteria->compare('endMonth',$this->endMonth);
		$criteria->compare('endDay',$this->endDay);
		$criteria->compare('eventSpecs',$this->eventSpecs,true);
		$criteria->compare('wcaDelegate',$this->wcaDelegate,true);
		$criteria->compare('organiser',$this->organiser,true);
		$criteria->compare('venue',$this->venue,true);
		$criteria->compare('venueAddress',$this->venueAddress,true);
		$criteria->compare('venueDetails',$this->venueDetails,true);
		$criteria->compare('website',$this->website,true);
		$criteria->compare('cellName',$this->cellName,true);
		$criteria->compare('latitude',$this->latitude);
		$criteria->compare('longitude',$this->longitude);

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
	 * @return Competitions the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
