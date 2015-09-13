<?php

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
		switch ($region) {
			case 'World':
				break;
			case 'Africa':
			case 'Asia':
			case 'Oceania':
			case 'Europe':
			case 'North America':
			case 'South America':
				$command->andWhere('country.continentId=:region', array(
					':region'=>'_' . $region,
				));
				break;
			default:
				$command->andWhere('p.countryId=:region', array(
					':region'=>$region,
				));
				break;
		}
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
			'average',
			'event',
		))->findAllByAttributes(array(
			'personId'=>$id
		), array(
			'order'=>'event.rank ASC',
		));
		$personRanks = array();
		foreach ($ranks as $rank) {
			$personRanks[$rank->eventId] = $rank;
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
		$personResults = array();
		$eventId = '';
		$best = $average = PHP_INT_MAX;
		$results = Results::model()->with(array(
			'competition',
			'round',
			'event',
		))->findAllByAttributes(array(
			'personId'=>$id
		), array(
			'order'=>'event.rank, competition.year, competition.month, competition.day, round.rank'
		));
		foreach($results as $result) {
			if ($eventId != $result->eventId) {
				//重置各值
				$eventId = $result->eventId;
				$best = $average = PHP_INT_MAX;
				$personResults[$eventId] = array();
			}
			if ($result->best > 0 && $result->best <= $best) {
				$result->newBest = true;
				$best = $result->best;
			}
			if ($result->average > 0 && $result->average <= $average) {
				$result->newAverage = true;
				$average = $result->average;
			}
			$personResults[$eventId][] = $result;
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
			$data = Statistics::getCompetition(array(
				'competitionId'=>$competition->id,
				'cellName'=>$competition->cellName,
				'cityName'=>$competition->cityName,
			));
			$data['longitude'] = $competition->longitude / 1e6;
			$data['latitude'] = $competition->latitude / 1e6;
			$data['url'] = CHtml::normalizeUrl($data['url']);
			$data['date'] = $competition->getDate();
			$competition->number = $key + 1;
			$mapData[] = $data;
		}
		$mapCenter = array(
			'longitude'=>number_format($temp['longitude'] / count($competitions), 6, ',', ''),
			'latitude'=>number_format($temp['latitude'] / count($competitions), 6, ',', ''),
		);
		return array(
			'id'=>$id,
			'personRanks'=>$personRanks,
			'personResults'=>call_user_func_array('array_merge', array_map('array_reverse', $personResults)),
			'wcPodiums'=>$wcPodiums,
			'historyWR'=>$historyWR,
			'historyCR'=>$historyCR,
			'historyNR'=>$historyNR,
			'overAll'=>$overAll,
			'score'=>$overAll['WR'] * 10 + $overAll['CR'] * 5 + $overAll['NR'],
			'firstCompetition'=>$firstCompetitionResult->competition,
			'lastCompetition'=>$lastCompetitionResult->competition,
			'mapData'=>$mapData,
			'mapCenter'=>$mapCenter,
			'competitions'=>array_reverse($competitions),
			'user'=>User::model()->findByAttributes(array(
				'wcaid'=>$id,
				'status'=>User::STATUS_NORMAL,
			)),
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
