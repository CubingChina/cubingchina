<?php

/**
 * This is the model class for table "results".
 *
 * The followings are the available columns in table 'results':
 * @property string $id
 * @property string $competition_id
 * @property string $event_id
 * @property string $round_type_id
 * @property integer $pos
 * @property integer $best
 * @property integer $average
 * @property string $person_name
 * @property string $person_id
 * @property string $person_country_id
 * @property string $format_id
 * @property string $regional_single_record
 * @property string $regional_average_record
 */
class Results extends ActiveRecord {

	public $newBest = false;
	public $newAverage = false;

	public static function getRankingTypes() {
		return array('single', 'average');
	}

	public static function getChampionshipPodiums($person_id) {
		$person = Persons::model()->with('country')->findByAttributes(array(
			'wca_id' => $person_id,
			'sub_id'=>1,
		));
		if ($person === null) {
			return [];
		}
		$cache = Yii::app()->cache;
		$method = 'Championships::buildChampionshipPodiums';
		$allPodiums['world'] = $cache->getData($method, ['world']);
		$allPodiums['continent'] = $cache->getData($method, [$person->country->continent_id]);
		$eligibleChampionshipTypes = EligibleCountryIso2sForChampionship::model()->findAllByAttributes([
			'eligible_country_iso2'=>$person->country->iso2,
		]);
		foreach ($eligibleChampionshipTypes as $eligibleChampionshipType) {
			$allPodiums[$eligibleChampionshipType->championship_type] = $cache->getData($method, [$eligibleChampionshipType->championship_type]);
		}
		$allPodiums['region'] = $cache->getData($method, [$person->country->iso2]);
		$personPodiums = [];
		foreach ($allPodiums as $key=>$podiums) {
			if (isset($podiums[$person_id])) {
				$personPodiums[$key] = $podiums[$person_id];
			}
		}
		return $personPodiums;
	}

	public static function getRankings($region = 'China', $type = 'single', $event = '333', $gender = 'all', $page = 1) {
		$cache = Yii::app()->cache;
		$cacheKey = "results_rankings_{$region}_{$type}_{$event}_{$gender}_{$page}";
		$expire = 86400 * 7;
		$field = $type === 'single' ? 'best' : 'average';
		if (($data = $cache->get($cacheKey)) === false) {
			$command = Yii::app()->wcaDb->createCommand()
				->select(array(
					'event_id',
					'best',
					'person_id',
				))
				->from('best_results')
				->where('type=:type AND event_id=:event_id', array(
					':type'=>$type,
					':event_id'=>$event,
				));
			self::applyRegionCondition($command, $region, 'country_id', 'continent_id');
			switch ($gender) {
				case 'female':
					$command->andWhere('gender="f"');
					break;
				case 'male':
					$command->andWhere('gender="m"');
					break;
			}
			$cmd1 = clone $command;
			$cmd2 = clone $command;
			$count = $cmd1->select('COUNT(DISTINCT person_id) AS count')
			->queryScalar();
			if ($page > ceil($count / 100)) {
				$page = ceil($count / 100);
			}
			$rows = array();
			$command
				->order('best ASC, person_id ASC')
				->limit(100, ($page - 1) * 100);
			$eventBestPerson = array_map(function($row) {
				return sprintf('("%s", %d, "%s")', $row['event_id'], $row['best'], $row['person_id']);
			}, $command->queryAll());
			if ($eventBestPerson !== array()) {
				$command = Yii::app()->wcaDb->createCommand()
				->select(array(
					'rs.id',
					'rs.event_id',
					sprintf('rs.%s AS best', $field),
					'rs.person_id',
					'rs.person_name',
					'rs.person_country_id',
					'rs.competition_id',
					'c.cell_name',
					'c.city_name',
					'c.year',
					'c.month',
					'c.day',
					'country.name AS country_name',
					'country.iso2',
				))
				->from('results rs')
				->leftJoin('competitions c', 'rs.competition_id=c.id')
				->leftJoin('countries country', 'rs.person_country_id=country.id')
				->where(sprintf('(rs.event_id, rs.%s, rs.person_id) IN (%s)',
					$field,
					implode(',', $eventBestPerson)
				))
				->order(sprintf('rs.%s ASC, rs.person_id ASC', $field));
				foreach ($command->queryAll() as $row) {
					$row['type'] = $type;
					$row = Statistics::getCompetition($row);
					if (!isset($rows[$row['person_id']])) {
						$rows[$row['person_id']] = $row;
					}
				}
				$rows = array_values($rows);
			}
			$rank = isset($rows[0]) ?
				$cmd2
				->select('COUNT(DISTINCT person_id) AS count')
				->andWhere('best<' . $rows[0]['best'])
				->queryScalar()
				: 0;
			$data = array(
				'count'=>$count,
				'rows'=>$rows,
				'rank'=>$rank,
			);
			$cache->set($cacheKey, $data, $expire);
		}
		return $data;
	}

