<?php

class AllEventsAchiever extends Statistics {
	public static function build($statistic) {
		$db = Yii::app()->wcaDb;
		$sum = 0;
		foreach (['single', 'average'] as $type) {
			$num = $db->createCommand()
				->select('count(distinct event_id)')
				->from('ranks_' . $type)
				->leftJoin('events e', 'e.id=event_id')
				->where('e.`rank`<900')
				->queryScalar();
			$sum += $num;
		}
		$cmd = $db->createCommand()
			->select([
				'rs.person_id',
				'p.name AS person_name',
				'p.country_id',
				'country.iso2',
				'COUNT(DISTINCT rs.event_id) AS singles',
				'COUNT(DISTINCT ra.event_id) AS averages',
			])
			->from('ranks_single rs')
			->leftJoin('ranks_average ra', 'rs.person_id=ra.person_id AND rs.event_id=ra.event_id')
			->leftJoin('persons p', 'rs.person_id=p.wca_id AND p.sub_id=1')
			->leftJoin('countries country', 'p.country_id=country.id')
			->leftJoin('events es', 'es.id=rs.event_id')
			->leftJoin('events ea', 'ea.id=rs.event_id')
			->where('es.`rank`<900 and ea.`rank`<900')
			->group('rs.person_id')
			->having('singles + averages = ' . $sum);
		ActiveRecord::applyRegionCondition($cmd, $statistic['region'] ?? 'China', 'p.country_id');
		$persons = $cmd->queryAll();
		$cmd = $db->createCommand()
			->from('results rs')
			->leftJoin('competitions c', 'rs.competition_id=c.id')
			->where('rs.person_id=:person_id');
		$cmd1 = (clone $cmd)->select([
				'rs.event_id',
				'MIN(UNIX_TIMESTAMP(CONCAT(c.year, "-", c.end_month, "-", c.end_day))) AS time',
			])->group('rs.event_id');
		foreach ($persons as $key=>$person) {
			$params = [':person_id'=>$person['person_id']];
			$startDate = (clone $cmd)->select('MIN(UNIX_TIMESTAMP(CONCAT(c.year, "-", c.month, "-", c.day))) AS time')->queryScalar($params);
			$singleDates = (clone $cmd1)->andWhere('rs.best>0')->queryAll(true, $params);
			$averageDates = (clone $cmd1)->andWhere('rs.average>0')->queryAll(true, $params);
			$finishDate = max(
				max(CHtml::listData($singleDates, 'event_id', 'time')),
				max(CHtml::listData($averageDates, 'event_id', 'time'))
			);
			$competitions = (clone $cmd)->select('COUNT(DISTINCT(competition_id)) as competitions')->andWhere(
				'c.year<:year OR (c.year=:year AND c.end_month<:month) OR (c.year=:year AND c.end_month=:month AND c.end_day<=:day)',
				[
					':year'=>date('Y', $finishDate),
					':month'=>date('n', $finishDate),
					':day'=>date('j', $finishDate),
				]
			)->queryScalar($params);
			$person['startDate'] = intval($startDate);
			$person['finishDate'] = intval($finishDate);
			$person['competitions'] = intval($competitions);
			$person['days'] = ($finishDate - $startDate) / 86400 + 1;
			$persons[$key] = $person;
		}
		usort($persons, function($personA, $personB) {
			return $personA['days'] - $personB['days'];
		});
		$statistic['count'] = count($persons);
		$statistic['rankKey'] = 'days';
		$statistic['rank'] = 0;
		$persons = array_slice($persons, 0, self::$limit);
		$columns = [
			[
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
				'type'=>'raw',
			],
			[
				'header'=>'Yii::t("statistics", "Days")',
				'value'=>'$data["days"]',
				'type'=>'raw',
			],
			[
				'header'=>'Yii::t("statistics", "Finish Date")',
				'value'=>'date("Y-m-d", $data["finishDate"])',
				'type'=>'raw',
			]
		];
		if (self::$limit > 10) {
			$columns[] = [
				'header'=>'Yii::t("statistics", "competitions")',
				'value'=>'$data["competitions"]',
				'type'=>'raw',
			];
		}
		if (isset($statistic['region'])) {
			$columns[] = [
				'header'=>'Yii::t("common", "Region")',
				'value'=>'Region::getIconName($data["country_id"], $data["iso2"])',
				'type'=>'raw',
			];
		}
		return self::makeStatisticsData($statistic, $columns, $persons);
	}
}
