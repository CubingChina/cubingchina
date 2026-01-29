<?php

class BestMisser extends Statistics {
	public static function build($statistic, $page = 1, $recursive = true) {
		if ($statistic['type'] === 'all') {
			$bestMissedPodiums = [];
			$events = Events::getNormalEvents();
			$temp = $statistic;
			$temp['type'] = 'single';
			foreach ($events as $event_id=>$name) {
				$temp['event_id'] = $event_id;
				$bestMissedPodiums[$event_id] = self::build($temp);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>$bestMissedPodiums,
				'select'=>$events,
				'selectHandler'=>'Yii::t("event", "$name")',
				'selectKey'=>'event',
			));
		}
		$event_id = $statistic['event_id'];
		$rankType = $statistic['rankType'] ?? self::getDefaultRankType($event_id);
		$db = Yii::app()->wcaDb;
		$cmd1 = $db->createCommand();
		$cmd1->from('results rs')
			->where('rs.event_id=:event_id', array(
				':event_id'=>$event_id,
			));
		ActiveRecord::applyRegionCondition($cmd1, $statistic['region'] ?? 'China');
		$cmd2 = clone $cmd1;
		switch ($statistic['exclude']) {
			case 'pos':
				$cmd1->andWhere('rs.round_type_id IN ("c", "f")')->andWhere('rs.pos IN (' . implode(',', $statistic['pos']) . ')')->andWhere("rs.best > 0");
				break;
			case 'record':
				$cmd1->andWhere("regional_{$rankType}_record IS NOT NULL");
				break;
		}
		if ($rankType == 'single') {
			$rankType = 'best';
		}
		$select1Sql = $cmd1->select('1')
		->from('results rs2')
		->andWhere('rs2.person_id = tmp.person_id')
		->getText();
		$select1Sql = str_replace('`1`', '1', $select1Sql);
		$select1Sql = str_replace('rs.', 'rs2.', $select1Sql);
		$select1Sql = str_replace('tmp.', 'rs.', $select1Sql);
		$cmd2->select([
				'event_id',
				'person_id',
				'person_country_id',
				'iso2',
				'person_name',
				"min(rs.{$rankType}) AS best"
			])
			->join('countries country', 'rs.person_country_id=country.id')
			->andWhere("rs.{$rankType} > 0")
			->andWhere("NOT EXISTS ({$select1Sql})");
		$cmd3 = clone $cmd2;
		$cmd2->group('rs.person_id')
			->order('best ASC')
			->limit(self::$limit)
			->offset(($page - 1) * self::$limit);
		$rows = $cmd2->queryAll();
		if (self::$limit > 10) {
			$statistic['count'] = $cmd3->select('count(DISTINCT rs.person_id) AS count')->queryScalar();
		}
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
				'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
				'type'=>'raw',
			],
			[
				'header'=>'Yii::t("results", "Personal Record")',
				'value'=>'Results::formatTime($data["best"], $data["event_id"])',
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
