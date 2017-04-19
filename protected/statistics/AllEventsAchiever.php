<?php

class AllEventsAchiever extends Statistics {
	public static function build($statistic) {
		$db = Yii::app()->wcaDb;
		$cmd = $db->createCommand()
			->select([
				'rs.personId',
				'p.name AS personName',
				'p.countryId',
				'country.iso2',
				'COUNT(DISTINCT rs.eventId) AS singles',
				'COUNT(DISTINCT ra.eventId) AS averages',
			])
			->from('RanksSingle rs')
			->leftJoin('RanksAverage ra', 'rs.personId=ra.personId AND rs.eventId=ra.eventId')
			->leftJoin('Persons p', 'rs.personId=p.id AND p.subid=1')
			->leftJoin('Countries country', 'p.countryId=country.id')
			->group('rs.personId')
			->having('singles + averages = 33');
		ActiveRecord::applyRegionCondition($cmd, $statistic['region'] ?? 'China', 'p.countryId');
		$persons = $cmd->queryAll();
		$cmd = $db->createCommand()
			->from('Results rs')
			->leftJoin('Competitions c', 'rs.competitionId=c.id')
			->where('rs.personId=:personId');
		$cmd1 = (clone $cmd)->select([
				'rs.eventId',
				'MIN(UNIX_TIMESTAMP(CONCAT(c.year, "-", c.endMonth, "-", c.endDay))) AS time',
			])->group('rs.eventId');
		foreach ($persons as $key=>$person) {
			$params = [':personId'=>$person['personId']];
			$startDate = (clone $cmd)->select('MIN(UNIX_TIMESTAMP(CONCAT(c.year, "-", c.month, "-", c.day))) AS time')->queryScalar($params);
			$singleDates = (clone $cmd1)->andWhere('rs.best>0')->queryAll(true, $params);
			$averageDates = (clone $cmd1)->andWhere('rs.average>0')->queryAll(true, $params);
			$finishDate = max(
				max(CHtml::listData($singleDates, 'eventId', 'time')),
				max(CHtml::listData($averageDates, 'eventId', 'time'))
			);
			$person['startDate'] = intval($startDate);
			$person['finishDate'] = intval($finishDate);
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
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
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
		if (isset($statistic['region'])) {
			$columns[] = [
				'header'=>'Yii::t("common", "Region")',
				'value'=>'Region::getIconName($data["countryId"], $data["iso2"])',
				'type'=>'raw',
			];
		}
		return self::makeStatisticsData($statistic, $columns, $persons);
	}
}
