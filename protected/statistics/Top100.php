<?php

class Top100 extends Statistics {

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
		switch ($statistic['region']) {
			case 'World':
				break;
			case 'Africa':
			case 'Asia':
			case 'Oceania':
			case 'Europe':
			case 'North America':
			case 'South America':
				$command->andWhere('country.continentId=:region', array(
					':region'=>'_' . $statistic['region'],
				));
				break;
			default:
				$command->andWhere('personCountryId=:region', array(
					':region'=>$statistic['region'],
				));
				break;
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
						'eventId',
					))
					->andWhere("value{$i}>0")
					->queryAll();
				}
				$top200 = call_user_func_array('array_merge', $temp);
				usort($top200, function($resultA, $resultB) {
					return $resultA['value'] - $resultB['value'];
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
			return self::makeStatisticsData($statistic, $columns, array_slice($top100, 0, self::$limit));
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
