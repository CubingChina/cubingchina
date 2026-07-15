<?php

/**
 * This is the model class for table "live_result".
 *
 * The followings are the available columns in table 'live_result':
 * @property string $id
 * @property string $competition_id
 * @property string $user_id
 * @property integer $user_type
 * @property integer $number
 * @property string $event
 * @property string $round
 * @property string $format
 * @property integer $best
 * @property integer $average
 * @property integer $value1
 * @property integer $value2
 * @property integer $value3
 * @property integer $value4
 * @property integer $value5
 * @property string $regional_single_record
 * @property string $regional_average_record
 * @property string $operator_id
 * @property integer $status
 * @property string $create_time
 * @property string $update_time
 */
class LiveResult extends ActiveRecord {

	const USER_TYPE_LIVE = 1;

	public static $records = array();
	public static $liveRecords = array();

	public $pos;
	public $subEventTitle;

	public static function formatTime($result, $eventId) {
		if ($result == -1) {
			return 'DNF';
		}
		if ($result == -2) {
			return 'DNS';
		}
		if ($result == 0) {
			return '';
		}
		if ($eventId === '333fm') {
			if ($result > 1000) {
				$time = sprintf('%.2f', $result / 100);
			} else {
				$time = $result;
			}
		} elseif ($eventId === '333mbf') {
			$time = substr($result, 3, -2);
		} else {
			$msecond = substr($result, -2);
			$second = substr($result, 0, -2);
			$time = $second . '.' . $msecond;
		}
		return $time;
	}

	public function getShowAttributes($calcPos = false) {
		//use the first letter to save traffic
		$attributes = array(
			'i'=>$this->id,
			'c'=>$this->competition_id,
			// 'user'=>array(
			// 	'type'=>$this->user_type,
			// 	'id'=>$this->user_id,
			// 	'name'=>$this->user->getCompetitionName(),
			// 	'wcaid'=>$this->user->wcaid,
			// 	'region'=>$this->user->country->name,
			// ),
			// 'r'=>$this->user->country->name,
			'n'=>$this->number,
			'e'=>$this->event,
			'r'=>$this->round,
			'f'=>$this->format,
			// 'p'=>'',
			'b'=>intval($this->best),
			'a'=>intval($this->average),
			'v'=>[
				intval($this->value1),
				intval($this->value2),
				intval($this->value3),
				intval($this->value4),
				intval($this->value5),
			],
			'sr'=>$this->regional_single_record,
			'ar'=>$this->regional_average_record,
		);
		if ($calcPos) {
			$attributes['p'] = $this->getCalculatedPos();
			$attributes['nb'] = $attributes['na'] = false;
		}
		return $attributes;
	}

	public function getEvents() {
		return [$this->event];
	}

	/**
	 * Compare two results for ranking purposes. Returns a negative number when
	 * $a ranks ahead of $b, positive when behind, 0 when tied.
	 * $a and $b expose integer `best` and `average` properties.
	 */
	public static function compareResults($a, $b, $format) {
		$temp = 0;
		if ($format == 'a' || $format == 'm') {
			if ($a->average > 0 && $b->average <= 0) {
				return -1;
			}
			if ($b->average > 0 && $a->average <= 0) {
				return 1;
			}
			$temp = $a->average - $b->average;
		}
		if ($temp == 0) {
			if ($a->best > 0 && $b->best <= 0) {
				return -1;
			}
			if ($b->best > 0 && $a->best <= 0) {
				return 1;
			}
			$temp = $a->best - $b->best;
		}
		return $temp;
	}

	public static function getRankingFormat($formatId) {
		switch ($formatId) {
			case '1':
			case '2':
			case '3':
			case '5':
				return 'b';
			default:
				return 'a';
		}
	}

