<?php

class Top100 extends Statistics {

	public static $top100s = array();

	public static function build($statistic) {
		$db = Yii::app()->wcaDb;
		$command = $db->createCommand()
		->from('Results rs')
		->leftJoin('Countries country', 'rs.personCountryId=country.Id')
		->leftJoin('Competitions c', 'rs.competitionId=c.id')
		->leftJoin('Persons p', 'rs.personId=p.id AND p.subid=1')
		->where('eventId=:eventId', array(
			':eventId'=>$statistic['event'],
		))
		->order('value ASC')
		->limit(200);
		ActiveRecord::applyRegionCondition($command, $statistic['region']);
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
		switch ($statistic['type']) {
			case 'single':
				$temp = array();
				for ($i = 1; $i <= 5; $i++) {
					$cmd = clone $command;
					$temp[] = $cmd->select(array(
						"value{$i} AS value",
						'personId',
						'personName',
						'personCountryId',
						'country.name AS countryName',
						'iso2',
						'competitionId',
						'cellName',
						'cityName',
						'eventId',
					))
					->andWhere("value{$i}>0")
					->queryAll();
				}
				$top200 = call_user_func_array('array_merge', $temp);
				usort($top200, function($resultA, $resultB) {
					$temp = $resultA['value'] - $resultB['value'];
					if ($temp == 0) {
						$temp = strcmp($resultA['personName'], $resultB['personName']);
					}
					return $temp;
				});
				break;
			case 'average':
				$top200 = $command->select(array(
					'personId',
					'personName',
					'personCountryId',
					'country.name AS countryName',
					'iso2',
					'competitionId',
					'cellName',
					'cityName',
					'eventId',
					'average AS value',
					'value1',
					'value2',
					'value3',
					'value4',
					'value5',
				))
				->andWhere('average>0')
				->queryAll();
				break;
		}
		$top100 = array();
		$lastValue = 0;
		$number = $pos = 0;
		foreach ($top200 as $result) {
			$number++;
			if ($lastValue != $result['value']) {
				$lastValue = $result['value'];
				$pos = $number;
			}
			if ($pos > 100) {
				break;
			}
			if (isset($statistic['count'])) {
				if (!isset($top100[$result['personId']])) {
					$top100[$result['personId']] = $result;
					$top100[$result['personId']]['count'] = 0;
				}
				$top100[$result['personId']]['count']++;
			} else {
				$top100[] = $result;
			}
		}
		if (isset($statistic['count'])) {
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
			self::$top100s[$statistic['type']][$statistic['event']] = self::makeStatisticsData($statistic, $columns, array_slice($top100, 0, self::$limit));
			$events = Events::getNormalEvents();
			$eventIds = array_keys($events);
			foreach ($eventIds as $eventId) {
				if (isset(self::$top100s[$statistic['type']][$eventId])) {
					continue;
				}
				$temp = $statistic;
				$temp['event'] = $eventId;
				self::build($temp);
			}
			if ($statistic['type'] === 'average') {
				unset($events['444bf'], $events['555bf'], $events['333mbf']);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>self::$top100s[$statistic['type']],
				'select'=>$events,
				'selectHandler'=>'Yii::t("event", "$name")',
				'selectKey'=>'event',
			));
		} else {
			$top100 = array_map(function($row) {
				return self::getCompetition($row);
			}, $top100);
			$columns = array(
				array(
					'header'=>'Yii::t("statistics", "Person")',
					'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
					'type'=>'raw',
				),
				array(
					'header'=>'Yii::t("common", "Result")',
					'value'=>'Results::formatTime($data["value"], $data["eventId"])',
					'type'=>'raw',
				),
				// array(
				// 	'header'=>'Yii::t("common", "Region")',
				// 	'value'=>'Region::getIconName($data["countryName"], $data["iso2"])',
				// 	'type'=>'raw',
				// 	'htmlOptions'=>array('class'=>'region'),
				// ),
				 array(
					'header'=>'Yii::t("common", "Competition")',
					'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
					'type'=>'raw',
				),
			);
			if ($statistic['type'] === 'average') {
				$columns[] = array(
					'header'=>"Yii::t('common', 'Detail')",
					'value'=>'Results::getDisplayDetail($data)',
					'type'=>'raw',
				);
			}
			$statistic['count'] = count($top100);
			$statistic['rankKey'] = 'value';
			return self::makeStatisticsData($statistic, $columns, $top100);
		}
	}

}
