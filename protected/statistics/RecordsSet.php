<?php

class RecordsSet extends Statistics {

	public static function build($statistic) {
		$command = Yii::app()->wcaDb->createCommand()
		->from('results r')
		->where('regional_single_record!="" OR regional_average_record!=""')
		->group($statistic['group'])
		->order('score DESC, WR DESC, CR DESC, NR DESC')
		->limit(self::$limit);
		$select = array();
		$score = array();
		$columns = array();
		foreach (array('WR'=>10, 'CR'=>5, 'NR'=>1) as $record=>$weight) {
			$temp = sprintf('sum(CASE WHEN regional_single_record="%s" THEN 1 ELSE 0 END) + sum(CASE WHEN regional_average_record="%s" THEN 1 ELSE 0 END)', $record, $record);
			if ($record === 'CR') {
				$temp = 'sum(CASE WHEN regional_single_record NOT IN ("WR", "NR", "") THEN 1 ELSE 0 END) + sum(CASE WHEN regional_average_record NOT IN ("WR", "NR", "") THEN 1 ELSE 0 END)';
			}
			$select[] = sprintf('%s AS %s', $temp, $record);
			$score[] = sprintf('(%s) * %d', $temp, $weight);
			$columns[] = array(
				'header'=>"Yii::t('common', '$record')",
				'name'=>$record,
			);
		}
		$score = implode(' + ', $score) . ' AS score';
		$select[] = $score;
		array_unshift($columns, array(
			'header'=>"CHtml::tag('span', array(
				'title'=>'WR: 10\nCR: 5\nNR: 1',
			), Yii::t('statistics', 'Score') . Html::fontAwesome('question-circle'))",
			'value'=>'CHtml::tag("b", array(), $data["score"])',
			'type'=>'raw',
		));
		switch ($statistic['group']) {
			case 'person_id':
				$columns[0]['header'] = str_replace('CR', 'AsR', $columns[0]['header']);
				$columns[2]['header'] = "Yii::t('common', 'AsR')";
				$select = array_merge($select, array(
					'person_id',
					'person_name',
				));
				$command->andWhere('person_country_id="China"');
				array_unshift($columns, array(
					'header'=>'Yii::t("statistics", "Person")',
					'value'=>'Persons::getLinkByNameNId($data["person_name"], $data["person_id"])',
					'type'=>'raw',
				));
				break;
			case 'competition_id':
				$select = array_merge($select, array(
					'competition_id',
					'c.cell_name',
					'c.city_name',
				));
				$command->leftJoin('competitions c', 'r.competition_id=c.id');
				$command->andWhere('c.country_id="China"');
				array_unshift($columns, array(
					'header'=>'Yii::t("common", "competitions")',
					'value'=>'CHtml::link(ActiveRecord::getModelAttributeValue($data, "name"), $data["url"])',
					'type'=>'raw',
				));
				break;
		}
		$rows = $command->select($select)->queryAll();
		if ($statistic['group'] === 'competition_id') {
			$rows = array_map(function($row) {
				return self::getCompetition($row);
			}, $rows);
		}
		return self::makeStatisticsData($statistic, $columns, $rows);
	}
}
