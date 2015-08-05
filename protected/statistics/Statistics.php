<?php

class Statistics {

	const CACHE_EXPIRE = 604800;

	public static $limit = 10;
	public static $offset = 0;

	private static $_competitions = array();

	public static $lists = array(
		'Sum of all single ranks'=>array(
			'type'=>'single',
			'class'=>'SumOfRanks',
			'more'=>array(
				'/results/statistics',
				'name'=>'sum-of-ranks',
				'type'=>'single',
			),
		),
		'Sum of all average ranks'=>array(
			'type'=>'average',
			'class'=>'SumOfRanks',
			'more'=>array(
				'/results/statistics',
				'name'=>'sum-of-ranks',
				'type'=>'average'
			),
		),
		'Sum of 2x2 to 5x5 single ranks'=>array(
			'type'=>'single',
			'class'=>'SumOfRanks',
			'eventIds'=>array('222', '333', '444', '555'),
			'width'=>6,
		),
		'Sum of 2x2 to 5x5 average ranks'=>array(
			'type'=>'average',
			'class'=>'SumOfRanks',
			'eventIds'=>array('222', '333', '444', '555'),
			'width'=>6,
		),
		'Sum of country single ranks'=>array(
			'type'=>'single',
			'class'=>'SumOfCountryRanks',
			'more'=>array(
				'/results/statistics',
				'name'=>'sum-of-country-ranks',
				'type'=>'single',
			),
		),
		'Sum of country average ranks'=>array(
			'type'=>'average',
			'class'=>'SumOfCountryRanks',
			'more'=>array(
				'/results/statistics',
				'name'=>'sum-of-country-ranks',
				'type'=>'average'
			),
		),
		'Best "medal collection" of all events'=>array(
			'type'=>'all',
			'class'=>'MedalCollection',
			'width'=>'6',
			'more'=>array(
				'/results/statistics',
				'name'=>'medal-collection',
			),
		),
		'Best "medal collection" in each event'=>array(
			'type'=>'each',
			'class'=>'MedalCollection',
			'width'=>'6',
		),
		'Appearances in top 100 Chinese single results of'=>array(
			'count'=>true,
			'region'=>'China',
			'type'=>'single',
			'class'=>'Top100',
			'event'=>'333',
			'width'=>6,
			'more'=>array(
				'/results/statistics',
				'name'=>'top-100',
				'event'=>'333',
				'type'=>'single',
			),
		),
		'Appearances in top 100 Chinese average results of'=>array(
			'count'=>true,
			'region'=>'China',
			'type'=>'average',
			'class'=>'Top100',
			'event'=>'333',
			'width'=>6,
			'more'=>array(
				'/results/statistics',
				'name'=>'top-100',
				'event'=>'333',
				'type'=>'average',
			),
		),
		'Best podiums'=>array(
			'class'=>'BestPodiums',
			'eventId'=>'333',
			'type'=>'all',
			'more'=>array(
				'/results/statistics',
				'name'=>'best-podiums',
			),
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
		'Oldest standing of current Chinese records in all events'=>array(
			'class'=>'OldestStandingRecords',
		),
		'Most competitions by one person'=>array(
			'class'=>'MostNumber',
			'group'=>'personId',
			'width'=>6,
		),
		'Most persons in one competition'=>array(
			'class'=>'MostNumber',
			'group'=>'competitionId',
			'width'=>6,
		),
		'Most personal solves in one competition'=>array(
			'class'=>'MostSolves',
			'type'=>'person',
			'width'=>6,
		),
		'Most solves in one competition'=>array(
			'class'=>'MostSolves',
			'type'=>'competition',
			'width'=>6,
		),
		'Most personal solves'=>array(
			'class'=>'MostSolves',
			'type'=>'all',
			'width'=>6,
			'more'=>array(
				'/results/statistics',
				'name'=>'most-solves',
			),
		),
		'Most personal solves in each year'=>array(
			'class'=>'MostSolves',
			'type'=>'year',
			'width'=>6,
		),
	);

	public static function getData($removeCache = false) {
		$cache = Yii::app()->cache;
		$cacheKey = 'results_statistics_data';
		if (!$removeCache && ($data = $cache->get($cacheKey)) !== false) {
			return $data;
		}
		$statistics = array();
		foreach (self::$lists as $name=>$statistic) {
			if ($statistic['class'] !== '') {
				$statistics[$name] = $statistic['class']::build($statistic);
			}
		}
		$data = array(
			'statistics'=>$statistics,
			'time'=>time(),
		);
		$cache->set($cacheKey, $data, self::CACHE_EXPIRE);
		return $data;
	}

	public static function buildRankings($statistic, $page = 1, $limit = 100) {
		self::$limit = $limit;
		$cacheKey = 'results_statistics_data_' . serialize($statistic) . '_' . $page;
		$cache = Yii::app()->cache;
		if (($data = $cache->get($cacheKey)) === false) {
			$statistic = $statistic['class']::build($statistic, $page);
			$data = array(
				'statistic'=>$statistic,
				'time'=>time(),
			);
			$cache->set($cacheKey, $data, self::CACHE_EXPIRE);
		}
		return $data;
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
		$class = 'col-md-' . $statistic['width'];
		if ($statistic['width'] < 12 && $statistic['width'] % 3 == 0) {
			$class .= ' col-sm-' . ($statistic['width'] * 2);
		}
		return array_merge($statistic, $data, array(
			'class'=>$class,
			'id'=>strtolower(preg_replace('/(?<!\b)(?=[A-Z])/', '_', $statistic['class'])) . '_' . $i++,
		));
	}

	public static function getCompetition($row) {
		$cacheKey = 'results_competition_data_' . $row['competitionId'];
		$cache = Yii::app()->cache;
		if (self::$_competitions === array()) {
			$competitions = Competition::model()->cache(self::CACHE_EXPIRE)->findAll('wca_competition_id!=""');
			foreach ($competitions as $competition) {
				self::$_competitions[$competition->wca_competition_id] = array(
					'name'=>$competition->name,
					'name_zh'=>$competition->name_zh,
					'url'=>array('/results/c', 'id'=>$competition->wca_competition_id),
				);
			}
		}
		if (isset(self::$_competitions[$row['competitionId']])) {
			$data = self::$_competitions[$row['competitionId']];
		} elseif (($data = $cache->get($cacheKey)) === false) {
			$data['name'] = $data['name_zh'] = $row['cellName'];
			$data['url'] = array('/results/c', 'id'=>$row['competitionId']);
			$cache->set($cacheKey, $data, self::CACHE_EXPIRE);
			self::$_competitions[$row['competitionId']] = $data;
		}
		return array_merge($row, $data);
	}
}
