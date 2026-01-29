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
				'value'=>'Region::getIconName($data["country_id"], $data["iso2"])',
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
		foreach ($eventIds as $key=>$event_id) {
			if (!isset($ranks[$event_id])) {
				unset($eventIds[$key]);
				continue;
			}
			$allPenalties += $penalty[$event_id];
		}
		//计算每个人的排名
		$rankSum = array();
		foreach ($eventIds as $event_id) {
			foreach ($ranks[$event_id] as $country_id=>$row) {
				if(!isset($rankSum[$country_id])) {
					$rankSum[$country_id] = $row;
					$rankSum[$country_id]['sum'] = $allPenalties;
				}
				$rankSum[$country_id]['sum'] += $row['world_rank'] - $penalty[$event_id];
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
		$count = count($rankSum);
		if ($page > ceil($count / self::$limit)) {
			$page = ceil($count / self::$limit);
		}
		$rows = array();
		foreach (array_slice($rankSum, ($page - 1) * self::$limit, self::$limit) as $country_id=>$row) {
			foreach ($eventIds as $event_id) {
				$row[$event_id] = isset($ranks[$event_id][$country_id])
								 ? $ranks[$event_id][$country_id]['world_rank']
								 : $penalty[$event_id];
				if (isset($ranks[$event_id][$country_id]) && $ranks[$event_id][$country_id]['world_rank'] <= 10) {
					$row[$event_id] = CHtml::tag('span', array('class'=>'top10'), $row[$event_id]);
				} elseif (!isset($ranks[$event_id][$country_id])) {
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

	public static function getRanks($type, $gender = 'all') {
		if (isset(self::$_ranks[$type])) {
			return self::$_ranks[$type];
		}
		$command = Yii::app()->wcaDb->createCommand()
		->select('event_id, c.name AS country_id, min(world_rank) AS world_rank, c.iso2')
		->from(sprintf('ranks_%s r', $type))
		->leftJoin('persons p', 'r.person_id=p.wca_id')
		->leftJoin('countries c', 'p.country_id=c.id')
		->where('p.sub_id=1')
		->group('event_id, country_id');
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
			$ranks[$row['event_id']][$row['country_id']] = $row;
		}
		return self::$_ranks[$type] = $ranks;
	}

	public static function getPenalties($type) {
		$command = Yii::app()->wcaDb->createCommand()
		->select('event_id, max(world_rank) AS world_rank')
		->from(sprintf('ranks_%s r', $type))
		->group('event_id');
		$penalty = array();
		foreach ($command->queryAll() as $row) {
			$penalty[$row['event_id']] = $row['world_rank'] + 1;
		}
		return $penalty;
	}
}
