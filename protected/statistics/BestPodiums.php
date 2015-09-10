<?php

class BestPodiums extends Statistics {

	public static function build($statistic, $page = 1) {
		if ($statistic['type'] === 'all') {
			$bestPodiums = array();
			$eventIds = array_keys(Events::getNormalEvents());
			$temp = $statistic;
			$temp['type'] = 'single';
			foreach ($eventIds as $eventId) {
				$temp['eventId'] = $eventId;
				$bestPodiums[$eventId] = self::build($temp);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>$bestPodiums,
				'select'=>Events::getNormalEvents(),
				'selectHandler'=>'Yii::t("event", "$name")',
				'selectKey'=>'event',
			));
		}
		$eventId = $statistic['eventId'];
		$type = self::getType($eventId);
		$command = Yii::app()->wcaDb->createCommand();
		$command->select(array(
			'r.competitionId',
			'r.eventId',
			'r.roundId',
			self::getSelectSum($eventId, $type),
			'c.cellName',
			'c.cityName',
			'c.year',
			'c.month',
			'c.day',
		))
		->from('Results r')
		->leftJoin('Competitions c', 'r.competitionId=c.id')
		->where('r.eventId=:eventId', array(
			':eventId'=>$eventId,
		))
		->andWhere('r.roundId IN ("c", "f")')
		->andWhere('r.pos IN (1,2,3)')
		->andWhere('c.countryId="China"')
		->andWhere("r.{$type} > 0");
		$cmd = clone $command;
		$command->group('r.competitionId')
		->order('sum ASC')
		->having('count(DISTINCT pos)<=3 AND count(pos)>=3')
		->limit(self::$limit)
		->offset(($page - 1) * self::$limit);
		$columns = array(
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
		$rows = array();
		foreach ($command->queryAll() as $row) {
			$row = self::getCompetition($row);
			self::setPodiumsResults($row, $type);
			$row['formatedSum'] = self::formatSum($row);
			$row['formatedAverage'] = self::formatAverage($row);
			$row['date'] = sprintf("%d-%02d-%02d", $row['year'], $row['month'], $row['day']);
			$rows[] = $row;
		}
		$statistic['count'] = $cmd->select('count(DISTINCT r.competitionId) AS count')->queryScalar();
		$statistic['rank'] = ($page - 1) * self::$limit;
		$statistic['rankKey'] = 'sum';
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

	private static function getSelectSum($eventId, $type) {
		if ($eventId === '333fm') {
			$str = 'CASE WHEN c.year<2014 THEN best*100 ELSE (CASE WHEN average=0 THEN best*100 ELSE average END) END';
		} else {
			$str = $type;
		}
		return sprintf('CASE WHEN count(pos)>3 THEN sum(DISTINCT %s) ELSE sum(%s) END AS sum', $str, $str);
	}

	private static function formatAverage($row) {
		switch ($row['eventId']) {
			case '333mbf':
				return round(array_sum(array_map(function($row) {
					$result = $row[0]['average'];
					$difference = 99 - substr($result, 0, 2);
					return $difference;
				}, array($row['first'], $row['second'], $row['third']))) / 3, 2);
			case '333fm':
				return round($row['sum'] / 300, 2);
			default:
				return Results::formatTime(round($row['sum'] / 3), $row['eventId']);
		}
	}

	private static function formatSum($row) {
		switch ($row['eventId']) {
			case '333mbf':
				return array_sum(array_map(function($row) {
					$result = $row[0]['average'];
					$difference = 99 - substr($result, 0, 2);
					return $difference;
				}, array($row['first'], $row['second'], $row['third'])));
			case '333fm':
				return $row['sum'] / 100;
			default:
				return Results::formatTime($row['sum'], $row['eventId']);
		}
	}

	private static function makePosValue($pos) {
		return 'implode(" / ", array_map(function($row) {
			return Persons::getLinkByNameNId($row["personName"], $row["personId"]);
		}, $data["' . $pos . '"]))';
	}

	private static function makePosResultValue($pos) {
		return sprintf('isset($data["%s"][0]) ? Results::formatTime($data["%s"][0]["average"], $data["eventId"]) : "-"', $pos, $pos);
	}

	private static function getType($eventId) {
		if (in_array("$eventId", array('333fm', '333bf', '444bf', '555bf', '333mbf'))) {
			return 'best';
		}
		return 'average';
	}

	private static function setPodiumsResults(&$row, $type) {
		if ($row['eventId'] === '333fm') {
			$type = 'CASE WHEN year<2014 THEN best ELSE (CASE WHEN average=0 THEN best ELSE average END) END';
		}
		$results = Yii::app()->wcaDb->createCommand()
		->select("personId, personName, {$type} AS average, pos")
		->from('Results r')
		->leftJoin('Competitions c', 'r.competitionId=c.id')
		->where('competitionId=:competitionId AND eventId=:eventId AND roundId=:roundId AND pos IN (1,2,3)', array(
			':competitionId'=>$row['competitionId'],
			':eventId'=>$row['eventId'],
			':roundId'=>$row['roundId'],
		))
		->queryAll();
		$keys = array(
			1=>'first',
			2=>'second',
			3=>'third',
		);
		$row['first'] = $row['second'] = $row['third'] = array();
		foreach ($results as $result) {
			$row[$keys[$result['pos']]][] = $result;
		}
	}
}