	public static function assignPositions(&$results, $format) {
		$format = self::getRankingFormat($format);
		$count = 0;
		$lastBest = 0;
		$lastAverage = 0;
		foreach ($results as $i=>&$result) {
			$average = is_array($result) ? $result['average'] : $result->average;
			$best = is_array($result) ? $result['best'] : $result->best;
			if ($format == 'a') {
				if ($average != $lastAverage) {
					$lastAverage = $average;
					$lastBest = $best;
					$pos = $i + 1;
					$count = $i;
				} elseif ($best != $lastBest) {
					$lastBest = $best;
					$pos = $i + 1;
					$count = $i;
				} else {
					$pos = $count + 1;
				}
			} else {
				if ($best != $lastBest) {
					$lastBest = $best;
					$pos = $i + 1;
					$count = $i;
				} else {
					$pos = $count + 1;
				}
			}
			if (is_array($result)) {
				$result['pos'] = $pos;
			} else {
				$result->pos = $pos;
			}
		}
	}

	/**
	 * Live path: combined dual podiums when the official c/f round is the
	 * second dual round and both dual rounds are closed.
	 */
	public static function isLiveCombinedDualPodiumRound($eventRound, $dualRounds = null) {
		if ($dualRounds === null) {
			$dualRounds = $eventRound->dualRounds;
		}
		return count($dualRounds) >= 2
			&& $dualRounds[0]->isClosed
			&& $dualRounds[1]->isClosed
			&& $eventRound->id == $dualRounds[1]->id;
	}

