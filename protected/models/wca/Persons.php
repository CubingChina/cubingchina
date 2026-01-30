<?php

Yii::import('application.statistics.*');

/**
 * This is the model class for table "persons".
 *
 * The followings are the available columns in table 'persons':
 * @property string $wca_id
 * @property integer $sub_id
 * @property string $name
 * @property string $country_id
 * @property string $gender
 */
class Persons extends ActiveRecord {

	public static function getBattleCheckBox($name, $id, $container = 'div', $htmlOptions = array('class'=>'checkbox')) {
		if ($id === '') {
			return '';
		}
		$checkBox = CHtml::checkBox('ids', isset($_COOKIE['battle_' . $id]), array(
			'class'=>'battle-person',
			'data-id'=>$id,
			'data-name'=>$name,
		));
		$text = CHtml::tag('span', array(), Yii::t('common', 'Battle'));
		$label = CHtml::tag('label', array('class'=>'battle-label'), $checkBox . $text);
		return CHtml::tag($container, $htmlOptions, $label);
	}

	public static function getPersons($region = 'China', $gender = 'all', $name = '', $page = 1) {
		$command = Yii::app()->wcaDb->createCommand()
		->select(array(
			'p.*',
			'country.iso2',
			'country.name AS country_name',
		))
		->from('persons p')
		->leftJoin('countries country', 'p.country_id=country.id')
		->where('p.sub_id=1');
		switch ($gender) {
			case 'female':
				$command->andWhere('p.gender="f"');
				break;
			case 'male':
				$command->andWhere('p.gender="m"');
				break;
		}
		self::applyRegionCondition($command, $region, 'p.country_id');
		if ($name) {
			$names = explode(' ', $name);
			foreach ($names as $key=>$value) {
				if (trim($value) === '') {
					continue;
				}
				$paramKey = ':name' . $key;
				$command->andWhere("p.name LIKE {$paramKey} or p.wca_id LIKE {$paramKey}", array(
					$paramKey=>'%' . $value . '%',
				));
			}
		}
		$cmd1 = clone $command;
		$count = $cmd1->select('COUNT(DISTINCT p.wca_id) AS count')
		->queryScalar();
		if ($page > ceil($count / 100)) {
			$page = ceil($count / 100);
		}
		$rows = array();
		$command->order('p.name ASC')
		->limit(100, ($page - 1) * 100);
		return array(
			'count'=>$count,
			'rows'=>$command->queryAll(),
		);
	}

	public static function getGenders() {
		return array(
			'all'=>Yii::t('common', 'All'),
			'female'=>Yii::t('common', 'Female'),
			'male'=>Yii::t('common', 'Male'),
		);
	}

	public static function getPersonNameById($id) {
		$person = self::model()->findByAttributes(array(
			'wca_id'=>$id,
			'sub_id'=>1,
		));
		if ($person === null) {
			return '';
		}
		return $person->name;
	}

	public static function getLinkById($id) {
		$person = self::model()->findByAttributes(array(
			'wca_id'=>$id,
			'sub_id'=>1,
		));
		if ($person === null) {
			return '';
		}
		return self::getLinkByNameNId($person->name, $id);
	}

	public static function getLinkByNameNId($name, $id) {
		return CHtml::link($name, array(
			'/results/p',
			'id'=>$id,
		));
	}

	public static function getWCALinkByNameNId($name, $id) {
		return CHtml::link($name, 'https://www.worldcubeassociation.org/persons/' . $id, array('target'=>'_blank'));
	}

	public static function getWCAIconLinkByNameNId($name, $id) {
		return self::getWCALinkByNameNId(CHtml::image('/f/images/wca.png', $name, array('class'=>'wca-competition')), $id) . $id;
	}

