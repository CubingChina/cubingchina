<?php

class MostSolves extends Statistics {

	public static function build($statistic, $page = 1, $recursive = true) {
		$limit = self::$limit;
		$command = Yii::app()->wcaDb->createCommand()
		->select(array(
			'sum(solve) AS solve',
			'sum(attempt) AS attempt',
			'competition_id',
			'person_id',
			'person_name',
			'cell_name',
			'city_name',
			'p.country_id',
			'country.iso2',
		))
		->from('results rs')
		->leftJoin('persons p', 'rs.person_id=p.wca_id AND p.sub_id=1')
		->leftJoin('countries country', 'p.country_id=country.id')
		->leftJoin('competitions c', 'rs.competition_id=c.id');
		if (isset($statistic['region'])) {
			ActiveRecord::applyRegionCondition($command, $statistic['region'], 'p.country_id');
		} else {
			$command->where('p.country_id="China"');
		}
		if (!empty($statistic['eventIds'])) {
			$command->andWhere(array('in', 'event_id', $statistic['eventIds']));
		}
		if (isset($statistic['gender'])) {
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
		$cmd = clone $command;
		$command->group('person_id')
		->order('solve DESC, attempt ASC')
		->limit($limit)
		->offset(($page - 1) * $limit);
		$columns = array(
			array(
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("statistics", "Solves/Attempts")',
				'value'=>'$data["solve"] . "/" . $data["attempt"]',
			),
		);
		if (isset($statistic['region'])) {
			$columns[] = array(
				'header'=>'Yii::t("common", "Region")',
				'value'=>'Region::getIconName($data["country_id"], $data["iso2"])',
				'type'=>'raw',
			);
		}
		switch ($statistic['type']) {
			case 'competition':
				$columns[0] = array(
					'header'=>'Yii::t("common", "Competition")',
					'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
					'type'=>'raw',
				);
				$rows = $command->where('c.country_id="China"')->group('competition_id')->queryAll();
				$rows = array_map(function($row) {
					return self::getCompetition($row);
				}, $rows);
				return self::makeStatisticsData($statistic, $columns, $rows);
			case 'person':
				$rows = $command->group('competition_id, person_id')->limit($limit + 50)->queryAll();
				$temp = array();
				foreach ($rows as $row) {
					if (!isset($temp[$row['person_id']])) {
						$temp[$row['person_id']] = $row;
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
				$statistic['count'] = $cmd->select('count(DISTINCT person_id) AS count')->queryScalar();
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
