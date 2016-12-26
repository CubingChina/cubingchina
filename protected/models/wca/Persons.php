<?php

Yii::import('application.statistics.*');

/**
 * This is the model class for table "Persons".
 *
 * The followings are the available columns in table 'Persons':
 * @property string $id
 * @property integer $subid
 * @property string $name
 * @property string $countryId
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
			'country.name AS countryName',
		))
		->from('Persons p')
		->leftJoin('Countries country', 'p.countryId=country.id')
		->where('p.subid=1');
		switch ($gender) {
			case 'female':
				$command->andWhere('p.gender="f"');
				break;
			case 'male':
				$command->andWhere('p.gender="m"');
				break;
		}
		self::applyRegionCondition($command, $region, 'p.countryId');
		if ($name) {
			$names = explode(' ', $name);
			foreach ($names as $key=>$value) {
				if (trim($value) === '') {
					continue;
				}
				$paramKey = ':name' . $key;
				$command->andWhere("p.name LIKE {$paramKey} or p.id LIKE {$paramKey}", array(
					$paramKey=>'%' . $value . '%',
				));
			}
		}
		$cmd1 = clone $command;
		$count = $cmd1->select('COUNT(DISTINCT p.id) AS count')
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
			'id'=>$id,
			'subid'=>1,
		));
		if ($person === null) {
			return '';
		}
		return $person->name;
	}

	public static function getLinkById($id) {
		$person = self::model()->findByAttributes(array(
			'id'=>$id,
			'subid'=>1,
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
		return CHtml::link($name, 'https://www.worldcubeassociation.org/results/p.php?i=' . $id, array('target'=>'_blank'));
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
			'personId'=>$id
		), array(
			'order'=>'event.rank ASC',
		));
		$personRanks = array();
		foreach ($ranks as $rank) {
			$personRanks[$rank->eventId] = $rank;
		}
		//sum of ranks
		$sumOfRanks = RanksSum::model()->findAllByAttributes(array(
			'personId'=>$id,
		), array(
			'order'=>'type DESC',
		));
		foreach ($sumOfRanks as $key=>$sumOfRank) {
			$sumOfRank->getRanks();
		}
		//奖牌数量
		$command = $db->createCommand();
		$command->select(array(
			'eventId',
			'sum(CASE WHEN pos=1 AND roundId IN ("c", "f") AND best>0 THEN 1 ELSE 0 END) AS gold',
			'sum(CASE WHEN pos=2 AND roundId IN ("c", "f") AND best>0 THEN 1 ELSE 0 END) AS silver',
			'sum(CASE WHEN pos=3 AND roundId IN ("c", "f") AND best>0 THEN 1 ELSE 0 END) AS bronze',
			'sum(CASE WHEN value1>0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value2>0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value3>0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value4>0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value5>0 THEN 1 ELSE 0 END)
			AS solve',
			'sum(CASE WHEN value1>-2 AND value1!=0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value2>-2 AND value2!=0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value3>-2 AND value3!=0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value4>-2 AND value4!=0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value5>-2 AND value5!=0 THEN 1 ELSE 0 END)
			AS attempt',
		))
		->from('Results')
		->where('personId=:personId', array(
			':personId'=>$id,
		))
		->group('eventId');
		foreach ($command->queryAll() as $row) {
			if (isset($personRanks[$row['eventId']])) {
				$personRanks[$row['eventId']]->medals = $row;
			}
		}
		//历史成绩
		$competitions = array();
		$byEvent = array();
		$byCompetition = array();
		$eventId = '';
		$best = $average = PHP_INT_MAX;
		$results = Results::model()->with(array(
			'competition',
			'competition.country',
			'round',
			'event',
		))->findAllByAttributes(array(
			'personId'=>$id
		), array(
			'order'=>'event.rank, competition.year, competition.month, competition.day, round.rank'
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
		foreach($results as $result) {
			if ($eventId != $result->eventId) {
				//重置各值
				$eventId = $result->eventId;
				$best = $average = PHP_INT_MAX;
				$byEvent[$eventId] = array();
			}
			if ($result->best > 0 && $result->best <= $best) {
				$result->newBest = true;
				$best = $result->best;
			}
			if ($result->average > 0 && $result->average <= $average) {
				$result->newAverage = true;
				$average = $result->average;
			}
			$year = $result->competition->year;
			if ($result->newBest || $result->newAverage) {
				if (!isset($personalBests['years'][$year][$result->eventId])) {
					$personalBests['years'][$year][$result->eventId] = $pbTemplate;
				}
				if (!isset($personalBests['events'][$result->eventId])) {
					$personalBests['events'][$result->eventId] = $pbTemplate;
				}
				if ($result->newBest) {
					$personalBests['years'][$year][$result->eventId]['best']++;
					$personalBests['years'][$year][$result->eventId]['total']++;
					$personalBests['events'][$result->eventId]['best']++;
					$personalBests['events'][$result->eventId]['total']++;
					$personalBests['total']['best']++;
					$personalBests['total']['total']++;
				}
				if ($result->newAverage) {
					$personalBests['years'][$year][$result->eventId]['average']++;
					$personalBests['years'][$year][$result->eventId]['total']++;
					$personalBests['events'][$result->eventId]['average']++;
					$personalBests['events'][$result->eventId]['total']++;
					$personalBests['total']['average']++;
					$personalBests['total']['total']++;
				}
			}
			$byEvent[$eventId][] = $result;
			$byCompetition[$result->competitionId][] = $result;
			$competitions[$result->competitionId] = $result->competition;
		}
		//世锦赛获奖记录
		$wcPodiums = Results::model()->with(array(
			'competition',
			'event',
		))->findAllByAttributes(array(
			'personId'=>$id,
			'roundId'=>array('c', 'f'),
			'pos'=>array(1, 2, 3),
		), array(
			'condition'=>'competitionId LIKE "WC%"',
			'order'=>'competition.year DESC, event.rank ASC',
		));
		$podiums = Results::getChampionshipPodiums($id);
		//洲/国锦赛获奖记录
		$ccPodiums = isset($podiums['continent']) ? $podiums['continent'] : array();
		$ncPodiums = isset($podiums['country']) ? $podiums['country'] : array();
		$rcPodiums = isset($podiums['region']) ? $podiums['region'] : array();
		//WR们
		$historyWR = Results::model()->with(array(
			'competition',
			'event',
			'round',
		))->findAllByAttributes(array(
			'personId'=>$id,
		), array(
			'condition'=>'regionalSingleRecord="WR" OR regionalAverageRecord="WR"',
			'order'=>'event.rank ASC, competition.year DESC, competition.month DESC, competition.day DESC, round.rank DESC',
		));
		//CR们
		$historyCR = Results::model()->with(array(
			'competition',
			'event',
			'round',
		))->findAllByAttributes(array(
			'personId'=>$id,
		), array(
			'condition'=>'regionalSingleRecord NOT IN ("WR", "NR", "") OR regionalAverageRecord NOT IN ("WR", "NR", "")',
			'order'=>'event.rank ASC, competition.year DESC, competition.month DESC, competition.day DESC, round.rank DESC',
		));
		//NR们
		$historyNR = Results::model()->with(array(
			'competition',
			'event',
			'round',
		))->findAllByAttributes(array(
			'personId'=>$id,
		), array(
			'condition'=>'regionalSingleRecord="NR" OR regionalAverageRecord="NR"',
			'order'=>'event.rank ASC, competition.year DESC, competition.month DESC, competition.day DESC, round.rank DESC',
		));
		//
		$firstCompetitionResult = Results::model()->with(array(
			'competition',
		))->findByAttributes(array(
			'personId'=>$id,
		), array(
			'order'=>'competition.year ASC, competition.month ASC, competition.day ASC',
		));
		$lastCompetitionResult = Results::model()->with(array(
			'competition',
		))->findByAttributes(array(
			'personId'=>$id,
		), array(
			'order'=>'competition.year DESC, competition.month DESC, competition.day DESC',
		));
		$overAll = array(
			'gold'=>array_sum(array_map(function($result) {
				return $result->medals['gold'];
			}, $personRanks)),
			'silver'=>array_sum(array_map(function($result) {
				return $result->medals['silver'];
			}, $personRanks)),
			'bronze'=>array_sum(array_map(function($result) {
				return $result->medals['bronze'];
			}, $personRanks)),
			'WR'=>count(array_filter($historyWR, function($result) {
				return $result->regionalSingleRecord == 'WR';
			})) + count(array_filter($historyWR, function($result) {
				return $result->regionalAverageRecord == 'WR';
			})),
			'CR'=>count(array_filter($historyCR, function($result) {
				return !in_array($result->regionalSingleRecord, array('WR', 'NR', ''));
			})) + count(array_filter($historyCR, function($result) {
				return !in_array($result->regionalAverageRecord, array('WR', 'NR', ''));
			})),
			'NR'=>count(array_filter($historyNR, function($result) {
				return $result->regionalSingleRecord == 'NR';
			})) + count(array_filter($historyNR, function($result) {
				return $result->regionalAverageRecord == 'NR';
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
			$temp['longitude'] += $competition->longitude / 1e6;
			$temp['latitude'] += $competition->latitude / 1e6;
			$data = $competition->getExtraData();
			$data['longitude'] = $competition->longitude / 1e6;
			$data['latitude'] = $competition->latitude / 1e6;
			$data['url'] = CHtml::normalizeUrl($data['url']);
			$data['date'] = $competition->getDate();
			$competition->number = $key + 1;
			$mapData[] = $data;
		}
		$competitionCount = count($competitions) ?: 1;
		$mapCenter = array(
			'longitude'=>number_format($temp['longitude'] / $competitionCount, 6, ',', ''),
			'latitude'=>number_format($temp['latitude'] / $competitionCount, 6, ',', ''),
		);
		if ($byCompetition != array()) {
			$byCompetition = call_user_func_array('array_merge', $byCompetition);
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
				$temp = $resultA->event->rank - $resultB->event->rank;
			}
			if ($temp == 0) {
				$temp = $resultB->round->rank - $resultA->round->rank;
			}
			return $temp;
		});
		if ($byEvent != array()) {
			$byEvent = call_user_func_array('array_merge', array_map('array_reverse', $byEvent));
		}
		//closest cubers and seen cubers
		$allCubers = $db->createCommand()
		->select(array(
			'personId',
			'personName',
			'count(DISTINCT competitionId) AS count',
		))
		->from('Results')
		->where(array('in', 'competitionId', $competitionIds))
		->group('personId')
		->having('count>1')
		->order('count ASC, personName DESC')
		// ->limit(21)
		->queryAll();
		$closestCubers = array_filter(array_slice(array_reverse($allCubers), 0, 21), function($cuber) use($id) {
			return $cuber['personId'] != $id;
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
			'count(DISTINCT personId) AS count',
		))
		->from('Results')
		->where(array('in', 'competitionId', $competitionIds))
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
			if (in_array($competition->countryId, ['Hong Kong', 'Taiwan', 'Macau'])) {
				if (!isset($visitedProvinces[$competition->countryId])) {
					$visitedProvinces[$competition->countryId] = [
						'name'=>$competition->countryId,
						'name_zh'=>$competition->countryId,
						'count'=>0,
					];
				}
				$visitedProvinces[$competition->countryId]['count']++;
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
			'wcPodiums'=>$wcPodiums,
			'ccPodiums'=>$ccPodiums,
			'ncPodiums'=>$ncPodiums,
			'rcPodiums'=>$rcPodiums,
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
			'personId'=>$this->id,
		), array(
			'select'=>'COUNT(DISTINCT competitionId)',
		));
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'Persons';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('subid', 'numerical', 'integerOnly'=>true),
			array('id', 'length', 'max'=>10),
			array('name', 'length', 'max'=>80),
			array('countryId', 'length', 'max'=>50),
			array('gender', 'length', 'max'=>1),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, subid, name, countryId, gender', 'safe', 'on'=>'search'),
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
			'id'=>Yii::t('Persons', 'ID'),
			'subid'=>Yii::t('Persons', 'Subid'),
			'name'=>Yii::t('Persons', 'Name'),
			'countryId'=>Yii::t('Persons', 'Country'),
			'gender'=>Yii::t('Persons', 'Gender'),
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
		$criteria->compare('subid',$this->subid);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('countryId',$this->countryId,true);
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
	 * @return Persons the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
