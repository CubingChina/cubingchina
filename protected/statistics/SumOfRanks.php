<?php

class SumOfRanks extends Statistics {

	private static $_ranks = array();

	public static function build($statistic, $page = 1) {
		$gender = isset($statistic['gender']) ? $statistic['gender'] : 'all';
		$ranks = self::getRanks($statistic['type']);
		$eventIds = !empty($statistic['eventIds']) ? $statistic['eventIds'] : array_keys(Events::getNormalEvents());
		$columns = array(
			array(
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "Sum")',
				'value'=>'CHtml::tag("b", array(), $data["sum"])',
				'type'=>'raw',
			),
		);
		//计算未参赛的项目应该排第几
		$allPenalties = 0;
		foreach ($eventIds as $key=>$eventId) {
			if (!isset($ranks[$eventId])) {
				unset($eventIds[$key]);
				continue;
			}
			$allPenalties += $penalty[$eventId] = count($ranks[$eventId]) + 1;
		}
		//计算每个人的排名
		$rankSum = array();
		foreach ($eventIds as $eventId) {
			foreach ($ranks[$eventId] as $personId=>$row) {
				if(!isset($rankSum[$personId])) {
					$rankSum[$personId] = $row;
					$rankSum[$personId]['sum'] = $allPenalties;
				}
				$rankSum[$personId]['sum'] += $row['countryRank'] - $penalty[$eventId];
			}
			$columns[] = array(
				'header'=>"CHtml::tag('span', array(
					'class'=>'event-icon event-icon-white event-icon-$eventId'
				), '&nbsp;')",
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
								 ? $ranks[$eventId][$personId]['countryRank']
								 : $penalty[$eventId];
				if (isset($ranks[$eventId][$personId]) && $ranks[$eventId][$personId]['countryRank'] <= 10) {
					$row[$eventId] = CHtml::tag('span', array('class'=>'top10'), $row[$eventId]);
				} elseif (!isset($ranks[$eventId][$personId])) {
					$row[$eventId] = CHtml::tag('span', array('class'=>'penalty'), $row[$eventId]);
				}
			}
			$rows[] = $row;
		}
		$statistic['count'] = $count;
		$statistic['rank'] = isset($rows[0]) ? count(array_filter($rankSum, function($row) use ($rows) {
			return $row < $rows[0]['sum'];
		})) : 0;
		$statistic['rankKey'] = 'sum';
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

	public static function getRanks($type, $region = 'China') {
		if (isset(self::$_ranks[$type][$region])) {
			return self::$_ranks[$type][$region];
		}
		$command = Yii::app()->wcaDb->createCommand()
		->select('eventId, personId, countryRank, p.gender, p.name AS personName')
		->from('Ranks' . ucfirst($type) . ' r')
		->leftJoin('Persons p', 'r.personId=p.id and p.subid=1')
		->where('p.countryId=:region', array(
			':region'=>$region,
		));
		$ranks = array();
		foreach ($command->queryAll() as $row) {
			$ranks[$row['eventId']][$row['personId']] = $row;
		}
		return self::$_ranks[$type][$region] = $ranks;
	}
}