	public static function getRecord($region = 'China', $event = '333', $type = 'single', $date = null) {
		if ($type == 'best') {
			$type = 'single';
		}
		$records = self::getRecords('history', $region, $event, false);
		if (!isset($records[$type])) {
			return null;
		}
		if ($date === null) {
			return current($records[$type]) ?: null;
		}
		foreach ($records[$type] as $index=>$record) {
			// check the date
			if (strtotime(implode('-', [$record['year'], $record['month'], $record['day']])) < $date) {
				return $record;
			}
		}
		return current($records[$type]) ?: null;
	}

	public static function getRecords($type = 'current', $region = 'China', $event = '333', $merge = true) {
		$cache = Yii::app()->cache;
		$cacheKey = "results_records_{$type}_{$region}_{$event}";
		$expire = 86400 * 7;
		if (($data = $cache->get($cacheKey)) === false) {
			switch ($type) {
				case 'history':
					$data = self::getHistoryRecords($region, $event);
					break;
				default:
					$data = self::getCurrentRecords($region);
					break;
			}
			$cache->set($cacheKey, $data, $expire);
		}
		if ($merge) {
			$data = call_user_func_array('array_merge', array_values($data));
		}
		return $data;
	}

	public static function getHistoryRecords($region = 'China', $event = '333') {
		$command = Yii::app()->wcaDb->createCommand()
		->select(array(
			'rs.id',
			'rs.event_id',
			'rs.best',
			'rs.average',
			'rs.person_id',
			'rs.person_name',
			'rs.competition_id',
			'rs.regional_single_record',
			'rs.regional_average_record',
			'c.cell_name',
			'c.city_name',
			'c.year',
			'c.month',
			'c.day',
			'country.name AS country_name',
			'country.iso2',
		))
		->from('results rs')
		->leftJoin('competitions c', 'rs.competition_id=c.id')
		->leftJoin('round_types round', 'rs.round_type_id=round.id')
		->leftJoin('countries country', 'rs.person_country_id=country.id')
		->where('rs.event_id=:event_id', array(
			':event_id'=>$event,
		));
		$order = 'c.year DESC, c.month DESC, c.day DESC, rs.%s ASC, round.rank DESC, rs.person_name ASC';
		self::applyRegionCondition($command, $region);
		$rows = array();
		foreach (self::getRankingTypes() as $type) {
			$cmd = clone $command;
			switch ($region) {
				case 'World':
					$cmd->andWhere(sprintf('rs.regional_%s_record="WR"', $type));
					break;
				case 'Africa':
				case 'Asia':
				case 'Oceania':
				case 'Europe':
				case 'North America':
				case 'South America':
					$cmd->leftJoin('continents continent', 'country.continent_id=continent.id');
					$cmd->andWhere(sprintf('rs.regional_%s_record IN (continent.record_name, "WR")', $type));
					break;
				default:
					$cmd->andWhere(sprintf('rs.regional_%s_record!=""', $type));
					break;
			}
			$cmd->order(sprintf($order, $type === 'single' ? 'best' : $type));
			$rows[$type] = array();
			foreach ($cmd->queryAll() as $row) {
				$row['type'] = $type;
				$row = Statistics::getCompetition($row);
				$rows[$type][] = $row;
			}
		}
		return $rows;

	}

