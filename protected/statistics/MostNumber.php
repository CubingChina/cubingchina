<?php

class MostNumber extends Statistics {

	public static function build($statistic, $page = 1) {
		$command = Yii::app()->wcaDb->createCommand()
		->from('results rs');
		if (!isset($statistic['region'])) {
			$statistic['region'] = 'China';
		}
		switch ($statistic['group']) {
			case 'person_id':
				$select = array(
					'person_id',
					'person_name',
					'count(DISTINCT competition_id) AS count',
				);
				$command->leftJoin('countries country', 'rs.person_country_id=country.id');
				Results::applyRegionCondition($command, $statistic['region']);
				$columns = array(
					array(
						'header'=>'Yii::t("statistics", "Person")',
						'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
						'type'=>'raw',
					),
					array(
						'header'=>'Yii::t("statistics", "competitions")',
						'name'=>'count',
					),
				);
				break;
			case 'competition_id':
				$select = array(
					'competition_id',
					'c.cell_name',
					'c.city_name',
					'count(DISTINCT person_id) AS count',
				);
				$command->leftJoin('competitions c', 'rs.competition_id=c.id');
				$command->leftJoin('countries country', 'c.country_id=country.id');
				Results::applyRegionCondition($command, $statistic['region'], 'c.country_id');
				$columns = array(
					array(
						'header'=>'Yii::t("common", "Competition")',
						'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
						'type'=>'raw',
					),
					array(
						'header'=>'Yii::t("statistics", "persons")',
						'name'=>'count',
					),
				);
				break;
		}
		if (isset($statistic['gender'])) {
			$command->leftJoin('persons p', 'rs.person_id=p.wca_id AND p.sub_id=1');
			switch ($statistic['gender']) {
				case 'female':
					$command->andWhere('p.gender="f"');
					break;
				case 'male':
					$command->andWhere('p.gender="m"');
					break;
			}
		}
		if (isset($statistic['year'])) {
			$command->andWhere('competition_id LIKE :year', [
				':year'=>'%' . $statistic['year'],
			]);
		}
		$limit = self::$limit;
		$cmd = clone $command;
		$rows = $command
		->select($select)
		->group($statistic['group'])
		->order(['count DESC', $statistic['group'] . ' ASC'])
		->limit($limit)
		->offset(($page - 1) * $limit)
		->queryAll();
		if ($statistic['group'] === 'competition_id') {
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
