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
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
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
		foreach ($eventIds as $key=>$eventId) {
			if (!isset($ranks[$eventId])) {
				unset($eventIds[$key]);
				continue;
			}
		}
		$penalty = array_intersect_key($penalty, array_flip($eventIds));
		$allPenalties = array_sum($penalty);
		//计算每个人的排名
		$rankSum = array();
		foreach ($eventIds as $eventId) {
			foreach ($ranks[$eventId] as $personId=>$row) {
				if(!isset($rankSum[$personId])) {
					$rankSum[$personId] = $row;
					$rankSum[$personId]['sum'] = $allPenalties;
				}
				$rankSum[$personId]['sum'] += $row['rank'] - $penalty[$eventId];
			}
			$columns[] = array(
				'header'=>"Events::getEventIcon('$eventId')",
				'name'=>$eventId,
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
		foreach (array_slice($rankSum, ($page - 1) * self::$limit, self::$limit) as $personId=>$row) {
			foreach ($eventIds as $eventId) {
				$row[$eventId] = isset($ranks[$eventId][$personId])
								 ? $ranks[$eventId][$personId]['rank']
								 : $penalty[$eventId];
				if (isset($ranks[$eventId][$personId]) && $ranks[$eventId][$personId]['rank'] <= 10) {
					$row[$eventId] = CHtml::tag('span', array('class'=>'top10'), $row[$eventId]);
				} elseif (!isset($ranks[$eventId][$personId])) {
					$row[$eventId] = CHtml::tag('span', array('class'=>'penalty'), $row[$eventId]);
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
			'eventId',
			'personId',
			'p.gender',
			'p.name AS personName',
			'p.subid',
			'country.name AS countryName',
			'country.iso2',
		);
		switch ($region) {
			case 'World':
				$field = 'worldRank';
				break;
			case 'Africa':
			case 'Asia':
			case 'Oceania':
			case 'Europe':
			case 'North America':
			case 'South America':
				$field = 'continentRank';
				break;
			default:
				$field = 'countryRank';
				break;
		}
		$select[] = $field . ' AS rank';
		$command = Yii::app()->wcaDb->createCommand()
		->select($select)
		->from('Ranks' . ucfirst($type) . ' r')
		->leftJoin('Persons p', 'r.personId=p.id AND p.subid=1')
		->leftJoin('Countries country', 'country.id=p.countryId');
		if ($field !== 'worldRank') {
			$command->where($field . '>0');
		}
		ActiveRecord::applyRegionCondition($command, $region, 'p.countryId');
		$ranks = array();
		foreach ($command->queryAll() as $row) {
			$ranks[$row['eventId']][$row['personId']] = $row;
		}
		return self::$_ranks[$type][$region] = $ranks;
	}
}