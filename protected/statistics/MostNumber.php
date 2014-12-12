<?php

class MostNumber extends Statistics {

	public static function build($statistic) {
		$command = Yii::app()->wcaDb->createCommand()
		->from('Results r')
		->group($statistic['group'])
		->order('count DESC')
		->limit(10);
		switch ($statistic['group']) {
			case 'personId':
				$select = array(
					'personId',
					'personName',
					'count(DISTINCT competitionId) AS count',
				);
				$command->andWhere('personCountryId="China"');
				$columns = array(
					array(
						'header'=>Yii::t('statistics', 'Person'),
						'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
						'type'=>'raw',
					),
					array(
						'header'=>Yii::t('statistics', 'Competitions'),
						'name'=>'count',
					),
				);
				break;
			case 'competitionId':
				$select = array(
					'competitionId',
					'c.cellName',
					'count(DISTINCT personId) AS count',
				);
				$command->leftJoin('Competitions c', 'r.competitionId=c.id');
				$command->andWhere('c.countryId="China"');
				$columns = array(
					array(
						'header'=>Yii::t('common', 'Competition'),
						'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
						'type'=>'raw',
					),
					array(
						'header'=>Yii::t('statistics', 'Persons'),
						'name'=>'count',
					),
				);
				break;
		}
		$rows = $command->select($select)->queryAll();
		if ($statistic['group'] === 'competitionId') {
			$rows = array_map(function($row) {
				return self::getCompetition($row);
			}, $rows);
		}
		return self::makeStatisticsData($statistic, $columns, $rows);
	}

}
