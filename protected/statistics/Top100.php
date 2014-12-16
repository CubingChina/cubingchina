<?php

class Top100 extends Statistics {

	public static function build($statistic) {
		$db = Yii::app()->wcaDb;
		$command = $db->createCommand()
		->from('Results')
		->where('personCountryId="China" AND eventId="333"')
		->order('value ASC')
		->limit(500);
		switch ($statistic['type']) {
			case 'single':
				$sqls = array();
				for ($i = 1; $i <= 5; $i++) {
					$temp = clone $command;
					$sqls[] = $temp->select(array(
						"value{$i} AS value",
						'personId',
						'personName',
					))
					->andWhere("value{$i}>0")
					->getText();
				}
				$sql = '(' . implode(') UNION ALL (', $sqls) . ')';
				$top500 = $command->from("($sql) AS Results")
				->where('1')
				->queryAll();
				break;
			case 'average':
				$top500 = $command->select(array(
					'personId',
					'personName',
					'average AS value',
				))
				->andWhere('average>0')
				->queryAll();
				break;
		}
		$top100 = array();
		$lastValue = 0;
		$number = $pos = 0;
		foreach ($top500 as $result) {
			$number++;
			if ($lastValue != $result['value']) {
				$lastValue = $result['value'];
				$pos = $number;
			}
			if ($pos > 100) {
				break;
			}
			if (!isset($top100[$result['personId']])) {
				$top100[$result['personId']] = $result;
				$top100[$result['personId']]['count'] = 0;
			}
			$top100[$result['personId']]['count']++;
		}
		usort($top100, function($rowA, $rowB) {
			return $rowB['count'] - $rowA['count'];
		});
		$columns = array(
			array(
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "Appearances")',
				'value'=>'CHtml::tag("b", array(), $data["count"])',
				'type'=>'raw',
			),
		);
		return self::makeStatisticsData($statistic, $columns, array_slice($top100, 0, self::$limit));
	}

}
