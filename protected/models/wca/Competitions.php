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
	//粗饼比赛
	public $c;
	private $_location;

	public static function getResultsTypes() {
		return array(
			'winners'=>Yii::t('Competitions', 'Winners'),
			'top3'=>Yii::t('Competitions', 'Top 3'),
			'all'=>Yii::t('Competitions', 'All Results'),
		);
	}

	public static function getResults($id) {
		//比赛成绩
		$winners = Results::model()->with(array(
			'person',
			'person.country',
			'round',
			'event',
			'format',
		))->findAllByAttributes(array(
			'competitionId'=>$id,
			'pos'=>1,
			'roundId'=>array('c', 'f'),
		), array(
			'order'=>'event.rank, round.rank, t.pos'
		));
		$top3 = Results::model()->with(array(
			'person',
			'person.country',
			'round',
			'event',
			'format',
		))->findAllByAttributes(array(
			'competitionId'=>$id,
			'pos'=>array(1, 2, 3),
			'roundId'=>array('c', 'f'),
		), array(
			'order'=>'event.rank, round.rank, t.pos'
		));
		$all = Results::model()->with(array(
			'person',
			'person.country',
			'round',
			'event',
			'format',
		))->findAllByAttributes(array(
			'competitionId'=>$id,
		), array(
			'order'=>'event.rank, round.rank, t.pos'
		));
		return array(
			'winners'=>$winners,
			'top3'=>$top3,
			'all'=>$all,
		);
	}

	public static function getDisplayDate($date, $endDate) {
		$displayDate = date("Y-m-d", $date);
		if ($endDate > 0) {
			if (date('Y', $endDate) != date('Y', $date)) {
				$displayDate .= date('~Y-m-d', $endDate);
			} elseif (date('m', $endDate) != date('m', $date)) {
				$displayDate .= date('~m-d', $endDate);
			} else {
				$displayDate .= date('~d', $endDate);
			}
		}
		return $displayDate;
	}

	public static function getWcaUrl($id) {
		return 'http://www.worldcubeassociation.org/results/c.php?i=' . $id;
	}

	public function getLinks() {
		if ($this->c) {
			$links[] = CHtml::link(CHtml::image('/f/images/icon64.png', $this->name, array('class'=>'wca-competition')), $this->c->url);
		}
		$links[] = $this->getWcaLink();
		return implode(' ', $links);
	}

	public function getWcaLink() {
		return CHtml::link(CHtml::image('/f/images/wca.png', $this->name, array('class'=>'wca-competition')), self::getWcaUrl($this->id), array('target'=>'_blank'));
	}

	public function getDate() {
		$date = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day));
		if ($this->endMonth > 0) {
			$endDate = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->endMonth, $this->endDay));
		} else {
			$endDate = 0;
		}
		return self::getDisplayDate($date, $endDate);
	}

	public function setLocation($location) {
		$this->_location = $location;
	}

	public function getLocation() {
		if ($this->_location === null) {
			$this->_location = $this->cityName . ', ' . $this->country->name;
		}
		return $this->_location;
	}

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
			'country'=>array(self::BELONGS_TO, 'Countries', 'countryId'),
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