	/**
	 * WCA path: combined dual podiums when the second dual round is c/f and no
	 * separate c/f round follows (e.g. 333fm 1+f yes, 1+2+f no).
	 *
	 * @param array $roundRanks map roundTypeId => rank
	 */
	public static function isWcaCombinedDualPodium($roundRanks, $dualRound2) {
		if (!in_array($dualRound2, array('c', 'f'), true) || !isset($roundRanks[$dualRound2])) {
			return false;
		}
		$dualRound2Rank = $roundRanks[$dualRound2];
		foreach ($roundRanks as $roundTypeId=>$rank) {
			if (in_array($roundTypeId, array('c', 'f'), true)
				&& $roundTypeId !== $dualRound2
				&& $rank > $dualRound2Rank) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Pick the better of two dual-round results (WCA Reg 9v4).
	 * @return array{better:mixed,betterRound:string}|null
	 */
	private static function pickBetterDualResult($r1, $r2, $format) {
		if ($r1 === null && $r2 === null) {
			return null;
		}
		if ($r1 === null) {
			return array('better'=>$r2, 'betterRound'=>'r2');
		}
		if ($r2 === null) {
			return array('better'=>$r1, 'betterRound'=>'r1');
		}
		if (self::compareResults(self::resultForCompare($r1), self::resultForCompare($r2), $format) <= 0) {
			return array('better'=>$r1, 'betterRound'=>'r1');
		}
		return array('better'=>$r2, 'betterRound'=>'r2');
	}

	/**
	 * Combine dual-round pairs per competitor, pick the better result, optionally
	 * filter invalid bests, and sort best to worst.
	 *
	 * @param array $pairsByCompetitor competitorKey => ['r1'=>..., 'r2'=>...]
	 * @param callable $tieBreaker function(array $a, array $b): int
	 * @return array list of ['key', 'r1', 'r2', 'better', 'betterRound']
	 */
	public static function rankCombinedPairs($pairsByCompetitor, $format, $tieBreaker, $requireValidBest = true) {
		$format = self::getRankingFormat($format);
		$combined = array();
		foreach ($pairsByCompetitor as $key=>$pair) {
			$r1 = isset($pair['r1']) ? $pair['r1'] : null;
			$r2 = isset($pair['r2']) ? $pair['r2'] : null;
			$picked = self::pickBetterDualResult($r1, $r2, $format);
			if ($picked === null) {
				continue;
			}
			$better = $picked['better'];
			if ($requireValidBest) {
				$best = is_array($better) ? $better['best'] : $better->best;
				if ($best <= 0) {
					continue;
				}
			}
			$combined[] = array(
				'key'=>$key,
				'r1'=>$r1,
				'r2'=>$r2,
				'better'=>$better,
				'betterRound'=>$picked['betterRound'],
			);
		}
		usort($combined, function($a, $b) use ($format, $tieBreaker) {
			$temp = self::compareResults(
				self::resultForCompare($a['better']),
				self::resultForCompare($b['better']),
				$format
			);
			if ($temp != 0) {
				return $temp;
			}
			return $tieBreaker($a, $b);
		});
		return $combined;
	}

	public static function tieBreakByNumberKey($a, $b) {
		return $a['key'] - $b['key'];
	}

	public static function tieBreakByCompetitorKey($a, $b) {
		return strcmp($a['key'], $b['key']);
	}

	/**
	 * Pick the two lowest-rank round type ids from a roundTypeId => rank map.
	 * @return array{0:string,1:string}|null null when fewer than two rounds
	 */
	public static function resolveDualRoundTypes($roundRanks) {
		if (count($roundRanks) < 2) {
			return null;
		}
		$sortedRanks = $roundRanks;
		asort($sortedRanks);
		$roundTypes = array_map('strval', array_keys($sortedRanks));
		return array($roundTypes[0], $roundTypes[1]);
	}

	/**
	 * Pair dual-round results by competitor, pick the better of each pair
	 * (WCA Reg 9v4), and sort best to worst.
	 * @param array $eventResults results with round_type_id, person_id, format_id
	 * @return array{0:array,1:string|null} list of better results and the format id
	 */
	public static function buildCombinedDualResults($eventResults, $round1, $round2) {
		$byPerson = array();
		$format = null;
		foreach ($eventResults as $result) {
			if ($result->round_type_id === $round1) {
				$byPerson[$result->person_id]['r1'] = $result;
				if ($format === null) {
					$format = $result->format_id;
				}
			} elseif ($result->round_type_id === $round2) {
				$byPerson[$result->person_id]['r2'] = $result;
				if ($format === null) {
					$format = $result->format_id;
				}
			}
		}
		if ($format === null) {
			return array(array(), null);
		}
		$ranked = self::rankCombinedPairs($byPerson, $format, array('LiveResult', 'tieBreakByCompetitorKey'));
		// Clone objects so assignPositions cannot overwrite per-round pos on shared Results.
		$betters = array();
		foreach ($ranked as $row) {
			$better = $row['better'];
			$betters[] = is_object($better) ? clone $better : $better;
		}
		return array($betters, $format);
	}

	private static function resultForCompare($result) {
		if (is_array($result)) {
			return (object) array(
				'best'=>(int) $result['best'],
				'average'=>(int) $result['average'],
			);
		}
		return $result;
	}

	public function getCalculatedPos() {
		if ($this->best == 0) {
			return '-';
		}
		$attributes = array(
			'competition_id'=>$this->competition_id,
			'event'=>$this->event,
			'round'=>$this->round,
		);
		$best = $this->best;
		$average = $this->average;
		$format = $this->eventRound === null ? 'a' : $this->eventRound->format;
		if ($format == 'a' || $format == 'm') {
			if ($average > 0) {
				$condition = '(average>0 AND average<:average) OR (average=:average AND best<:best)';
			} elseif ($average < 0) {
				if ($best > 0) {
					$condition = 'average>0 OR (average<0 AND best>0 AND best<:best)';
				} else {
					$condition = 'average>0 OR (average<0 AND best>0)';
				}
			} else {
				if ($best > 0) {
					$condition = 'average!=0 OR (average=0 AND best>0 AND best<:best)';
				} else {
					$condition = 'average!=0 OR (average=0 AND best>0)';
				}
			}
		} else {
			if ($best > 0) {
				$condition = 'best>0 AND best<:best';
			} else {
				$condition = 'best>0';
			}
		}
		$params = array();
		if (($format == 'a' || $format == 'm') && $average > 0) {
			$params[':average'] = $average;
			$params[':best'] = $best;
		} elseif ($best > 0) {
			$params[':best'] = $best;
		}
		return self::model()->countByAttributes($attributes, array(
			'condition'=>$condition,
			'params'=>$params,
		)) + 1;
	}

	// public function getUser() {
	// 	return $this->user_type == self::USER_TYPE_LIVE ? $this->liveUser : $this->realUser;
	// }

	public function isProbablyRecord() {
		$date = $this->competition->date;
		foreach (['best', 'average'] as $type) {
			$NR = Results::getRecord($this->user->country->wcaCountry->id, $this->event, $type, $date);
			if ($this->$type <= $NR[$type]) {
				return true;
			}
		}
		return false;
	}

	public function isNotProbablyRecord() {
		$date = $this->competition->date;
		foreach (['best', 'average'] as $type) {
			$NR = Results::getRecord($this->user->country->wcaCountry->id, $this->event, $type, $date);
			if ($this->$type > $NR[$type]) {
				return true;
			}
		}
		return false;
	}

	public function shouldComputeRecord() {
		if (!$this->competition->isWCACompetition()) {
			return false;
		}
		return $this->isProbablyRecord() || $this->isRecord() && $this->isNotProbablyRecord();
	}

	public function isRecord() {
		return $this->regional_single_record != '' || $this->regional_average_record != '';
	}

	public function getDetail() {
		$data = $this->attributes;
		$data['eventId'] = $data['event'];
		return Results::getDisplayDetail($data);
	}

	public function getRecord($type) {
		$attribute = 'regional_' . $type . '_record';
		if ($this->$attribute) {
			$record = strtolower($this->$attribute);
			$record = in_array($record, ['nr', 'wr']) ? $record : 'cr';
			return CHtml::tag('span', ['class'=>'record record-' . $record], $this->$attribute);
		}
	}

	public function getSortClass() {
		$eventRound = $this->eventRound;
		switch ($eventRound->format) {
			case '1':
			case '2':
			case '3':
			case '5':
				return 'sort-by-best';
			default:
				return 'sort-by-average';
		}
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_result';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('competition_id, user_id, number', 'required'),
			array('user_type, number, best, average, value1, value2, value3, value4, value5, status', 'numerical', 'integerOnly'=>true),
			array('competition_id, user_id, operator_id, create_time, update_time', 'length', 'max'=>10),
			array('event', 'length', 'max'=>32),
			array('round, format', 'length', 'max'=>1),
			array('regional_single_record, regional_average_record', 'length', 'max'=>3),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, user_id, user_type, number, event, round, format, best, average, value1, value2, value3, value4, value5, regional_single_record, regional_average_record, operator_id, status, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'competition'=>array(self::BELONGS_TO, 'Competition', 'competition_id'),
			'user'=>array(self::BELONGS_TO, 'User', 'user_id'),
			'eventRound'=>array(self::BELONGS_TO, 'LiveEventRound', array(
				'competition_id'=>'competition_id',
				'event'=>'event',
				'round'=>'round',
			)),
			'wcaEvent'=>array(self::BELONGS_TO, 'Events', 'event'),
			'wcaRound'=>array(self::BELONGS_TO, 'RoundTypes', 'round'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'competition_id' => 'Competition',
			'user_id' => 'User',
			'user_type' => 'User Type',
			'number' => 'Number',
			'event' => 'Event',
			'round' => 'Round',
			'format' => 'Format',
			'best' => 'Best',
			'average' => 'Average',
			'value1' => 'Value1',
			'value2' => 'Value2',
			'value3' => 'Value3',
			'value4' => 'Value4',
			'value5' => 'Value5',
			'regional_single_record' => 'Regional Single Record',
			'regional_average_record' => 'Regional Average Record',
			'operator_id' => 'Operator',
			'status' => 'Status',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
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

		$criteria->compare('id', $this->id, true);
		$criteria->compare('competition_id', $this->competition_id, true);
		$criteria->compare('user_id', $this->user_id, true);
		$criteria->compare('user_type', $this->user_type);
		$criteria->compare('number', $this->number);
		$criteria->compare('event', $this->event, true);
		$criteria->compare('round', $this->round, true);
		$criteria->compare('format', $this->format, true);
		$criteria->compare('best', $this->best);
		$criteria->compare('average', $this->average);
		$criteria->compare('value1', $this->value1);
		$criteria->compare('value2', $this->value2);
		$criteria->compare('value3', $this->value3);
		$criteria->compare('value4', $this->value4);
		$criteria->compare('value5', $this->value5);
		$criteria->compare('regional_single_record', $this->regional_single_record, true);
		$criteria->compare('regional_average_record', $this->regional_average_record, true);
		$criteria->compare('operator_id', $this->operator_id, true);
		$criteria->compare('status', $this->status);
		$criteria->compare('create_time', $this->create_time, true);
		$criteria->compare('update_time', $this->update_time, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return LiveResult the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
