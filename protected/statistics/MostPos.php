<?php

class MostPos extends Statistics {
	public static $positions = [2=>2, 4=>4];

	public static function build($statistic, $page = 1, $recursive = true) {
		if ($statistic['type'] === 'each') {
			$statistics = [];
			$events = Events::getNormalEvents();
			$temp = $statistic;
			$temp['type'] = 'all';
			foreach ($events as $eventId=>$name) {
				$temp['eventIds'] = [$eventId];
				$statistics[$eventId] = self::build($temp);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>$statistics,
				'select'=>$events,
				'selectHandler'=>'Yii::t("event", "$name")',
				'selectKey'=>'event',
			));
		}
		$db = Yii::app()->wcaDb;
		$cmd1 = $db->createCommand()
			->select([
				'count(pos) AS count',
				'personId',
				'personCountryId',
				'iso2',
				'personName',
			])
			->from('Results rs')
			->leftJoin('Countries country', 'rs.personCountryId=country.id')
			->leftJoin('Persons p', 'rs.personId=p.id AND p.subid=1')
			->where('pos = :pos', [':pos'=>$statistic['pos']]);
		if (!isset($statistic['includeDNF']) || $statistic['includeDNF'] == 0) {
			$cmd1->andWhere('best > 0');
		}
		if (!empty($statistic['eventIds'])) {
			$cmd1->andWhere(['in', 'eventId', $statistic['eventIds']]);
		}
		if (isset($statistic['gender'])) {
			switch ($statistic['gender']) {
				case 'female':
					$cmd1->andWhere('p.gender="f"');
					break;
				case 'male':
					$cmd1->andWhere('p.gender="m"');
					break;
			}
		}
		ActiveRecord::applyRegionCondition($cmd1, $statistic['region'] ?? 'China');
		$cmd2 = clone $cmd1;
		$cmd1->group('rs.personId')
			->order('count DESC, personName ASC')
			->limit(self::$limit)
			->offset(($page - 1) * self::$limit);
		$rows = $cmd1->queryAll();
		$statistic['count'] = $cmd2->select('count(DISTINCT rs.personId) AS count')->queryScalar();
		$statistic['rank'] = ($page - 1) * self::$limit;
		$statistic['rankKey'] = 'count';
		if ($page > 1 && $rows !== array() && $recursive) {
			$stat = self::build($statistic, $page - 1, false);
			foreach (array_reverse($stat['rows']) as $row) {
				if ($row['count'] === $rows[0]['count']) {
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
				'header'=>'Yii::t("statistics", "Count")',
				'value'=>'$data["count"]',
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
