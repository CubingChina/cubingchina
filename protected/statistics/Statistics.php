<?php

class Statistics {

	const CACHE_EXPIRE = 604800;

	public static $limit = 10;
	public static $offset = 0;

	private static $_competitions = array();

	public static $lists = [
		'Sum of all single ranks'=>[
			'type'=>'single',
			'region'=>'China',
			'class'=>'SumOfRanks',
			'more'=>[
				'/results/statistics',
				'name'=>'sum-of-ranks',
				'type'=>'single',
			],
		],
		'Sum of all average ranks'=>[
			'type'=>'average',
			'class'=>'SumOfRanks',
			'region'=>'China',
			'more'=>[
				'/results/statistics',
				'name'=>'sum-of-ranks',
				'type'=>'average'
			],
		],
		'Sum of 2x2 to 5x5 single ranks'=>[
			'type'=>'single',
			'region'=>'China',
			'class'=>'SumOfRanks',
			'eventIds'=>['222', '333', '444', '555'],
			'width'=>6,
		],
		'Sum of 2x2 to 5x5 average ranks'=>[
			'type'=>'average',
			'region'=>'China',
			'class'=>'SumOfRanks',
			'eventIds'=>['222', '333', '444', '555'],
			'width'=>6,
		],
		'Sum of country single ranks'=>[
			'type'=>'single',
			'class'=>'SumOfCountryRanks',
			'more'=>[
				'/results/statistics',
				'name'=>'sum-of-country-ranks',
				'type'=>'single',
			],
		],
		'Sum of country average ranks'=>[
			'type'=>'average',
			'class'=>'SumOfCountryRanks',
			'more'=>[
				'/results/statistics',
				'name'=>'sum-of-country-ranks',
				'type'=>'average'
			],
		],
		'Best "medal collection" of all events'=>[
			'type'=>'all',
			'class'=>'MedalCollection',
			'width'=>'6',
			'more'=>[
				'/results/statistics',
				'name'=>'medal-collection',
			],
		],
		'Best "medal collection" in each event'=>[
			'type'=>'each',
			'class'=>'MedalCollection',
			'width'=>'6',
		],
		'Best "uncrowned kings" in each event'=>[
			'type'=>'all',
			'class'=>'BestMisser',
			'exclude'=>'pos',
			'pos'=>[1],
			'width'=>'6',
			'more'=>[
				'/results/statistics',
				'name'=>'uncrowned-kings',
			],
		],
		'Best "podium missers" in each event'=>[
			'type'=>'all',
			'class'=>'BestMisser',
			'exclude'=>'pos',
			'pos'=>[1, 2, 3],
			'width'=>'6',
			'more'=>[
				'/results/statistics',
				'name'=>'podium-missers',
			],
		],
		'Best "record missers" in each event'=>[
			'type'=>'all',
			'class'=>'BestMisser',
			'exclude'=>'record',
			'width'=>'6',
			'more'=>[
				'/results/statistics',
				'name'=>'record-missers',
			],
		],
		'Appearances in top 100 Chinese single results of'=>[
			'count'=>true,
			'region'=>'China',
			'type'=>'single',
			'class'=>'Top100',
			'event'=>'333',
			'width'=>6,
			'more'=>[
				'/results/statistics',
				'name'=>'top-100',
				'event'=>'333',
				'type'=>'single',
			],
		],
		'Appearances in top 100 Chinese average results of'=>[
			'count'=>true,
			'region'=>'China',
			'type'=>'average',
			'class'=>'Top100',
			'event'=>'333',
			'width'=>6,
			'more'=>[
				'/results/statistics',
				'name'=>'top-100',
				'event'=>'333',
				'type'=>'average',
			],
		],
		'Best podiums in Chinese competitions'=>[
			'class'=>'BestPodiums',
			'eventId'=>'333',
			'type'=>'all',
			'more'=>[
				'/results/statistics',
				'name'=>'best-podiums',
			],
		],
		'Records set by Chinese competitors'=>[
			'class'=>'RecordsSet',
			'group'=>'personId',
			'width'=>6,
		],
		'Records set in Chinese competitions'=>[
			'class'=>'RecordsSet',
			'group'=>'competitionId',
			'width'=>6,
		],
		'Oldest standing of current Chinese records in all events'=>[
			'class'=>'OldestStandingRecords',
		],
		'Most competitions by one person'=>[
			'class'=>'MostNumber',
			'group'=>'personId',
			'width'=>6,
			'more'=>[
				'/results/statistics',
				'name'=>'most-competitions',
			],
		],
		'Most persons in one competition'=>[
			'class'=>'MostNumber',
			'group'=>'competitionId',
			'width'=>6,
			'more'=>[
				'/results/statistics',
				'name'=>'most-persons',
			],
		],
		'Most personal solves in one competition'=>[
			'class'=>'MostSolves',
			'type'=>'person',
			'width'=>6,
		],
		'Most solves in one competition'=>[
			'class'=>'MostSolves',
			'type'=>'competition',
			'width'=>6,
		],
		'Most personal solves'=>[
			'class'=>'MostSolves',
			'type'=>'all',
			'width'=>6,
			'more'=>[
				'/results/statistics',
				'name'=>'most-solves',
			],
		],
		'Most personal solves in each year'=>[
			'class'=>'MostSolves',
			'type'=>'year',
			'width'=>6,
		],
	];

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
			$competitions = Competition::model()->with('location', 'location.province', 'location.city')->cache(self::CACHE_EXPIRE)->findAll(array(
				'condition'=>'wca_competition_id!=""',
				'select'=>'t.name, t.name_zh, t.wca_competition_id',
			));
			foreach ($competitions as $competition) {
				self::$_competitions[$competition->wca_competition_id] = array(
					'name'=>$competition->name,
					'name_zh'=>$competition->name_zh,
					'city_name'=>$competition->isMultiLocation() ? 'Multiple' : (in_array($competition->location[0]->province_id, array(215, 525, 567, 642)) ? $competition->location[0]->province->name : $competition->location[0]->city->name . ', ' . $competition->location[0]->province->name),
					'city_name_zh'=>$competition->isMultiLocation() ? '多地' : (in_array($competition->location[0]->province_id, array(215, 525, 567, 642)) ? $competition->location[0]->province->name_zh : $competition->location[0]->province->name_zh . $competition->location[0]->city->name_zh),
					'url'=>array('/results/c', 'id'=>$competition->wca_competition_id),
				);
			}
		}
		if (isset(self::$_competitions[$row['competitionId']])) {
			$data = self::$_competitions[$row['competitionId']];
		} elseif (($data = $cache->get($cacheKey)) === false) {
			$data['name'] = $data['name_zh'] = $row['cellName'];
			$data['city_name'] = $data['city_name_zh'] = $row['cityName'];
			$data['url'] = array('/results/c', 'id'=>$row['competitionId']);
			$cache->set($cacheKey, $data, self::CACHE_EXPIRE);
			self::$_competitions[$row['competitionId']] = $data;
		}
		return array_merge($row, $data);
	}
}
