<?php

/**
 * This is the model class for table "competitions".
 *
 * The followings are the available columns in table 'competitions':
 * @property string $id
 * @property string $name
 * @property string $city_name
 * @property string $country_id
 * @property string $information
 * @property integer $year
 * @property integer $month
 * @property integer $day
 * @property integer $end_year
 * @property integer $end_month
 * @property integer $end_day
 * @property string $event_specs
 * @property string $delegates
 * @property string $organizers
 * @property string $venue
 * @property string $venue_address
 * @property string $venue_details
 * @property string $external_website
 * @property string $cell_name
 * @property integer $latitude_microdegrees
 * @property integer $longitude_microdegrees
 */
class Competitions extends ActiveRecord {
	//粗饼比赛
	public $c;
	private $_location;

	public $region;
	public $event;
	public $number;

	public static function getResultsTypes() {
		return array(
			'winners'=>Yii::t('competitions', 'Winners'),
			'top3'=>Yii::t('competitions', 'Top 3'),
			'all'=>Yii::t('competitions', 'All results'),
			'byPerson'=>Yii::t('competitions', 'By Person'),
			'records'=>Yii::t('common', 'Records'),
			'scrambles'=>Yii::t('competitions', 'scrambles'),
		);
	}

	public static function getYears($current = true) {
		$years = [];
		if ($current) {
			$years['current'] = Yii::t('common', 'Current');
		}
		$lastCompetition = self::model()->find(array(
			'order'=>'year DESC',
		));
		for ($year = $lastCompetition->year; $year >= 2003; $year--) {
			$years[$year] = $year;
		}
		$years[1982] = 1982;
		return $years;
	}

