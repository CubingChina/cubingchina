<?php

class MostNumber extends Statistics {

	public static function build($statistic, $page = 1) {
		$command = Yii::app()->wcaDb->createCommand()
		->from('Results rs');
		if (!isset($statistic['region'])) {
			$statistic['region'] = 'China';
		}
		switch ($statistic['group']) {
			case 'personId':
				$select = array(
					'personId',
					'personName',
					'count(DISTINCT competitionId) AS count',
				);
				$command->leftJoin('Countries country', 'rs.personCountryId=country.id');
				Results::applyRegionCondition($command, $statistic['region']);
				$columns = array(
					array(
						'header'=>'Yii::t("statistics", "Person")',
						'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
						'type'=>'raw',
					),
					array(
						'header'=>'Yii::t("statistics", "Competitions")',
						'name'=>'count',
					),
				);
				break;
			case 'competitionId':
				$select = array(
					'competitionId',
					'c.cellName',
					'c.cityName',
					'count(DISTINCT personId) AS count',
				);
				$command->leftJoin('Competitions c', 'rs.competitionId=c.id');
				$command->leftJoin('Countries country', 'c.countryId=country.id');
				Results::applyRegionCondition($command, $statistic['region'], 'c.countryId');
				$columns = array(
					array(
						'header'=>'Yii::t("common", "Competition")',
						'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
						'type'=>'raw',
					),
					array(
						'header'=>'Yii::t("statistics", "Persons")',
						'name'=>'count',
					),
				);
				break;
		}
		if (isset($statistic['gender'])) {
			$command->leftJoin('Persons p', 'rs.personId=p.id AND p.subid=1');
			switch ($statistic['gender']) {
				case 'female':
					$command->andWhere('p.gender="f"');
					break;
				case 'male':
					$command->andWhere('p.gender="m"');
					break;
			}
		}
		$limit = self::$limit;
		$cmd = clone $command;
		$rows = $command
		->select($select)
		->group($statistic['group'])
		->order('count DESC')
		->limit($limit)
		->offset(($page - 1) * $limit)
		->queryAll();
		if ($statistic['group'] === 'competitionId') {
			$rows = array_map(function($row) {
				return self::getCompetition($row);
			}, $rows);
		}
		$statistic['count'] = $cmd->select('count(DISTINCT ' . $statistic['group'] . ') AS count')->queryScalar();
		$statistic['rank'] = ($page - 1) * $limit;
		$statistic['rankKey'] = 'count'; 
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

}