	public static function getCurrentRecords($region = 'China') {
		$command = Yii::app()->wcaDb->createCommand()
		->select(array(
			'r.*',
			'r.best AS average',
			'(CASE
				WHEN r.world_rank=1 THEN "WR"
				WHEN r.continent_rank=1 THEN continent.record_name
				ELSE "NR"
			END) AS record',
			'rs.id',
			'rs.person_name',
			'rs.competition_id',
			'c.cell_name',
			'c.city_name',
			'c.year',
			'c.month',
			'c.day',
			'country.name AS country_name',
			'country.iso2',
		))
		->leftJoin('events e', 'r.event_id=e.id')
		->leftJoin('persons p', 'r.person_id=p.wca_id AND p.sub_id=1')
		->leftJoin('countries country', 'p.country_id=country.id')
		->leftJoin('continents continent', 'country.continent_id=continent.id')
		->order('e.rank ASC');
		switch ($region) {
			case 'World':
				$command->where('r.world_rank=1');
				break;
			case 'Africa':
			case 'Asia':
			case 'Oceania':
			case 'Europe':
			case 'North America':
			case 'South America':
				$command->where('r.continent_rank=1 AND country.continent_id=:region', array(
					':region'=>'_' . $region,
				));
				break;
			default:
				$command->where('r.country_rank=1 AND rs.person_country_id=:region', array(
					':region'=>$region,
				));
				break;
		}
		$rows = array(
			'333'=>array(),
		);
		foreach (self::getRankingTypes() as $type) {
			$cmd = clone $command;
			$cmd->from(sprintf('ranks_%s r', $type))
			->leftJoin('results rs', sprintf('r.best=rs.%s AND r.person_id=rs.person_id AND r.event_id=rs.event_id', $type == 'single' ? 'best' : $type))
			->leftJoin('competitions c', 'rs.competition_id=c.id');
			foreach ($cmd->queryAll() as $row) {
				$row['type'] = $type;
				$row = Statistics::getCompetition($row);
				$rows[$row['event_id']][] = $row;
			}
		}
		return $rows;
	}

	public static function getMBFPoints($result) {
		$difference = 99 - substr($result, 0, 2);
		return $difference;
	}

	public static function formatImprovement($data) {
		if ($data['lastYearsBest'] === null || ($data['event'] !== '333mbf' && $data['improvement'] == 0)) {
			return '-';
		}
		if ($data['event'] !== '333mbf') {
			return self::formatTime($data['improvement'], $data['event']) . " ({$data['improvementPercent']}%)";
		} else {
			if ($data['improvement'] > 0) {
				return $data['improvement'] . " ({$data['improvementPercent']}%)";
			} else {
				$lastYearsTime = substr($data['lastYearsBest']->best, 2, -2);
				$thisYearsTime = substr($data['thisYearsBest']->best, 2, -2);
				$deltaTime = $lastYearsTime - $thisYearsTime;
				return self::formatTimeByEvent($deltaTime, '333mbf');
			}
		}
	}

	public static function formatTime($result, $event_id, $encode = true) {
		if ($result == -1) {
			return 'DNF';
		}
		if ($result == -2) {
			return 'DNS';
		}
		if ($result == 0) {
			return '';
		}
		if ($event_id === '333fm') {
			if ($result > 1000) {
				$time = sprintf('%.2f', $result / 100);
			} else {
				$time = $result;
			}
		} elseif ($event_id === '333mbf' || ($event_id === '333mbo' && strlen($result) == 9)) {
			$difference = 99 - substr($result, 0, 2);
			$missed = intval(substr($result, -2));
			$time = self::formatTimeByEvent(substr($result, 2, -2), $event_id);
			$solved = $difference + $missed;
			$attempted = $solved + $missed;
			$time = $solved . '/' . $attempted . ' ' . $time;
		} elseif ($event_id === '333mbo') {
			$solved = 99 - substr($result, 1, 2);
			$attempted = intval(substr($result, 3, 2));
			$time = self::formatTimeByEvent(substr($result, -5), $event_id);
			$time = $solved . '/' . $attempted . ' ' . $time;
		} else {
			$msecond = str_pad(substr($result, -2), 2, '0', STR_PAD_LEFT);
			$second = substr($result, 0, -2);
			$time = self::formatTimeByEvent(intval($second)) . '.' . $msecond;
		}
		if ($encode) {
			$time = CHtml::encode($time);
		}
		return $time;
	}

	/**
	 *
	 * @param int $time 要被格式化的时间
	 * @param str $event_id 项目ID，用于对多盲时间格式化方式的判断
	 */
	private static function formatTimeByEvent($time, $event_id = '') {
		$time = intval($time);
		if ($time === 99999 && substr($event_id, 0, -1) === '333mb') {
			return 'unknown';
		}
		if ($time == 0) {
			return '0';
		}

		$seconds = $time % 60;
		$minutes = intval($time / 60);
		if ($event_id === '333mbf') {
			return sprintf('%d:%02d', $minutes, $seconds);
		}
		$hours = intval($minutes / 60);
		$minutes = $minutes % 60;
		return ltrim(sprintf('%d:%02d:%02d', $hours, $minutes, $seconds), '0:');
	}

	public static function getDisplayDetail($data, $boldBest = false) {
		if (!isset($data['attempts'])) {
			$data['attempts'] = ResultAttempts::model()->findAllByAttributes([
				'result_id'=>$data['id'],
			]);
		}
		// sort attempts by attempt_number
		usort($data['attempts'], function($a, $b) {
			return $a['attempt_number'] - $b['attempt_number'];
		});
		$detail = array();
		foreach ($data['attempts'] as $attempt) {
			$value = $attempt['value'];
			$time = self::formatTime($value, $data['event_id']);
			$time = str_pad($time, $data['event_id'] === '333mbo' || $data['event_id'] === '333mbf' ? 12 : 7);
			if ($boldBest && $value === $data['best']) {
				$time = CHtml::Tag('b', array(), $time);
			}
			$detail[] = $time;
		}
		return CHtml::tag('pre', [], trim(implode('   ', $detail)));
	}

	public function getTime($attribute, $highlight = true, $showRecord = false) {
		$time = self::formatTime($this->$attribute, $this->event_id);
		if ($highlight && (($attribute == 'best' && $this->newBest) || ($attribute == 'average' && $this->newAverage))) {
			$time = '<span class="new-best">' . $time . '</strong>';
		}
		if ($showRecord) {
			$temp = sprintf('regional_%s_record', $attribute === 'best' ? 'single' : 'average');
			$record = $this->$temp;
			$class = $record == 'WR' || $record == 'NR' ? strtolower($record) : 'cr';
			$time = CHtml::tag('span', ['class'=>'record record-' . $class], $record) . ' ' . $time;
		}
		return $time;
	}

	public function getCompetitionLink() {
		return $this->competition->getCompetitionLink();
	}

	public function getDetail($boldBest = false) {
		return self::getDisplayDetail($this->attributes + ['attempts'=>$this->attempts], $boldBest);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'results';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('pos, best, average', 'numerical', 'integerOnly'=>true),
			array('competition_id', 'length', 'max'=>32),
			array('event_id', 'length', 'max'=>6),
			array('round_type_id, format_id', 'length', 'max'=>1),
			array('person_name', 'length', 'max'=>80),
			array('person_id', 'length', 'max'=>10),
			array('person_country_id', 'length', 'max'=>50),
			array('regional_single_record, regional_average_record', 'length', 'max'=>3),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, event_id, round_type_id, pos, best, average, person_name, person_id, person_country_id, format_id, regional_single_record, regional_average_record', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'person'=>array(self::BELONGS_TO, 'Persons', 'person_id'),
			'personCountry'=>array(self::BELONGS_TO, 'Countries', 'person_country_id'),
			'competition'=>array(self::BELONGS_TO, 'Competitions', 'competition_id'),
			'round'=>array(self::BELONGS_TO, 'RoundTypes', 'round_type_id'),
			'event'=>array(self::BELONGS_TO, 'Events', 'event_id'),
			'format'=>array(self::BELONGS_TO, 'Formats', 'format_id'),
			'attempts'=>array(self::HAS_MANY, 'ResultAttempts', 'result_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('results', 'ID'),
			'competition_id' => Yii::t('results', 'Competition'),
			'event_id' => Yii::t('results', 'Event'),
			'round_type_id' => Yii::t('results', 'Round'),
			'pos' => Yii::t('results', 'Pos'),
			'best' => Yii::t('results', 'Best'),
			'average' => Yii::t('results', 'Average'),
			'person_name' => Yii::t('results', 'Person Name'),
			'person_id' => Yii::t('results', 'Person'),
			'person_country_id' => Yii::t('results', 'Country'),
			'format_id' => Yii::t('results', 'Format'),
			'regional_single_record' => Yii::t('results', 'Regional Single Record'),
			'regional_average_record' => Yii::t('results', 'Regional Average Record'),
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
		$criteria->compare('competition_id',$this->competition_id,true);
		$criteria->compare('event_id',$this->event_id,true);
		$criteria->compare('round_type_id',$this->round_type_id,true);
		$criteria->compare('pos',$this->pos);
		$criteria->compare('best',$this->best);
		$criteria->compare('average',$this->average);
		$criteria->compare('person_name',$this->person_name,true);
		$criteria->compare('person_id',$this->person_id,true);
		$criteria->compare('person_country_id',$this->person_country_id,true);
		$criteria->compare('format_id',$this->format_id,true);
		$criteria->compare('regional_single_record',$this->regional_single_record,true);
		$criteria->compare('regional_average_record',$this->regional_average_record,true);

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
	 * @return results the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
