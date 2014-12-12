<?php

class MedalCollection extends Statistics {

	public static function build($statistic) {
		$command = Yii::app()->wcaDb->createCommand();
		$command->select(array(
			'personId', 'personName',
			'sum(CASE WHEN pos=1 THEN 1 ELSE 0 END) AS gold',
			'sum(CASE WHEN pos=2 THEN 1 ELSE 0 END) AS silver',
			'sum(CASE WHEN pos=3 THEN 1 ELSE 0 END) AS bronze',
		))
		->from('Results')
		->where('personCountryId="China" AND roundId IN ("c", "f") AND best>0')
		->group('personId')
		->order('gold DESC, silver DESC, bronze DESC, personName ASC')
		->limit(10);
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
			$rows = $command->queryAll();
			return self::makeStatisticsData($statistic, $columns, $rows);
		} else {
			$medals = array();
			$eventIds =array_keys(Events::getNormalEvents());
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
