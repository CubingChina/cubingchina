<?php

class Top100 extends Statistics {

	public static $top100s = array();

	public static function build($statistic) {
		$db = Yii::app()->wcaDb;
		$command = $db->createCommand()
		->from('results rs')
		->leftJoin('countries country', 'rs.person_country_id=country.Id')
		->leftJoin('competitions c', 'rs.competition_id=c.id')
		->leftJoin('persons p', 'rs.person_id=p.wca_id AND p.sub_id=1')
		->where('event_id=:event_id', array(
			':event_id'=>$statistic['event'],
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
				// join result_attempts
				$top200 = $command->leftJoin('result_attempts ra', 'rs.id=ra.result_id')
				->select([
					'person_id',
					'person_name',
					'person_country_id',
					'country.name AS country_name',
					'iso2',
					'competition_id',
					'cell_name',
					'city_name',
					'event_id',
					'value'
				])
				->andWhere('value>0')
				->queryAll();
				break;
			case 'average':
				$top200 = $command->select(array(
					'person_id',
					'person_name',
					'person_country_id',
					'country.name AS country_name',
					'iso2',
					'competition_id',
					'cell_name',
					'city_name',
					'event_id',
					'average AS value',
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
				if (!isset($top100[$result['person_id']])) {
					$top100[$result['person_id']] = $result;
					$top100[$result['person_id']]['count'] = 0;
				}
				$top100[$result['person_id']]['count']++;
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
					'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
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
			foreach ($eventIds as $event_id) {
				if (isset(self::$top100s[$statistic['type']][$event_id])) {
					continue;
				}
				$temp = $statistic;
				$temp['event'] = $event_id;
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
					'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
					'type'=>'raw',
				),
				array(
					'header'=>'Yii::t("common", "Result")',
					'value'=>'Results::formatTime($data["value"], $data["event_id"])',
					'type'=>'raw',
				),
				// array(
				// 	'header'=>'Yii::t("common", "Region")',
				// 	'value'=>'Region::getIconName($data["country_name"], $data["iso2"])',
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
