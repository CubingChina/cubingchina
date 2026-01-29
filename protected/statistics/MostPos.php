<?php

class MostPos extends Statistics {
	public static $positions = [2=>2, 4=>4];

	public static function build($statistic, $page = 1, $recursive = true) {
		if ($statistic['type'] === 'each') {
			$statistics = [];
			$events = Events::getNormalEvents();
			$temp = $statistic;
			$temp['type'] = 'all';
			foreach ($events as $event_id=>$name) {
				$temp['eventIds'] = ["$event_id"];
				$statistics[$event_id] = self::build($temp);
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
				'person_id',
				'person_country_id',
				'iso2',
				'person_name',
			])
			->from('results rs')
			->leftJoin('countries country', 'rs.person_country_id=country.id')
			->leftJoin('persons p', 'rs.person_id=p.wca_id AND p.sub_id=1')
			->where('pos = :pos', [':pos'=>$statistic['pos']]);
		if (!isset($statistic['includeDNF']) || $statistic['includeDNF'] == 0) {
			$cmd1->andWhere('best > 0');
		}
		if (!empty($statistic['eventIds'])) {
			$cmd1->andWhere(['in', 'event_id', $statistic['eventIds']]);
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
		$cmd1->group('rs.person_id')
			->order('count DESC, person_name ASC')
			->limit(self::$limit)
			->offset(($page - 1) * self::$limit);
		$rows = $cmd1->queryAll();
		$statistic['count'] = $cmd2->select('count(DISTINCT rs.person_id) AS count')->queryScalar();
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
				'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
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
				'value'=>'Region::getIconName($data["person_country_id"], $data["iso2"])',
				'type'=>'raw',
			];
		}
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

	private static function getDefaultRankType($event_id) {
		if (in_array("$event_id", ['333bf', '444bf', '555bf', '333mbf'])) {
			return 'single';
		}
		return 'average';
	}
}
