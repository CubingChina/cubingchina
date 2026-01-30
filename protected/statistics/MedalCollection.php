<?php

class MedalCollection extends Statistics {

	public static function build($statistic, $page = 1, $recursive = true) {
		$command = Yii::app()->wcaDb->createCommand();
		$command->select(array(
			'person_id', 'person_name',
			'sum(CASE WHEN pos=1 THEN 1 ELSE 0 END) AS gold',
			'sum(CASE WHEN pos=2 THEN 1 ELSE 0 END) AS silver',
			'sum(CASE WHEN pos=3 THEN 1 ELSE 0 END) AS bronze',
		))
		->from('results rs')
		->leftJoin('persons p', 'rs.person_id=p.wca_id AND p.sub_id=1')
		->leftJoin('countries country', 'p.country_id=country.id')
		->where('round_type_id IN ("c", "f") AND best>0');
		ActiveRecord::applyRegionCondition($command, $statistic['region'] ?? 'China', 'p.country_id');
		if (!empty($statistic['eventIds'])) {
			$command->andWhere(array('in', 'event_id', $statistic['eventIds']));
		}
		if (isset($statistic['gender'])) {
			switch ($statistic['gender']) {
				case 'female':
					$command->andWhere('p.gender="f"');
					break;
				case 'male':
					$command->andWhere('p.gender="m"');
					break;
			}
		}
		if (isset($statistic['year'])) {
			$command->andWhere('competition_id LIKE :year', [
				':year'=>'%' . $statistic['year'],
			]);
		}
		$cmd = clone $command;
		$command->group('person_id')
		->order('gold DESC, silver DESC, bronze DESC, person_name ASC')
		->having('gold + silver + bronze > 0')
		->limit(self::$limit)
		->offset(($page - 1) * self::$limit);
		$columns = array(
			array(
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "Gold")',
				'name'=>'gold',
			),
			array(
				'header'=>'Yii::t("statistics", "Silver")',
				'name'=>'silver',
			),
			array(
				'header'=>'Yii::t("statistics", "Bronze")',
				'name'=>'bronze',
			),
			array(
				'header'=>'Yii::t("statistics", "Sum")',
				'value'=>'CHtml::tag("b", array(), $data["gold"] + $data["silver"] + $data["bronze"])',
				'type'=>'raw',
			),
		);
		if ($statistic['type'] === 'all') {
			$rows = array();
			foreach ($command->queryAll() as $row) {
				$row['rank'] = sprintf('%d_%d_%d', $row['gold'], $row['silver'], $row['bronze']);
				$rows[] = $row;
			}
			$statistic['count'] = $cmd->select('count(DISTINCT person_id) AS count')
			->andWhere('pos IN (1,2,3)')
			->queryScalar();
			$statistic['rank'] = ($page - 1) * self::$limit;
			$statistic['rankKey'] = 'rank';
			if ($page > 1 && $rows !== array() && $recursive) {
				$stat = self::build($statistic, $page - 1, false);
				foreach (array_reverse($stat['rows']) as $row) {
					if ($row['rank'] === $rows[0]['rank']) {
						$statistic['rank']--;
					} else {
						break;
					}
				}
			}
			return self::makeStatisticsData($statistic, $columns, $rows);
		} else {
			$medals = array();
			$eventIds = array_keys(Events::getNormalEvents());
			foreach ($eventIds as $event_id) {
				$cmd = clone $command;
				$rows = $cmd->andWhere("event_id='{$event_id}'")->queryAll();
				$medals[$event_id] = self::makeStatisticsData($statistic, $columns, $rows);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>$medals,
				'select'=>Events::getNormalEvents(),
				'selectHandler'=>'Yii::t("event", "$name")',
			));
		}
	}

}
