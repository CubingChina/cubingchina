<?php

class OldestStandingRecords extends Statistics {

	public static function build($statistic) {
		$command = Yii::app()->wcaDb->createCommand()
		->select(array(
			'r.*',
			'rs.person_name',
			'rs.competition_id',
			'c.cell_name',
			'c.city_name',
			'c.year',
			'c.month',
			'c.day',
		))
		->leftJoin('persons p', 'r.person_id=p.wca_id AND p.sub_id=1')
		->leftJoin('events e', 'r.event_id=e.id')
		->where('r.country_rank=1 AND rs.person_country_id="China" AND e.`rank`<900');
		$rows = array();
		foreach (Results::getRankingTypes() as $type) {
			$cmd = clone $command;
			$cmd->from(sprintf('ranks_%s r', $type))
			->leftJoin('results rs', sprintf('r.best=rs.%s AND r.person_id=rs.person_id AND r.event_id=rs.event_id', $type == 'single' ? 'best' : $type))
			->leftJoin('competitions c', 'rs.competition_id=c.id');
			$rows[$type] = array();
			foreach ($cmd->queryAll() as $row) {
				$row['type'] = $type;
				$row = self::getCompetition($row);
				$rows[$type][] = $row;
			}
		}
		$rows = array_merge(array_values($rows['single']), array_values($rows['average']));
		usort($rows, function($rowA, $rowB) {
			$temp = $rowA['year'] - $rowB['year'];
			if ($temp == 0) {
				$temp = $rowA['month'] - $rowB['month'];
			}
			if ($temp == 0) {
				$temp = $rowA['day'] - $rowB['day'];
			}
			if ($temp == 0) {
				$temp = strcmp($rowA['person_name'], $rowB['person_name']);
			}
			return $temp;
		});
		$temp = [];
		$day = $rows[0]['day'];
		foreach ($rows as $row) {
			if ($row['day'] != $day) {
				$day = $row['day'];
				if (count($temp) >= self::$limit) {
					break;
				}
			}
			$temp[] = $row;
		}
		$rows = $temp;
		//person days event type result record competition
		$columns = array(
			array(
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "Days")',
				'value'=>'CHtml::tag("b", array(), floor((time() - strtotime(sprintf("%s-%s-%s", $data["year"], $data["month"], $data["day"]))) / 86400))',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("common", "Event")',
				'value'=>'Events::getFullEventName($data["event_id"])',
			),
			array(
				'header'=>'Yii::t("common", "Type")',
				'value'=>'Yii::t("common", ucfirst($data["type"]))',
			),
			array(
				'header'=>'Yii::t("common", "Result")',
				'value'=>'Results::formatTime($data["best"], $data["event_id"])',
			),
			array(
				'header'=>'Yii::t("common", "Records")',
				'value'=>'$data["world_rank"] == 1 ? "WR" : ($data["continent_rank"] == 1 ? "AsR" : "NR")',
			),
			array(
				'header'=>'Yii::t("common", "Competition")',
				'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
				'type'=>'raw',
			),
		);
		return self::makeStatisticsData($statistic, $columns, $rows);
	}
}
