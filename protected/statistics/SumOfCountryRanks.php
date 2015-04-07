<?php

class SumOfCountryRanks extends Statistics {

	private static $_ranks = array();

	public static function build($statistic, $page = 1) {
		$gender = isset($statistic['gender']) ? $statistic['gender'] : 'all';
		$ranks = self::getRanks($statistic['type'], $gender);
		$eventIds = !empty($statistic['eventIds']) ? $statistic['eventIds'] : array_keys(Events::getNormalEvents());
		$columns = array(
			array(
				'header'=>'Yii::t("common", "Region")',
				'value'=>'CHtml::image("http://s.cubingchina.com/flag/" . strtolower($data["iso2"]) . ".png", $data["countryId"], array("class"=>"flag-icon")) . Yii::t("Region", $data["countryId"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "Sum")',
				'value'=>'CHtml::tag("b", array(), $data["sum"])',
				'type'=>'raw',
			),
		);
		//计算未参赛的项目应该排第几
		$penalty = self::getPenalties($statistic['type']);
		$allPenalties = 0;
		foreach ($eventIds as $key=>$eventId) {
			if (!isset($ranks[$eventId])) {
				unset($eventIds[$key]);
				continue;
			}
			$allPenalties += $penalty[$eventId];
		}
		//计算每个人的排名
		$rankSum = array();
		foreach ($eventIds as $eventId) {
			foreach ($ranks[$eventId] as $countryId=>$row) {
				if(!isset($rankSum[$countryId])) {
					$rankSum[$countryId] = $row;
					$rankSum[$countryId]['sum'] = $allPenalties;
				}
				$rankSum[$countryId]['sum'] += $row['worldRank'] - $penalty[$eventId];
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
		$count = count($rankSum);
		if ($page > ceil($count / self::$limit)) {
			$page = ceil($count / self::$limit);
		}
		$rows = array();
		foreach (array_slice($rankSum, ($page - 1) * self::$limit, self::$limit) as $countryId=>$row) {
			foreach ($eventIds as $eventId) {
				$row[$eventId] = isset($ranks[$eventId][$countryId])
								 ? $ranks[$eventId][$countryId]['worldRank']
								 : $penalty[$eventId];
				if (isset($ranks[$eventId][$countryId]) && $ranks[$eventId][$countryId]['worldRank'] <= 10) {
					$row[$eventId] = CHtml::tag('span', array('class'=>'top10'), $row[$eventId]);
				} elseif (!isset($ranks[$eventId][$countryId])) {
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

	public static function getRanks($type, $gender = 'all') {
		if (isset(self::$_ranks[$type])) {
			return self::$_ranks[$type];
		}
		$command = Yii::app()->wcaDb->createCommand()
		->select('eventId, c.name AS countryId, min(worldRank) AS worldRank, c.iso2')
		->from('Ranks' . ucfirst($type) . ' r')
		->leftJoin('Persons p', 'r.personId=p.id')
		->leftJoin('Countries c', 'p.countryId=c.id')
		->where('p.subid=1')
		->group('eventId, countryId');
		switch ($gender) {
			case 'female':
				$command->andWhere('p.gender="f"');
				break;
			case 'male':
				$command->andWhere('p.gender="m"');
				break;
		}
		$ranks = array();
		foreach ($command->queryAll() as $row) {
			$ranks[$row['eventId']][$row['countryId']] = $row;
		}
		return self::$_ranks[$type] = $ranks;
	}

	public static function getPenalties($type) {
		$command = Yii::app()->wcaDb->createCommand()
		->select('eventId, max(worldRank) AS worldRank')
		->from('Ranks' . ucfirst($type) . ' r')
		->group('eventId');
		$penalty = array();
		foreach ($command->queryAll() as $row) {
			$penalty[$row['eventId']] = $row['worldRank'] + 1;
		}
		return $penalty;
	}
}