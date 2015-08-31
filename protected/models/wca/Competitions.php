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

	public $region;
	public $event;

	public static function getResultsTypes() {
		return array(
			'winners'=>Yii::t('Competitions', 'Winners'),
			'top3'=>Yii::t('Competitions', 'Top 3'),
			'all'=>Yii::t('Competitions', 'All Results'),
		);
	}

	public static function getYears() {
		$years = array(
			'current'=>Yii::t('common', 'Current'),
		);
		$lastCompetition = self::model()->find(array(
			'order'=>'year DESC',
		));
		for ($year = $lastCompetition->year; $year >= 2003; $year--) {
			$years[$year] = $year;
		}
		$years[1982] = 1982;
		return $years;
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
			} elseif (date('d', $endDate) != date('d', $date)) {
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

	public function getCompetitionLink() {
		$competition = Statistics::getCompetition(array(
			'competitionId'=>$this->id,
			'cellName'=>$this->cellName,
		));
		return CHtml::link(ActiveRecord::getModelAttributeValue($competition, 'name'), $competition['url']);
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
			$this->_location = $this->cityName;
			if ($this->country) {
				$this->_location .= ', ' . $this->country->name;
			}
		}
		return $this->_location;
	}

	public function isInProgress() {
		$now = time();
		$date = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day));
		if ($this->endMonth > 0) {
			$endDate = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->endMonth, $this->endDay));
		} else {
			$endDate = 0;
		}
		return $now > $date && $now - 86400 < max($date, $endDate);
	}

	public function isEnded() {
		$date = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day));
		if ($this->endMonth > 0) {
			$endDate = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->endMonth, $this->endDay));
		} else {
			$endDate = 0;
		}
		return time() - 86400 > max($date, $endDate);
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

		$criteria->with = 'country';

		$criteria->compare('t.id',$this->id,true);
		// $criteria->compare('t.name',$this->name,true);
		$criteria->compare('t.cityName',$this->cityName,true);
		$criteria->compare('t.countryId',$this->countryId,true);
		$criteria->compare('t.information',$this->information,true);
		// $criteria->compare('t.year',$this->year);
		// $criteria->compare('t.month',$this->month);
		// $criteria->compare('t.day',$this->day);
		$criteria->compare('t.endMonth',$this->endMonth);
		$criteria->compare('t.endDay',$this->endDay);
		$criteria->compare('t.eventSpecs',$this->eventSpecs,true);
		$criteria->compare('t.wcaDelegate',$this->wcaDelegate,true);
		$criteria->compare('t.organiser',$this->organiser,true);
		$criteria->compare('t.venue',$this->venue,true);
		$criteria->compare('t.venueAddress',$this->venueAddress,true);
		$criteria->compare('t.venueDetails',$this->venueDetails,true);
		$criteria->compare('t.website',$this->website,true);
		$criteria->compare('t.cellName',$this->cellName,true);
		$criteria->compare('t.latitude',$this->latitude);
		$criteria->compare('t.longitude',$this->longitude);

		$pageSize = 100;
		if (in_array($this->year, self::getYears())) {
			$criteria->compare('year', $this->year);
		} elseif ($this->year === 'current') {
			$time = time() - 86400 * 90;
			$criteria->addCondition('UNIX_TIMESTAMP(CONCAT(year, "-", month, "-", day))>=' . $time);
			$pageSize = 10000;
		}
		switch ($this->region) {
			case 'World':
				break;
			case 'Africa':
			case 'Asia':
			case 'Oceania':
			case 'Europe':
			case 'North America':
			case 'South America':
				$criteria->compare('country.continentId', '_' . $this->region);
				break;
			default:
				$criteria->compare('t.countryId', $this->region);
				break;
		}
		if ($this->event && in_array($this->event, array_keys(Events::getNormalEvents()))) {
			$criteria->addCondition("eventSpecs REGEXP '[[:<:]]{$this->event}[[:>:]]'");
		}
		if ($this->name) {
			$names = explode(' ', $this->name);
			foreach ($names as $key=>$value) {
				if (trim($value) === '') {
					continue;
				}
				$paramKey = ':name' . $key;
				$criteria->addCondition("t.cellName LIKE {$paramKey} or t.cityName LIKE {$paramKey} or t.venue LIKE {$paramKey}");
				$criteria->params[$paramKey] = '%' . $value . '%';
			}
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>$pageSize,
			),
			'sort'=>array(
				'defaultOrder'=>'t.year DESC, t.month DESC, t.day DESC, t.endMonth DESC, t.endDay DESC',
			),
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
