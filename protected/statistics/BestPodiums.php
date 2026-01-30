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
		$type = self::getType($event_id);
		$command = Yii::app()->wcaDb->createCommand();
		$command->select(array(
			'r.competition_id',
			'r.event_id',
			'r.round_type_id',
			self::getSelectSum($event_id, $type),
			'c.cell_name',
			'c.city_name',
			'c.year',
			'c.month',
			'c.day',
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
		$statistic['count'] = $cmd->select('count(DISTINCT r.competition_id) AS count')->queryScalar();
		$statistic['rank'] = ($page - 1) * self::$limit;
		$statistic['rankKey'] = 'sum';
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

	private static function getSelectSum($event_id, $type) {
		if ($event_id === '333fm') {
			$str = 'CASE WHEN c.year<2014 THEN best*100 ELSE (CASE WHEN average=0 THEN best*100 ELSE average END) END';
		} else {
			$str = $type;
		}
		return sprintf('CASE WHEN count(pos)>3 THEN sum(DISTINCT %s) ELSE sum(%s) END AS sum', $str, $str);
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
				return round($row['sum'] / 300, 2);
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
				return $row['sum'] / 100;
			default:
				return Results::formatTime($row['sum'], $row['event_id']);
		}
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
		if ($row['event_id'] === '333fm') {
			$type = 'CASE WHEN year<2014 THEN best ELSE (CASE WHEN average=0 THEN best ELSE average END) END';
		}
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
