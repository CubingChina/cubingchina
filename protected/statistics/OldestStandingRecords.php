<?php

class OldestStandingRecords extends Statistics {

	public static function build($statistic) {
		$command = Yii::app()->wcaDb->createCommand()
		->select(array(
			'r.*',
			'rs.personName',
			'rs.competitionId',
			'c.cellName',
			'c.cityName',
			'c.year',
			'c.month',
			'c.day',
		))
		->leftJoin('Persons p', 'r.personId=p.id AND p.subid=1')
		->where('r.countryRank=1 AND rs.personCountryId="China"');
		$rows = array();
		foreach (Results::getRankingTypes() as $type) {
			$cmd = clone $command;
			$cmd->from(sprintf('Ranks%s r', ucfirst($type)))
			->leftJoin('Results rs', sprintf('r.best=rs.%s AND r.personId=rs.personId AND r.eventId=rs.eventId', $type == 'single' ? 'best' : $type))
			->leftJoin('Competitions c', 'rs.competitionId=c.id');
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
				$temp = strcmp($rowA['personName'], $rowB['personName']);
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
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "Days")',
				'value'=>'CHtml::tag("b", array(), floor((time() - strtotime(sprintf("%s-%s-%s", $data["year"], $data["month"], $data["day"]))) / 86400))',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("common", "Event")',
				'value'=>'Events::getFullEventName($data["eventId"])',
			),
			array(
				'header'=>'Yii::t("common", "Type")',
				'value'=>'Yii::t("common", ucfirst($data["type"]))',
			),
			array(
				'header'=>'Yii::t("common", "Result")',
				'value'=>'Results::formatTime($data["best"], $data["eventId"])',
			),
			array(
				'header'=>'Yii::t("common", "Records")',
				'value'=>'$data["worldRank"] == 1 ? "WR" : ($data["continentRank"] == 1 ? "AsR" : "NR")',
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
