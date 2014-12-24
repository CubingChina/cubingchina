<?php

class MostSolves extends Statistics {

	public static function build($statistic, $page = 1, $recursive = true) {
		$limit = self::$limit;
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
			AS attempt',
			'competitionId',
			'personId',
			'personName',
			'cellName',
		))
		->from('Results r')
		->leftJoin('Competitions c', 'r.competitionId=c.id')
		->where('personCountryId="China"');
		$cmd = clone $command;
		$command->group('personId')
		->order('solve DESC, attempt ASC')
		->limit($limit)
		->offset(($page - 1) * $limit);
		$columns = array(
			array(
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "Solves/Attempts")',
				'value'=>'$data["solve"] . "/" . $data["attempt"]',
			),
		);
		switch ($statistic['type']) {
			case 'competition':
				$columns[0] = array(
					'header'=>'Yii::t("common", "Competition")',
					'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
					'type'=>'raw',
				);
				$rows = $command->where('c.countryId="China"')->group('competitionId')->queryAll();
				$rows = array_map(function($row) {
					return self::getCompetition($row);
				}, $rows);
				return self::makeStatisticsData($statistic, $columns, $rows);
			case 'person':
				$rows = $command->group('competitionId, personId')->limit($limit + 50)->queryAll();
				$temp = array();
				foreach ($rows as $row) {
					if (!isset($temp[$row['personId']])) {
						$temp[$row['personId']] = $row;
					}
					if (count($temp) == $limit) {
						break;
					}
				}
				$rows = array_map(function($row) {
					return self::getCompetition($row);
				}, array_values($temp));
				$columns[] = array(
					'header'=>'Yii::t("common", "Competition")',
					'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
					'type'=>'raw',
				);
				return self::makeStatisticsData($statistic, $columns, $rows);
			case 'all':
				$rows = array();
				foreach ($command->queryAll() as $row) {
					$row['rank'] = $row['solve'] . '_' . $row['attempt'];
					$rows[] = $row;
				}
				$statistic['count'] = $cmd->select('count(DISTINCT personId) AS count')->queryScalar();
				$statistic['rank'] = ($page - 1) * $limit;
				$statistic['rankKey'] = 'rank'; 
				if ($page > 1 && $rows !== array() && $recursive) {
					$stat = self::build($statistic, $page - 1, false);
					foreach (array_reverse($stat['rows']) as $row) {
						if ($row['rank'] === $rows[0]['rank']) {
							$statistic['rank']--;
						} else {
							break;
						}
					}
				}
				return self::makeStatisticsData($statistic, $columns, $rows);
			case 'year':
				$years = Competition::getYears();
				$years = array_slice($years, 1);
				$solves = array();
				foreach ($years as $key=>$year) {
					$cmd = clone $command;
					$rows = $cmd->andWhere("year={$year}")->queryAll();
					if (count($rows) < $limit) {
						unset($years[$key]);
						continue;
					}
					$solves[$year] = self::makeStatisticsData($statistic, $columns, $rows);
				}
				return self::makeStatisticsData($statistic, array(
					'statistic'=>$solves,
					'select'=>array_combine($years, $years),
				));
		}
	}

}
