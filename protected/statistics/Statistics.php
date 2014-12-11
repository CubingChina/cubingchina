<?php

class Statistics {

	public static $lists = array(
		'Sum of all single ranks'=>array(
			'type'=>'single',
			'class'=>'SumOfRanks',
		),
		'Sum of all average ranks'=>array(
			'type'=>'average',
			'class'=>'SumOfRanks',
		),
		'Sum of 2x2 to 5x5 single ranks'=>array(
			'type'=>'single',
			'class'=>'SumOfRanks',
			'eventIds'=>array('222', '333', '444', '555'),
			'width'=>'6',
		),
		'Sum of 2x2 to 5x5 average ranks'=>array(
			'type'=>'average',
			'class'=>'SumOfRanks',
			'eventIds'=>array('222', '333', '444', '555'),
			'width'=>'6',
		),
		'Best "medal collection" of all events'=>array(
			'type'=>'all',
			'class'=>'MedalCollection',
			'width'=>'6',
		),
		'Best "medal collection" in each event'=>array(
			'type'=>'each',
			'class'=>'MedalCollection',
			'width'=>'6',
		),
		'Appearances in top 100 Chinese competitors\' single results of Rubik\'s Cube'=>array(
			'type'=>'single',
			'class'=>'',
		),
		'Appearances in top 100 Chinese competitors\' average results of Rubik\'s Cube'=>array(
			'type'=>'single',
			'class'=>'',
		),
		'Best Podiums in Rubik\'s Cube event'=>array(
			'class'=>'BestPodiums',
			'eventId'=>'333',
		),
		'Records set by Chinese competitors'=>array(
			'class'=>'RecordsSet',
			'group'=>'personId',
			'width'=>6,
		),
		'Records set in Chinese competitions'=>array(
			'class'=>'RecordsSet',
			'group'=>'competitionId',
			'width'=>6,
		),
		'Oldest Standing of current Chinese records in all events'=>array(
			'type'=>'single',
			'class'=>'',
		),
		'Most Persons in one competition'=>array(
			'type'=>'single',
			'class'=>'',
		),
		'Most competitions by one person'=>array(
			'type'=>'single',
			'class'=>'',
		),
		'Most solves in one competition'=>array(
			'type'=>'single',
			'class'=>'',
		),
		'Most solves per year'=>array(
			'type'=>'single',
			'class'=>'',
		),
	);

	public static function getData() {
		$statistics = array();
		foreach (self::$lists as $name=>$statistic) {
			if ($statistic['class'] !== '') {
				$statistics[$name] = $statistic['class']::build($statistic);
			}
		}
		return $statistics;
	}

	protected static function makeStatisticsData($statistic, $columns, $rows = null) {
		static $i = 0;
		if ($rows === null) {
			$data = $columns;
		} else {
			$data = array(
				'columns'=>$columns,
				'rows'=>$rows,
			);
		}
		if (!isset($statistic['width'])) {
			$statistic['width'] = 12;
		}
		return array_merge($data, array(
			'class'=>'col-md-' . $statistic['width'],
			'id'=>strtolower(preg_replace('/(?<!\b)(?=[A-Z])/', '_', substr($statistic['class'], 3))) . '_' . $i++,
		));
	}

	protected static function getCompetition($row) {
		$competition = Competition::model()->findByAttributes(array(
			'wca_competition_id'=>$row['competitionId'],
		));
		if ($competition === null) {
			$row['name'] = $row['name_zh'] = $row['cellName'];
			$row['url'] = 'http://www.worldcubeassociation.org/results/c.php?i=' . $row['competitionId'];
		} else {
			$row['name'] = $competition->name;
			$row['name_zh'] = $competition->name_zh;
			$row['url'] = $competition->url;
		}
		return $row;
	}

}
