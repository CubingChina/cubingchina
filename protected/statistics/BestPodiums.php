<?php

class BestPodiums extends Statistics {

	public static function build($statistic, $page = 1) {
		if ($statistic['type'] === 'all') {
			$bestPodiums = array();
			$eventIds = array_keys(Events::getNormalEvents());
			$temp = $statistic;
			$temp['type'] = 'single';
			foreach ($eventIds as $event_id) {
				$temp['event_id'] = $event_id;
				$bestPodiums[$event_id] = self::build($temp);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>$bestPodiums,
				'select'=>Events::getNormalEvents(),
				'selectHandler'=>'Yii::t("event", "$name")',
				'selectKey'=>'event',
			));
		}
		$event_id = $statistic['event_id'];
		if ($event_id === '333fm') {
			return self::build333fmPodiums($statistic, $page);
		}
		$columns = self::getColumns();
		$type = self::getType($event_id);
		$command = Yii::app()->wcaDb->createCommand();
		$command->select(array(
			'r.competition_id',
			'MAX(r.event_id) AS event_id',
			'MAX(r.round_type_id) AS round_type_id',
			self::getSelectSum($type),
			'MAX(c.cell_name) AS cell_name',
			'MAX(c.city_name) AS city_name',
			'MAX(c.year) AS year',
			'MAX(c.month) AS month',
			'MAX(c.day) AS day',
		))
		->from('results r')
		->leftJoin('competitions c', 'r.competition_id=c.id')
		->where('r.event_id=:event_id', array(
			':event_id'=>$event_id,
		))
		->andWhere('r.round_type_id IN ("c", "f")')
		->andWhere('r.pos IN (1,2,3)')
		->andWhere('c.country_id="China"')
		->andWhere("r.{$type} > 0");
		$cmd = clone $command;
		$command->group('r.competition_id')
		->order('sum ASC')
		->having('count(DISTINCT pos)<=3 AND count(pos)>=3')
		->limit(self::$limit)
		->offset(($page - 1) * self::$limit);
		$rows = array();
		foreach ($command->queryAll() as $row) {
			$rows[] = self::formatStandardRow($row, $type);
		}
		$statistic['count'] = $cmd->select('count(DISTINCT r.competition_id) AS count')->queryScalar();
		$statistic['rank'] = ($page - 1) * self::$limit;
		$statistic['rankKey'] = 'sum';
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

