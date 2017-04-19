<?php

class BestMisser extends Statistics {
	public static function build($statistic, $page = 1, $recursive = true) {
		if ($statistic['type'] === 'all') {
			$bestMissedPodiums = [];
			$events = Events::getNormalEvents();
			$temp = $statistic;
			$temp['type'] = 'single';
			foreach ($events as $eventId=>$name) {
				$temp['eventId'] = $eventId;
				$bestMissedPodiums[$eventId] = self::build($temp);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>$bestMissedPodiums,
				'select'=>$events,
				'selectHandler'=>'Yii::t("event", "$name")',
				'selectKey'=>'event',
			));
		}
		$eventId = $statistic['eventId'];
		$rankType = $statistic['rankType'] ?? self::getDefaultRankType($eventId);
		$db = Yii::app()->wcaDb;
		$cmd1 = $db->createCommand();
		$cmd1->from('Results rs')
			->leftJoin('Countries country', 'rs.personCountryId=country.id')
			->where('rs.eventId=:eventId', array(
				':eventId'=>$eventId,
			));
		ActiveRecord::applyRegionCondition($cmd1, $statistic['region'] ?? 'China');
		$cmd2 = clone $cmd1;
		switch ($statistic['exclude']) {
			case 'pos':
				$cmd1->andWhere('rs.roundTypeId IN ("c", "f")')->andWhere('rs.pos IN (' . implode(',', $statistic['pos']) . ')')->andWhere("rs.best > 0");
				break;
			case 'record':
				$recordType = ucfirst($rankType);
				$cmd1->andWhere("regional{$recordType}Record != ''");
				break;
		}
		if ($rankType == 'single') {
			$rankType = 'best';
		}
		$podiumPersonIdSql = $cmd1->selectDistinct('rs.personId')->getText();
		$cmd2->select([
				'eventId',
				'personId',
				'personCountryId',
				'iso2',
				'personName',
				"min(rs.{$rankType}) AS best"
			])
			->andWhere("rs.{$rankType} > 0")
			->andWhere("rs.personId NOT IN ({$podiumPersonIdSql})");
		$cmd3 = clone $cmd2;
		$cmd2->group('rs.personId')
			->order('best ASC')
			->limit(self::$limit)
			->offset(($page - 1) * self::$limit);
		$rows = $cmd2->queryAll();
		$statistic['count'] = $cmd3->select('count(DISTINCT rs.personId) AS count')->queryScalar();
		$statistic['rank'] = ($page - 1) * self::$limit;
		$statistic['rankKey'] = 'best';
		if ($page > 1 && $rows !== array() && $recursive) {
			$stat = self::build($statistic, $page - 1, false);
			foreach (array_reverse($stat['rows']) as $row) {
				if ($row['best'] === $rows[0]['best']) {
					$statistic['rank']--;
				} else {
					break;
				}
			}
		}
		$columns = [
			[
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
				'type'=>'raw',
			],
			[
				'header'=>'Yii::t("Results", "Personal Record")',
				'value'=>'Results::formatTime($data["best"], $data["eventId"])',
				'type'=>'raw',
			],
		];
		if (isset($statistic['region'])) {
			$columns[] = [
				'header'=>'Yii::t("common", "Region")',
				'value'=>'Region::getIconName($data["personCountryId"], $data["iso2"])',
				'type'=>'raw',
			];
		}
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

	private static function getDefaultRankType($eventId) {
		if (in_array("$eventId", ['333bf', '444bf', '555bf', '333mbf'])) {
			return 'single';
		}
		return 'average';
	}
}
