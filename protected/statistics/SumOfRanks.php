<?php

class SumOfRanks extends Statistics {

	private static $_ranks = array();

	public static function build($statistic, $page = 1) {
		$gender = isset($statistic['gender']) ? $statistic['gender'] : 'all';
		$ranks = self::getRanks($statistic['type'], $statistic['region']);
		$eventIds = !empty($statistic['eventIds']) ? $statistic['eventIds'] : array_keys(Events::getNormalEvents());
		$eventIds = array_unique($eventIds);
		$columns = array(
			array(
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
				'type'=>'raw',
			),
		);
		if (Region::isContinent($statistic['region']) || $statistic['region'] === 'World') {
			$columns[] = array(
				'header'=>'Yii::t("common", "Region")',
				'value'=>'Region::getIconName($data["countryName"], $data["iso2"])',
				'type'=>'raw',
				'htmlOptions'=>array('class'=>'region'),
			);
		}
		$columns[] = array(
			'header'=>'Yii::t("statistics", "Sum")',
			'value'=>'CHtml::tag("b", array(), $data["sum"])',
			'type'=>'raw',
		);
		//计算未参赛的项目应该排第几
		$penalty = Yii::app()->cache->getData('RanksPenalty::getPenlties', array($statistic['type'], $statistic['region']));
		foreach ($eventIds as $key=>$event_id) {
			if (!isset($ranks[$event_id])) {
				unset($eventIds[$key]);
				continue;
			}
		}
		$penalty = array_intersect_key($penalty, array_flip($eventIds));
		$allPenalties = array_sum($penalty);
		//计算每个人的排名
		$rankSum = array();
		foreach ($eventIds as $event_id) {
			foreach ($ranks[$event_id] as $person_id=>$row) {
				if(!isset($rankSum[$person_id])) {
					$rankSum[$person_id] = $row;
					$rankSum[$person_id]['sum'] = $allPenalties;
				}
				$rankSum[$person_id]['sum'] += $row['rank'] - $penalty[$event_id];
			}
			$columns[] = array(
				'header'=>"Events::getEventIcon('$event_id')",
				'name'=>$event_id,
				'type'=>'raw',
			);
		}
		uasort($rankSum, function($rankA, $rankB) {
			return $rankA['sum'] - $rankB['sum'];
		});
		switch ($gender) {
			case 'female':
				$rankSum = array_filter($rankSum, function($row) {
					return $row['gender'] == 'f';
				});
				break;
			case 'male':
				$rankSum = array_filter($rankSum, function($row) {
					return $row['gender'] == 'm';
				});
				break;
		}
		$count = count($rankSum);
		if ($page > ceil($count / self::$limit)) {
			$page = ceil($count / self::$limit);
		}
		$rows = array();
		foreach (array_slice($rankSum, ($page - 1) * self::$limit, self::$limit) as $person_id=>$row) {
			foreach ($eventIds as $event_id) {
				$row[$event_id] = isset($ranks[$event_id][$person_id])
								 ? $ranks[$event_id][$person_id]['rank']
								 : $penalty[$event_id];
				if (isset($ranks[$event_id][$person_id]) && $ranks[$event_id][$person_id]['rank'] <= 10) {
					$row[$event_id] = CHtml::tag('span', array('class'=>'top10'), $row[$event_id]);
				} elseif (!isset($ranks[$event_id][$person_id])) {
					$row[$event_id] = CHtml::tag('span', array('class'=>'penalty'), $row[$event_id]);
				}
			}
			$rows[] = $row;
		}
		$statistic['count'] = $count;
		$statistic['rank'] = isset($rows[0]) ? count(array_filter($rankSum, function($row) use ($rows) {
			return $row['sum'] < $rows[0]['sum'];
		})) : 0;
		$statistic['rankKey'] = 'sum';
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

	public static function getRanks($type, $region = 'China') {
		if (isset(self::$_ranks[$type][$region])) {
			return self::$_ranks[$type][$region];
		}
		$select = array(
			'event_id',
			'person_id',
			'p.gender',
			'p.name AS person_name',
			'p.sub_id',
			'country.name AS country_name',
			'country.iso2',
		);
		switch ($region) {
			case 'World':
				$field = 'world_rank';
				break;
			case 'Africa':
			case 'Asia':
			case 'Oceania':
			case 'Europe':
			case 'North America':
			case 'South America':
				$field = 'continent_rank';
				break;
			default:
				$field = 'country_rank';
				break;
		}
		$select[] = $field . ' AS rank';
		$command = Yii::app()->wcaDb->createCommand()
		->select($select)
		->from(sprintf('ranks_%s r', $type))
		->leftJoin('persons p', 'r.person_id=p.wca_id AND p.sub_id=1')
		->leftJoin('countries country', 'country.id=p.country_id');
		if ($field !== 'world_rank') {
			$command->where($field . '>0');
		}
		ActiveRecord::applyRegionCondition($command, $region, 'p.country_id');
		$ranks = array();
		foreach ($command->queryAll() as $row) {
			$ranks[$row['event_id']][$row['person_id']] = $row;
		}
		return self::$_ranks[$type][$region] = $ranks;
	}
}