	private static function getColumns() {
		return array(
			array(
				'header'=>'Yii::t("common", "Competition")',
				'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("Competition", "Date")',
				'value'=>'$data["date"]',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "Sum")',
				'value'=>'CHtml::tag("b", array(), $data["formatedSum"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("common", "Average")',
				'value'=>'$data["formatedAverage"]',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "First")',
				'value'=>self::makePosValue('first'),
				'type'=>'raw',
			),
			array(
				'header'=>'',
				'value'=>self::makePosResultValue('first'),
			),
			array(
				'header'=>'Yii::t("statistics", "Second")',
				'value'=>self::makePosValue('second'),
				'type'=>'raw',
			),
			array(
				'header'=>'',
				'value'=>self::makePosResultValue('second'),
			),
			array(
				'header'=>'Yii::t("statistics", "Third")',
				'value'=>self::makePosValue('third'),
				'type'=>'raw',
			),
			array(
				'header'=>'',
				'value'=>self::makePosResultValue('third'),
			),
		);
	}

	private static function build333fmPodiums($statistic, $page) {
		$data = self::load333fmData();
		$rankings = array();
		foreach ($data['competitions'] as $competition_id=>$competition) {
			$podium = self::get333fmPodiumFromData($competition_id, $data);
			if ($podium === null) {
				continue;
			}
			$rankings[] = array(
				'competition'=>$competition,
				'podium'=>$podium,
				'sum'=>$podium['sum'],
				'competition_id'=>$competition_id,
			);
		}
		usort($rankings, function($a, $b) {
			if ($a['sum'] == $b['sum']) {
				return strcmp($a['competition_id'], $b['competition_id']);
			}
			return $a['sum'] - $b['sum'];
		});
		$statistic['count'] = count($rankings);
		$statistic['rank'] = ($page - 1) * self::$limit;
		$statistic['rankKey'] = 'sum';
		$rows = array();
		foreach (array_slice($rankings, ($page - 1) * self::$limit, self::$limit) as $ranking) {
			$row = array_merge($ranking['competition'], $ranking['podium'], array(
				'event_id'=>'333fm',
			));
			$rows[] = self::format333fmRow($row);
		}
		return self::makeStatisticsData($statistic, self::getColumns(), $rows);
	}

	private static function load333fmData() {
		static $data;
		if ($data !== null) {
			return $data;
		}
		$competitions = Yii::app()->wcaDb->createCommand()
		->select('c.id AS competition_id, c.cell_name, c.city_name, c.year, c.month, c.day')
		->from('competitions c')
		->join('results r', 'r.competition_id=c.id AND r.event_id="333fm" AND r.best>0')
		->where('c.country_id="China"')
		->group('c.id, c.cell_name, c.city_name, c.year, c.month, c.day')
		->queryAll();
		$competitionsById = array();
		$competitionIds = array();
		foreach ($competitions as $competition) {
			$competitionsById[$competition['competition_id']] = $competition;
			$competitionIds[] = $competition['competition_id'];
		}
		$byCompetition = array();
		if ($competitionIds !== array()) {
			$results = self::attach333fmAttempts(Yii::app()->wcaDb->createCommand()
			->select('id, competition_id, person_id, person_name, best, average, pos, round_type_id, format_id, attempt, solve')
			->from('results')
			->where(array('in', 'competition_id', $competitionIds))
			->andWhere('event_id="333fm" AND best>0')
			->queryAll());
			foreach ($results as $result) {
				$competition_id = $result['competition_id'];
				if (!isset($byCompetition[$competition_id])) {
					$byCompetition[$competition_id] = array(
						'byRound'=>array(),
						'official'=>array(),
					);
				}
				$round_type_id = $result['round_type_id'];
				if (!isset($byCompetition[$competition_id]['byRound'][$round_type_id])) {
					$byCompetition[$competition_id]['byRound'][$round_type_id] = array();
				}
				$byCompetition[$competition_id]['byRound'][$round_type_id][] = $result;
				if (in_array($round_type_id, array('c', 'f'), true) && $result['pos'] >= 1 && $result['pos'] <= 3) {
					$byCompetition[$competition_id]['official'][] = $result;
				}
			}
		}
		$data = array(
			'competitions'=>$competitionsById,
			'byCompetition'=>$byCompetition,
		);
		return $data;
	}

	private static function formatStandardRow($row, $type) {
		$row = self::getCompetition($row);
		self::setPodiumsResults($row, $type);
		return self::formatDisplayFields($row);
	}

	private static function format333fmRow($row) {
		$row = self::getCompetition($row);
		return self::formatDisplayFields($row);
	}

	private static function formatDisplayFields($row) {
		$row['formatedSum'] = self::formatSum($row);
		$row['formatedAverage'] = self::formatAverage($row);
		$row['date'] = sprintf("%d-%02d-%02d", $row['year'], $row['month'], $row['day']);
		return $row;
	}

	private static function get333fmPodiumFromData($competition_id, $data) {
		$compData = isset($data['byCompetition'][$competition_id]) ? $data['byCompetition'][$competition_id] : null;
		if ($compData === null) {
			return null;
		}
		if (self::isDual333fmCompetition($competition_id)) {
			$round1 = isset($compData['byRound']['1']) ? $compData['byRound']['1'] : array();
			$roundf = isset($compData['byRound']['f']) ? $compData['byRound']['f'] : array();
			if ($round1 !== array() && $roundf !== array()) {
				$podium = self::buildCombined333fmPodium($round1, $roundf);
				if ($podium !== null) {
					$podium['round_type_id'] = 'f';
					return $podium;
				}
			}
		}
		return self::buildOfficial333fmPodiumFromResults($compData['official']);
	}

	private static function isDual333fmCompetition($competition_id) {
		static $ids;
		if ($ids === null) {
			$ids = array_flip(Yii::app()->db->createCommand()
			->selectDistinct('c.wca_competition_id')
			->from('competition c')
			->join('competition_event ce', 'ce.competition_id=c.id')
			->where('ce.event="333fm" AND ce.dual=1 AND c.wca_competition_id!=""')
			->queryColumn());
		}
		return isset($ids[$competition_id]);
	}

	private static function attach333fmAttempts($results) {
		if ($results === array()) {
			return $results;
		}
		$byResult = array();
		foreach (array_chunk(array_column($results, 'id'), 500) as $resultIds) {
			$attempts = Yii::app()->wcaDb->createCommand()
			->select('result_id, value')
			->from('result_attempts')
			->where(array('in', 'result_id', $resultIds))
			->order('attempt_number')
			->queryAll();
			foreach ($attempts as $attempt) {
				$byResult[$attempt['result_id']][] = (int) $attempt['value'];
			}
		}
		foreach ($results as &$result) {
			$result['attempt_values'] = isset($byResult[$result['id']]) ? $byResult[$result['id']] : array();
		}
		return $results;
	}

	private static function buildCombined333fmPodium($round1, $roundf) {
		$byPerson = array();
		foreach ($round1 as $result) {
			$byPerson[$result['person_id']]['r1'] = $result;
		}
		foreach ($roundf as $result) {
			$byPerson[$result['person_id']]['r2'] = $result;
		}
		$ranked = LiveResult::rankCombinedPairs($byPerson, 'm', array('LiveResult', 'tieBreakByCompetitorKey'), false);
		$combined = array_column($ranked, 'better');
		LiveResult::assignPositions($combined, 'm');
		return self::make333fmPodiumFromRanked($combined);
	}

	private static function buildOfficial333fmPodiumFromResults($results) {
		if (count($results) < 3) {
			return null;
		}
		$distinctPos = count(array_unique(array_map(function($result) {
			return $result['pos'];
		}, $results)));
		if ($distinctPos > 3) {
			return null;
		}
		$round_type_id = null;
		foreach ($results as $result) {
			if ($result['round_type_id'] === 'f') {
				$round_type_id = 'f';
				break;
			}
			$round_type_id = $result['round_type_id'];
		}
		return self::finalize333fmPodium(self::group333fmPodiumResults($results, $round_type_id), $round_type_id);
	}

	private static function make333fmPodiumFromRanked($ranked) {
		return self::finalize333fmPodium(self::group333fmPodiumEntries($ranked));
	}

	private static function finalize333fmPodium($groups, $round_type_id = 'f') {
		$all = array_merge($groups['first'], $groups['second'], $groups['third']);
		if (count($all) < 3) {
			return null;
		}
		$moveSumTotal = 0;
		$solveCountTotal = 0;
		foreach ($all as $entry) {
			$moveSumTotal += $entry['move_sum'];
			$solveCountTotal += $entry['solve_count'];
		}
		if ($solveCountTotal <= 0) {
			return null;
		}
		$podiumCount = count($all);
		$distinctSumCenti = self::sumDistinct333fmScores($all);
		if ($podiumCount > 3) {
			$rankSum = (int) round($distinctSumCenti / 3);
		} else {
			$rankSum = (int) round($moveSumTotal * 100 / $solveCountTotal);
		}
		return array(
			'round_type_id'=>$round_type_id,
			'sum'=>$rankSum,
			'move_sum_total'=>$moveSumTotal,
			'solve_count_total'=>$solveCountTotal,
			'podium_count'=>$podiumCount,
			'distinct_sum_centi'=>$distinctSumCenti,
			'first'=>$groups['first'],
			'second'=>$groups['second'],
			'third'=>$groups['third'],
		);
	}

	private static function sumDistinct333fmScores($entries) {
		$seen = array();
		$total = 0;
		foreach ($entries as $entry) {
			if (isset($seen[$entry['average']])) {
				continue;
			}
			$seen[$entry['average']] = true;
			$total += $entry['average'];
		}
		return $total;
	}

	private static function group333fmPodiumResults($results, $round_type_id) {
		return self::group333fmPodiumEntries($results, function($result) use ($round_type_id) {
			return $result['round_type_id'] === $round_type_id;
		});
	}

	private static function group333fmPodiumEntries($results, $filter = null) {
		$groups = array(
			'first'=>array(),
			'second'=>array(),
			'third'=>array(),
		);
		foreach ($results as $result) {
			if ($filter !== null && !$filter($result)) {
				continue;
			}
			if ($result['pos'] < 1 || $result['pos'] > 3) {
				continue;
			}
			$groups[self::getPodiumPosKeys()[$result['pos']]][] = self::format333fmPodiumEntry($result);
		}
		return $groups;
	}

	private static function getPodiumPosKeys() {
		return array(
			1=>'first',
			2=>'second',
			3=>'third',
		);
	}

	private static function format333fmPodiumEntry($result) {
		$attemptValues = self::get333fmAttemptValues($result);
		$moveSum = 0;
		$solveCount = 0;
		foreach ($attemptValues as $value) {
			if ($value > 0) {
				$moveSum += $value;
				$solveCount++;
			}
		}
		if ($moveSum <= 0 && (int) $result['best'] > 0) {
			$moveSum = (int) $result['best'];
			$solveCount = 1;
		}
		if ((int) $result['average'] > 0) {
			$average = (int) $result['average'];
		} elseif ($solveCount > 0) {
			$average = (int) round($moveSum * 100 / $solveCount);
		} elseif ((int) $result['best'] > 0) {
			$average = (int) $result['best'] * 100;
		} else {
			$average = 0;
		}
		return array(
			'person_id'=>$result['person_id'],
			'person_name'=>$result['person_name'],
			'average'=>$average,
			'move_sum'=>$moveSum,
			'solve_count'=>$solveCount,
		);
	}

	private static function get333fmAttemptValues($result) {
		return isset($result['attempt_values']) ? $result['attempt_values'] : array();
	}

	private static function getSelectSum($type) {
		return sprintf('CASE WHEN count(pos)>3 THEN sum(DISTINCT %s) ELSE sum(%s) END AS sum', $type, $type);
	}

	private static function formatAverage($row) {
		switch ($row['event_id']) {
			case '333mbf':
				return round(array_sum(array_map(function($row) {
					$result = $row[0]['average'];
					$difference = 99 - substr($result, 0, 2);
					return $difference;
				}, array($row['first'], $row['second'], $row['third']))) / 3, 2);
			case '333fm':
				if (!empty($row['podium_count']) && $row['podium_count'] > 3) {
					return round($row['distinct_sum_centi'] / 300, 2);
				}
				return round($row['sum'] / 100, 2);
			default:
				return Results::formatTime(round($row['sum'] / 3), $row['event_id']);
		}
	}

	private static function formatSum($row) {
		switch ($row['event_id']) {
			case '333mbf':
				return array_sum(array_map(function($row) {
					$result = $row[0]['average'];
					$difference = 99 - substr($result, 0, 2);
					return $difference;
				}, array($row['first'], $row['second'], $row['third'])));
			case '333fm':
				return self::format333fmSum($row);
			default:
				return Results::formatTime($row['sum'], $row['event_id']);
		}
	}

	private static function format333fmSum($row) {
		if (!empty($row['podium_count']) && $row['podium_count'] > 3) {
			return self::format333fmScore($row['distinct_sum_centi']);
		}
		$moveSumTotal = (int) $row['move_sum_total'];
		$solveCountTotal = (int) $row['solve_count_total'];
		if ($solveCountTotal === 3) {
			return (string) $moveSumTotal;
		}
		if ($solveCountTotal === 9) {
			return self::format333fmMixedThird($moveSumTotal);
		}
		$total = 0;
		foreach (array('first', 'second', 'third') as $pos) {
			if (empty($row[$pos])) {
				continue;
			}
			foreach ($row[$pos] as $person) {
				$total += $person['average'];
			}
		}
		return self::format333fmScore($total);
	}

	private static function format333fmScore($centi) {
		$value = $centi / 100;
		if ($value == (int) $value) {
			return (string) (int) $value;
		}
		return sprintf('%.2f', $value);
	}

	private static function format333fmMixedThird($moveSum) {
		$quotient = intdiv($moveSum, 3);
		$remainder = $moveSum % 3;
		if ($remainder === 0) {
			return (string) $quotient;
		}
		$decimals = array(
			1=>'.33',
			2=>'.67',
		);
		return $quotient . $decimals[$remainder];
	}

	private static function makePosValue($pos) {
		return 'implode(" / ", array_map(function($row) {
			return Persons::getLinkByNameNId($row["person_name"], $row["person_id"]);
		}, $data["' . $pos . '"]))';
	}

	private static function makePosResultValue($pos) {
		return sprintf('isset($data["%s"][0]) ? Results::formatTime($data["%s"][0]["average"], $data["event_id"]) : "-"', $pos, $pos);
	}

	private static function getType($event_id) {
		if (in_array("$event_id", array('333fm', '333bf', '444bf', '555bf', '333mbf'))) {
			return 'best';
		}
		return 'average';
	}

	private static function setPodiumsResults(&$row, $type) {
		$results = Yii::app()->wcaDb->createCommand()
		->select("person_id, person_name, {$type} AS average, pos")
		->from('results r')
		->leftJoin('competitions c', 'r.competition_id=c.id')
		->where('competition_id=:competition_id AND event_id=:event_id AND round_type_id=:round_type_id AND pos IN (1,2,3)', array(
			':competition_id'=>$row['competition_id'],
			':event_id'=>$row['event_id'],
			':round_type_id'=>$row['round_type_id'],
		))
		->queryAll();
		$keys = self::getPodiumPosKeys();
		$row['first'] = $row['second'] = $row['third'] = array();
		foreach ($results as $result) {
			$row[$keys[$result['pos']]][] = $result;
		}
	}
}
