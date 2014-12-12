<?php

class MostSolves extends Statistics {

	public static function build($statistic) {
		$command = Yii::app()->wcaDb->createCommand()
		->select(array(
			'sum(CASE WHEN value1>0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value2>0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value3>0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value4>0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value5>0 THEN 1 ELSE 0 END)
			AS solve',
			'sum(CASE WHEN value1>-2 AND value1!=0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value2>-2 AND value2!=0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value3>-2 AND value3!=0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value4>-2 AND value4!=0 THEN 1 ELSE 0 END)
			+sum(CASE WHEN value5>-2 AND value5!=0 THEN 1 ELSE 0 END)
			AS try',
			'competitionId',
			'personId',
			'personName',
			'cellName',
		))
		->from('Results r')
		->leftJoin('Competitions c', 'r.competitionId=c.id')
		->where('personCountryId="China"')
		->order('solve DESC, try ASC')
		->limit(10);
		$columns = array(
			array(
				'header'=>Yii::t('statistics', 'Person'),
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
				'type'=>'raw',
			),
			array(
				'header'=>Yii::t('statistics', 'Solve/Try'),
				'value'=>'$data["solve"] . "/" . $data["try"]',
			),
			array(
				'header'=>Yii::t('common', 'Competitions'),
				'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
				'type'=>'raw',
			),
		);
		if ($statistic['type'] === 'all') {
			$rows = $command->group('competitionId, personId')->limit(50)->queryAll();
			$temp = array();
			foreach ($rows as $row) {
				if (!isset($temp[$row['personId']])) {
					$temp[$row['personId']] = $row;
				}
				if (count($temp) == 10) {
					break;
				}
			}
			$rows = array_map(function($row) {
				return self::getCompetition($row);
			}, array_values($temp));
			return self::makeStatisticsData($statistic, $columns, $rows);
		} else {
			$years = Competition::getYears();
			$years = array_slice($years, 1);
			$solves = array();
			$command->group('personId');
			foreach ($years as $key=>$year) {
				$cmd = clone $command;
				$rows = $cmd->andWhere("year={$year}")->queryAll();
				if (count($rows) < 10) {
					unset($years[$key]);
					continue;
				}
				$rows = array_map(function($row) {
					return self::getCompetition($row);
				}, $rows);
				$solves[$year] = self::makeStatisticsData($statistic, $columns, $rows);
			}
			return self::makeStatisticsData($statistic, array(
				'statistic'=>$solves,
				'select'=>array_combine($years, $years),
			));
		}
	}

}