	public static function getResults($id) {
		$db = Yii::app()->wcaDb;
		//个人排名
		$ranks = RanksSingle::model()->with(array(
			'average'=>array(
				'together'=>true,
			),
			'person'=>array(
				'together'=>true,
			),
			'event'=>array(
				'together'=>true,
			),
		))->findAllByAttributes(array(
			'person_id'=>$id
		), array(
			'order'=>'event.`rank` ASC',
		));
		$personRanks = array();
		foreach ($ranks as $rank) {
			$personRanks[$rank->event_id] = $rank;
		}
		//sum of ranks
		$sumOfRanks = RanksSum::model()->findAllByAttributes(array(
			'person_id'=>$id,
		), array(
			'order'=>'type DESC',
		));
		foreach ($sumOfRanks as $key=>$sumOfRank) {
			// $sumOfRank->getRanks();
		}
		//奖牌数量
		$command = $db->createCommand();
		$command->select(array(
			'event_id',
			'sum(CASE WHEN pos=1 AND round_type_id IN ("c", "f") AND best>0 THEN 1 ELSE 0 END) AS gold',
			'sum(CASE WHEN pos=2 AND round_type_id IN ("c", "f") AND best>0 THEN 1 ELSE 0 END) AS silver',
			'sum(CASE WHEN pos=3 AND round_type_id IN ("c", "f") AND best>0 THEN 1 ELSE 0 END) AS bronze',
			'sum(solve) AS solve',
			'sum(attempt) AS attempt',
		))
		->from('results')
		->where('person_id=:person_id', array(
			':person_id'=>$id,
		));
		$command2 = clone $command;
		$overAllMedals = $command->queryRow();
		$command2->group('event_id');
		foreach ($command2->queryAll() as $row) {
			if (isset($personRanks[$row['event_id']])) {
				$personRanks[$row['event_id']]->medals = $row;
			}
		}
		//历史成绩
		$competitions = array();
		$byEvent = array();
		$byCompetition = array();
		$eventId = '';
		$best = $average = PHP_INT_MAX;
		$lastBest = $lastAverage = null;
		$year = 0;
		$results = Results::model()->with(array(
			'competition',
			'competition.country',
			'round',
			'event',
			'attempts',
		))->findAllByAttributes(array(
			'person_id'=>$id
		), array(
			'order'=>'event.`rank`, competition.year, competition.month, competition.day, round.`rank`'
		));
		$pbTemplate = [
			'best'=>0,
			'average'=>0,
			'total'=>0,
		];
		$personalBests = [
			'total'=>$pbTemplate,
			'years'=>[],
			'events'=>[],
		];
		$personalBestResults = [];
		foreach($results as $result) {
			if ($eventId != $result->event_id) {
				$personalBestResults[$year][$eventId]['best'] = $lastBest;
				$personalBestResults[$year][$eventId]['average'] = $lastAverage;
				//重置各值
				$eventId = $result->event_id;
				$best = $average = PHP_INT_MAX;
				$byEvent[$eventId] = array();
				$year = 0;
				$lastBest = $lastAverage = null;
			}
			if ($year != $result->competition->year) {
				$personalBestResults[$year][$eventId]['best'] = $lastBest;
				$personalBestResults[$year][$eventId]['average'] = $lastAverage;
				$year = $result->competition->year;
			}
			if ($result->best > 0 && $result->best <= $best) {
				$result->newBest = true;
				$best = $result->best;
				$lastBest = $result;
			}
			if ($result->average > 0 && $result->average <= $average) {
				$result->newAverage = true;
				$average = $result->average;
				$lastAverage = $result;
			}
			$key = $result->competition->year;
			if ($result->newBest || $result->newAverage) {
				if (!isset($personalBests['years'][$key][$result->event_id])) {
					$personalBests['years'][$key][$result->event_id] = $pbTemplate;
				}
				if (!isset($personalBests['events'][$result->event_id])) {
					$personalBests['events'][$result->event_id] = $pbTemplate;
				}
				if ($result->newBest) {
					$personalBests['years'][$key][$result->event_id]['best']++;
					$personalBests['years'][$key][$result->event_id]['total']++;
					$personalBests['events'][$result->event_id]['best']++;
					$personalBests['events'][$result->event_id]['total']++;
					$personalBests['total']['best']++;
					$personalBests['total']['total']++;
				}
				if ($result->newAverage) {
					$personalBests['years'][$key][$result->event_id]['average']++;
					$personalBests['years'][$key][$result->event_id]['total']++;
					$personalBests['events'][$result->event_id]['average']++;
					$personalBests['events'][$result->event_id]['total']++;
					$personalBests['total']['average']++;
					$personalBests['total']['total']++;
				}
			}
			$byEvent[$eventId][] = $result;
			$byCompetition[$result->competition_id][] = $result;
			$competitions[$result->competition_id] = $result->competition;
		}
		$personalBestResults[$year][$eventId]['best'] = $lastBest;
		$personalBestResults[$year][$eventId]['average'] = $lastAverage;
		krsort($personalBestResults);
		$podiums = Results::getChampionshipPodiums($id);
		//WR们
		$historyWR = Results::model()->with(array(
			'competition',
			'event',
			'round',
		))->findAllByAttributes(array(
			'person_id'=>$id,
		), array(
			'condition'=>'regional_single_record="WR" OR regional_average_record="WR"',
			'order'=>'event.`rank` ASC, competition.year DESC, competition.month DESC, competition.day DESC, round.`rank` DESC',
		));
		//CR们
		$historyCR = Results::model()->with(array(
			'competition',
			'event',
			'round',
		))->findAllByAttributes(array(
			'person_id'=>$id,
		), array(
			'condition'=>'regional_single_record NOT IN ("WR", "NR", "") OR regional_average_record NOT IN ("WR", "NR", "")',
			'order'=>'event.`rank` ASC, competition.year DESC, competition.month DESC, competition.day DESC, round.`rank` DESC',
		));
		//NR们
		$historyNR = Results::model()->with(array(
			'competition',
			'event',
			'round',
		))->findAllByAttributes(array(
			'person_id'=>$id,
		), array(
			'condition'=>'regional_single_record="NR" OR regional_average_record="NR"',
			'order'=>'event.`rank` ASC, competition.year DESC, competition.month DESC, competition.day DESC, round.`rank` DESC',
		));
		//
		$firstCompetitionResult = Results::model()->with(array(
			'competition',
		))->findByAttributes(array(
			'person_id'=>$id,
		), array(
			'order'=>'competition.year ASC, competition.month ASC, competition.day ASC',
		));
		$lastCompetitionResult = Results::model()->with(array(
			'competition',
		))->findByAttributes(array(
			'person_id'=>$id,
		), array(
			'order'=>'competition.year DESC, competition.month DESC, competition.day DESC',
		));
		$overAll = array(
			'gold'=>$overAllMedals['gold'],
			'silver'=>$overAllMedals['silver'],
			'bronze'=>$overAllMedals['bronze'],
			'WR'=>count(array_filter($historyWR, function($result) {
				return $result->regional_single_record == 'WR';
			})) + count(array_filter($historyWR, function($result) {
				return $result->regional_average_record == 'WR';
			})),
			'CR'=>count(array_filter($historyCR, function($result) {
				return !in_array($result->regional_single_record, array('WR', 'NR', ''));
			})) + count(array_filter($historyCR, function($result) {
				return !in_array($result->regional_average_record, array('WR', 'NR', ''));
			})),
			'NR'=>count(array_filter($historyNR, function($result) {
				return $result->regional_single_record == 'NR';
			})) + count(array_filter($historyNR, function($result) {
				return $result->regional_average_record == 'NR';
			})),
		);
		$competitionIds = array_keys($competitions);
		usort($competitions, function($competitionA, $competitionB) {
			$temp = $competitionB->year - $competitionA->year;
			if ($temp == 0) {
				$temp = $competitionB->month - $competitionA->month;
			}
			if ($temp == 0) {
				$temp = $competitionB->day - $competitionA->day;
			}
			return -$temp;
		});
		$temp = array(
			'longitude'=>0,
			'latitude'=>0,
		);
		$mapData = array();
		foreach ($competitions as $key=>$competition) {
			$temp['longitude'] += $competition->latitude_microdegrees / 1e6;
			$temp['latitude'] += $competition->latitude_microdegrees / 1e6;
			$data = $competition->getExtraData();
			$data['longitude'] = $competition->longitude_microdegrees / 1e6;
			$data['latitude'] = $competition->latitude_microdegrees / 1e6;
			$data['url'] = CHtml::normalizeUrl($data['url']);
			$data['date'] = $competition->getDate();
			$competition->number = $key + 1;
			$mapData[] = $data;
		}
		$competitionCount = count($competitions) ?: 1;
		$mapCenter = array(
			'longitude'=>number_format($temp['longitude'] / $competitionCount, 6, '.', ''),
			'latitude'=>number_format($temp['latitude'] / $competitionCount, 6, '.', ''),
		);
		if ($byCompetition != array()) {
			$byCompetition = call_user_func_array('array_merge', array_values($byCompetition));
		}
		usort($byCompetition, function($resultA, $resultB) {
			$temp = $resultB->competition->year - $resultA->competition->year;
			if ($temp == 0) {
				$temp = $resultB->competition->month - $resultA->competition->month;
			}
			if ($temp == 0) {
				$temp = $resultB->competition->day - $resultA->competition->day;
			}
			if ($temp == 0) {
				$temp = strcmp($resultA->competition_id, $resultB->competition_id);
			}
			if ($temp == 0) {
				$temp = $resultA->event->rank - $resultB->event->rank;
			}
			if ($temp == 0) {
				$temp = $resultB->round->rank - $resultA->round->rank;
			}
			return $temp;
		});
		if ($byEvent != array()) {
			$byEvent = call_user_func_array('array_merge', array_values(array_map('array_reverse', $byEvent)));
		}
		//closest cubers and seen cubers
		$allCubers = $db->createCommand()
		->select(array(
			'person_id',
			'person_name',
			'count(DISTINCT competition_id) AS count',
		))
		->from('results')
		->where(array('in', 'competition_id', $competitionIds))
		->group('person_id')
		->having('count>1')
		->order('count ASC, person_name DESC')
		// ->limit(21)
		->queryAll();
		$closestCubers = array_filter(array_slice(array_reverse($allCubers), 0, 21), function($cuber) use($id) {
			return $cuber['person_id'] != $id;
		});
		$seenCubers = [];
		foreach ($allCubers as $cuber) {
			$count = $cuber['count'];
			if (!isset($seenCubers[$count])) {
				$seenCubers[$count] = [
					'count'=>$count,
					'competitors'=>0,
				];
				if ($count == $competitionCount) {
					$seenCubers[$count]['competitors']--;
				}
			}
			$seenCubers[$count]['competitors']++;
		}
		ksort($seenCubers);
		$allSeenCubers = $db->createCommand()
		->select(array(
			'count(DISTINCT person_id) AS count',
		))
		->from('results')
		->where(array('in', 'competition_id', $competitionIds))
		->queryScalar();
		$sum = array_sum(array_map(function($data) {
			return $data['competitors'];
		}, $seenCubers));
		array_unshift($seenCubers, [
			'count'=>1,
			'competitors'=>$allSeenCubers - $sum,
		]);
		$seenCubers[] = [
			'count'=>'All',
			'competitors'=>$allSeenCubers,
		];
		$seenCubers = array_filter($seenCubers, function($data) {
			return $data['competitors'] > 0;
		});
		//visited provinces
		$visitedProvinces = [];
		$chineseCompetitions = Competition::model()->findAllByAttributes([
			'wca_competition_id'=>$competitionIds,
			'status'=>Competition::STATUS_SHOW,
		]);
		foreach ($chineseCompetitions as $competition) {
			if (!$competition->isMultiLocation()) {
				$location = $competition->location[0];
				//Hong Kong, Macau and Taiwan
				if (in_array($location->province_id, [2, 3, 4])) {
					continue;
				}
				if (!isset($visitedProvinces[$location->province_id])) {
					$visitedProvinces[$location->province_id] = [
						'name'=>$location->province->name,
						'name_zh'=>$location->province->name_zh,
						'count'=>0,
					];
				}
				$visitedProvinces[$location->province_id]['count']++;
			}
		}
		foreach ($competitions as $competition) {
			if (in_array($competition->country_id, ['Hong Kong', 'Taiwan', 'Macau'])) {
				if (!isset($visitedProvinces[$competition->country_id])) {
					$visitedProvinces[$competition->country_id] = [
						'name'=>$competition->country_id,
						'name_zh'=>$competition->country_id,
						'count'=>0,
					];
				}
				$visitedProvinces[$competition->country_id]['count']++;
			}
		}
		usort($visitedProvinces, function($dataA, $dataB) {
			return $dataB['count'] - $dataA['count'];
		});
		return array(
			'id'=>$id,
			'personRanks'=>$personRanks,
			'sumOfRanks'=>$sumOfRanks,
			'byEvent'=>$byEvent,
			'byCompetition'=>$byCompetition,
			'personalBests'=>$personalBests,
			'personalBestResults'=>$personalBestResults,
			'podiums'=>$podiums,
			'historyWR'=>$historyWR,
			'historyCR'=>$historyCR,
			'historyNR'=>$historyNR,
			'overAll'=>$overAll,
			'score'=>$overAll['WR'] * 10 + $overAll['CR'] * 5 + $overAll['NR'],
			'firstCompetition'=>$firstCompetitionResult ? $firstCompetitionResult->competition : null,
			'lastCompetition'=>$lastCompetitionResult ? $lastCompetitionResult->competition : null,
			'mapData'=>$mapData,
			'mapCenter'=>$mapCenter,
			'competitions'=>array_reverse($competitions),
			'user'=>User::model()->findByAttributes(array(
				'wcaid'=>$id,
				'status'=>User::STATUS_NORMAL,
			)),
			'closestCubers'=>array_values($closestCubers),
			'seenCubers'=>array_values($seenCubers),
			'visitedProvinces'=>array_values($visitedProvinces),
		);
	}

