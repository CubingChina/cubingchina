<?php

class BestPodiums extends Statistics {

	public static function build($statistic) {
		$command = Yii::app()->wcaDb->createCommand();
		$command->select(array(
			'r.competitionId',
			'r.roundId',
			'sum(r.average) AS sum',
			'c.cellName',
		))
		->from('Results r')
		->leftJoin('Competitions c', 'r.competitionId=c.id')
		->where("r.eventId='{$statistic['eventId']}'")
		->andWhere('r.roundId IN ("c", "f")')
		->andWhere('r.pos IN (1,2,3)')
		->andWhere('c.countryId="China"')
		->group('r.competitionId')
		->order('sum ASC')
		->limit(10);
		$columns = array(
			array(
				'header'=>Yii::t('common', 'Competition'),
				'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
				'type'=>'raw',
			),
			array(
				'header'=>Yii::t('statistics', 'Sum'),
				'value'=>'CHtml::tag("b", array(), Results::formatTime($data["sum"], "333"))',
				'type'=>'raw',
			),
			array(
				'header'=>Yii::t('statistics', 'First'),
				'value'=>'Persons::getLinkByNameNId($data["first"]["personName"], $data["first"]["personId"])',
				'type'=>'raw',
			),
			array(
				'header'=>'',
				'value'=>'Results::formatTime($data["first"]["average"], "333")',
			),
			array(
				'header'=>Yii::t('statistics', 'Second'),
				'value'=>'Persons::getLinkByNameNId($data["second"]["personName"], $data["second"]["personId"])',
				'type'=>'raw',
			),
			array(
				'header'=>'',
				'value'=>'Results::formatTime($data["second"]["average"], "333")',
			),
			array(
				'header'=>Yii::t('statistics', 'Third'),
				'value'=>'Persons::getLinkByNameNId($data["third"]["personName"], $data["third"]["personId"])',
				'type'=>'raw',
			),
			array(
				'header'=>'',
				'value'=>'Results::formatTime($data["third"]["average"], "333")',
			),
		);
		$rows = array();
		foreach ($command->queryAll() as $row) {
			$row = self::getCompetition($row);
			$row["first"] = self::getPodiumsAverage($row['competitionId'], $row['roundId'], 1);
			$row["second"] = self::getPodiumsAverage($row['competitionId'], $row['roundId'], 2);
			$row["third"] = self::getPodiumsAverage($row['competitionId'], $row['roundId'], 3);
			$rows[] = $row;
		}
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

	private static function getPodiumsAverage($competitionId, $roundId, $pos) {
		return Yii::app()->wcaDb->createCommand()
		->select('personId, personName, average')
		->from('Results')
		->where("competitionId='{$competitionId}'")
		->andWhere("roundId='{$roundId}'")
		->andWhere("pos={$pos}")
		->queryRow();
	}
}
