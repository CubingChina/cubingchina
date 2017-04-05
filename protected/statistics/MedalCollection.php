<?php

class MedalCollection extends Statistics {

	public static function build($statistic, $page = 1, $recursive = true) {
		$command = Yii::app()->wcaDb->createCommand();
		$command->select(array(
			'personId', 'personName',
			'sum(CASE WHEN pos=1 THEN 1 ELSE 0 END) AS gold',
			'sum(CASE WHEN pos=2 THEN 1 ELSE 0 END) AS silver',
			'sum(CASE WHEN pos=3 THEN 1 ELSE 0 END) AS bronze',
		))
		->from('Results')
		->where('personCountryId="China" AND roundTypeId IN ("c", "f") AND best>0');
		if (!empty($statistic['eventIds'])) {
			$command->andWhere(array('in', 'eventId', $statistic['eventIds']));
		}
		$cmd = clone $command;
		$command->group('personId')
		->order('gold DESC, silver DESC, bronze DESC, personName ASC')
		->having('gold + silver + bronze > 0')
		->limit(self::$limit)
		->offset(($page - 1) * self::$limit);
		$columns = array(
			array(
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
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
			$statistic['count'] = $cmd->select('count(DISTINCT personId) AS count')
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
			foreach ($eventIds as $eventId) {
				$cmd = clone $command;
				$rows = $cmd->andWhere("eventId='{$eventId}'")->queryAll();
				$medals[$eventId] = self::makeStatisticsData($statistic, $columns, $rows);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>$medals,
				'select'=>Events::getNormalEvents(),
				'selectHandler'=>'Yii::t("event", "$name")',
			));
		}
	}

}