	public function getCompetitionNum() {
		return Results::model()->countByAttributes(array(
			'person_id'=>$this->wca_id,
		), array(
			'select'=>'COUNT(DISTINCT competition_id)',
		));
	}

	public function getDelegatedCompetitions() {
		if ($this->delegate === null) {
			return [];
		}
		$criteria = new CDbCriteria();
		$criteria->compare('cancelled', 0);
		$criteria->compare('wcaDelegate', $this->delegate->email, true);
		$criteria->order = 'year DESC, month DESC, day DESC';
		return Competitions::model()->findAll($criteria);
	}

	public function getLocalName() {
		if (preg_match('{\((.+)\)}', $this->name, $matches)) {
			return $matches[1];
		}
		return $this->name;
	}

	public function getStartYear() {
		return substr($this->wca_id, 0, 4);
	}

	public function getSummaryYears() {
		$years = [];
		$startYear = $this->startYear;
		$endYear = Summary::getCurrentYear();
		for ($year = $endYear; $year >= $startYear && $year >= 2003; $year--) {
			$years[$year] = $year;
		}
		return $years;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'persons';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('sub_id', 'numerical', 'integerOnly'=>true),
			array('wca_id', 'length', 'max'=>10),
			array('name', 'length', 'max'=>80),
			array('country_id', 'length', 'max'=>50),
			array('gender', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('wca_id, sub_id, name, country_id, gender', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return [
			'country'=>[self::BELONGS_TO, 'Countries', 'country_id'],
			'delegate'=>[self::HAS_ONE, 'Delegates', ['wca_id'=>'wca_id']],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'wca_id'=>Yii::t('persons', 'ID'),
			'sub_id'=>Yii::t('persons', 'Subid'),
			'name'=>Yii::t('persons', 'Name'),
			'country_id'=>Yii::t('persons', 'Country'),
			'gender'=>Yii::t('persons', 'Gender'),
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

		$criteria->compare('wca_id',$this->wca_id,true);
		$criteria->compare('sub_id',$this->sub_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('country_id',$this->country_id,true);
		$criteria->compare('gender',$this->gender,true);

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
	 * @return persons the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