	public function getResults($id) {
		//比赛成绩
		$winners = Results::model()->with(array(
			'person',
			'person.country',
			'round',
			'event',
			'format',
		))->findAllByAttributes(array(
			'competition_id'=>$id,
			'pos'=>1,
			'round_type_id'=>array('c', 'f'),
		), array(
			'condition'=>'best > 0',
			'order'=>'event.`rank`, round.`rank`, t.pos'
		));
		$events = array();
		foreach ($winners as $result) {
			$events[$result->event_id] = $result->event_id;
		}
		$top3 = Results::model()->with(array(
			'person',
			'person.country',
			'round',
			'event',
			'format',
		))->findAllByAttributes(array(
			'competition_id'=>$id,
			'pos'=>array(1, 2, 3),
			'round_type_id'=>array('c', 'f'),
		), array(
			'condition'=>'best > 0',
			'order'=>'event.`rank`, round.`rank`, t.pos'
		));
		$all = Results::model()->with(array(
			'person',
			'person.country',
			'round',
			'event',
			'format',
			'attempts',
		))->findAllByAttributes(array(
			'competition_id'=>$id,
		), array(
			'order'=>'event.`rank`, round.`rank`, t.pos'
		));
		$personIds = array_unique(array_map(function($result) {
			return $result->person_id;
		}, $all));
		$previousPersonalRecords = [];
		$command = Yii::app()->wcaDb->createCommand()
			->select([
				'person_id',
				'event_id',
				'MIN(CASE WHEN best > 0 THEN best ELSE 999999999 END) AS best',
				'MIN(CASE WHEN average > 0 THEN average ELSE 999999999 END) AS average',
			])
			->from('results rs')
			->leftJoin('competitions c', 'rs.competition_id=c.id')
			->where(['in', 'person_id', $personIds])
			->andWhere('c.year<:year OR (c.year=:year AND c.month<:month) OR (c.year=:year AND c.month=:month AND c.day<:day)', [
				':year'=>$this->year,
				':month'=>$this->month,
				':day'=>$this->day,
			])
			->group('person_id, event_id');
		foreach ($command->queryAll() as $result) {
			$previousPersonalRecords[$result['person_id']][$result['event_id']] = $result;
		}
		array_walk($all, function($result) use (&$previousPersonalRecords) {
			$personId = $result->person_id;
			$eventId = $result->event_id;
			if ($result->best > 0 && (!isset($previousPersonalRecords[$personId][$eventId]['best'])
				|| $previousPersonalRecords[$personId][$eventId]['best'] == 999999999
				|| $previousPersonalRecords[$personId][$eventId]['best'] >= $result->best)
			) {
				$result->newBest = true;
				$previousPersonalRecords[$personId][$eventId]['best'] = $result->best;
			}
			if ($result->average > 0 && (!isset($previousPersonalRecords[$personId][$eventId]['average'])
				|| $previousPersonalRecords[$personId][$eventId]['average'] == 999999999
				|| $previousPersonalRecords[$personId][$eventId]['average'] >= $result->average)
			) {
				$result->newAverage = true;
				$previousPersonalRecords[$personId][$eventId]['average'] = $result->average;
			}
		});
		$byPerson = $all;
		usort($byPerson, function($resultA, $resultB) {
			$temp = $resultA->person_name <=> $resultB->person_name;
			if ($temp === 0) {
				$temp = $resultA->person_id <=> $resultB->person_id;
			}
			if ($temp === 0) {
				$temp = $resultA->event->rank <=> $resultB->event->rank;
			}
			if ($temp === 0) {
				$temp = $resultB->round->rank <=> $resultA->round->rank;
			}
			return $temp;
		});
		$records = array_filter($all, function($result) {
			return $result->regional_single_record != '' || $result->regional_average_record != '';
		});
		$scrambles = Scrambles::model()->with(array(
			'round',
			'event',
		))->findAllByAttributes(array(
			'competition_id'=>$id,
		), array(
			'order'=>'event.`rank`, round.`rank`, t.group_id, t.is_extra, t.scramble_num',
		));
		return array(
			'winners'=>$winners,
			'top3'=>$top3,
			'all'=>$all,
			'byPerson'=>$byPerson,
			'records'=>array_values($records),
			'scrambles'=>$scrambles,
			'events'=>$events,
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
		return 'https://www.worldcubeassociation.org/competitions/' . $id;
	}

	public function getCityInfo() {
		$competition = Statistics::getCompetition(array(
			'competition_id'=>$this->id,
			'cell_name'=>$this->cell_name,
			'city_name'=>$this->city_name,
		));
		return ActiveRecord::getModelAttributeValue($competition, 'city_name');
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
		$competition = $this->getExtraData();
		return CHtml::link(ActiveRecord::getModelAttributeValue($competition, 'name'), $competition['url']);
	}

	public function getExtraData() {
		return Statistics::getCompetition(array(
			'competition_id'=>$this->id,
			'cell_name'=>$this->cell_name,
			'city_name'=>$this->city_name,
		));
	}

	public function getDate() {
		$date = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day));
		if ($this->end_month > 0) {
			$endDate = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->end_month, $this->end_day));
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
			$this->_location = $this->city_name;
			if ($this->country) {
				$this->_location .= ', ' . $this->country->name;
			}
		}
		return $this->_location;
	}

	public function isInProgress() {
		$now = time();
		$date = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day));
		if ($this->end_month > 0) {
			$endDate = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->end_month, $this->end_day));
		} else {
			$endDate = 0;
		}
		return $now > $date && $now - 86400 < max($date, $endDate);
	}

	public function isEnded() {
		$date = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day));
		if ($this->end_month > 0) {
			$endDate = strtotime(sprintf('%04d-%02d-%02d', $this->year, $this->end_month, $this->end_day));
		} else {
			$endDate = 0;
		}
		return time() - 86400 > max($date, $endDate);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'competitions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('event_specs', 'required'),
			array('year, month, day, end_year, end_month, end_day, latitude_microdegrees, longitude_microdegrees', 'numerical', 'integerOnly'=>true),
			array('id', 'length', 'max'=>32),
			array('name, city_name, country_id', 'length', 'max'=>50),
			array('delegates, venue', 'length', 'max'=>240),
			array('organizers', 'length', 'max'=>200),
			array('venue_address, venue_details', 'length', 'max'=>120),
			array('cell_name', 'length', 'max'=>45),
			array('information', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, city_name, country_id, information, year, month, day, end_year, end_month, end_day, event_specs, delegates, organizers, venue, venue_address, venue_details, external_website, cell_name, latitude_microdegrees, longitude_microdegrees', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'country'=>array(self::BELONGS_TO, 'Countries', 'country_id'),
			'results'=>array(self::HAS_MANY, 'Results', 'competition_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('competitions', 'ID'),
			'name' => Yii::t('competitions', 'Name'),
			'city_name' => Yii::t('competitions', 'City Name'),
			'country_id' => Yii::t('competitions', 'Country'),
			'information' => Yii::t('competitions', 'Information'),
			'year' => Yii::t('competitions', 'Year'),
			'month' => Yii::t('competitions', 'Month'),
			'day' => Yii::t('competitions', 'Day'),
			'end_year' => Yii::t('competitions', 'End Year'),
			'end_month' => Yii::t('competitions', 'End Month'),
			'end_day' => Yii::t('competitions', 'End Day'),
			'event_specs' => Yii::t('competitions', 'Event Specs'),
			'delegates' => Yii::t('competitions', 'Wca Delegate'),
			'organizers' => Yii::t('competitions', 'Organiser'),
			'venue' => Yii::t('competitions', 'Venue'),
			'venue_address' => Yii::t('competitions', 'Venue Address'),
			'venue_details' => Yii::t('competitions', 'Venue Details'),
			'external_website' => Yii::t('competitions', 'Website'),
			'cell_name' => Yii::t('competitions', 'Cell Name'),
			'latitude_microdegrees' => Yii::t('competitions', 'Latitude'),
			'longitude_microdegrees' => Yii::t('competitions', 'Longitude'),
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
				$criteria->compare('country.continent_id', '_' . $this->region);
				break;
			default:
				$criteria->compare('t.country_id', $this->region);
				break;
		}
		if ($this->event && in_array($this->event, array_keys(Events::getNormalEvents()))) {
			$criteria->addCondition("event_specs REGEXP '\\\\b{$this->event}\\\\b'");
		}
		if ($this->name) {
			$names = explode(' ', $this->name);
			foreach ($names as $key=>$value) {
				if (trim($value) === '') {
					continue;
				}
				$paramKey = ':name' . $key;
				$criteria->addCondition("t.cell_name LIKE {$paramKey} or t.city_name LIKE {$paramKey} or t.venue LIKE {$paramKey}");
				$criteria->params[$paramKey] = '%' . $value . '%';
			}
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>$pageSize,
			),
			'sort'=>array(
				'defaultOrder'=>'t.year DESC, t.month DESC, t.day DESC, t.end_month DESC, t.end_day DESC',
			),
		));
	}

	public function searchUser($personId) {
		Yii::import('application.statistics.*');
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;

		$criteria->with = array(
			'results'=>array(
				'together'=>true,
			),
		);
		$criteria->compare('results.person_id', $personId);

		$criteria->group = 't.id';

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>100,
			),
			'sort'=>array(
				'defaultOrder'=>'t.year DESC, t.month DESC, t.day DESC, t.end_month DESC, t.end_day DESC',
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
	 * @return competitions the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
