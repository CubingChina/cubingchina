<?php

class Summary2016 extends Summary {
	public $year = 2016;

	public function person($person, $data) {
		//competitions, cities
		$competitions = 0;
		$competitionIds = [];
		$visitedRegionList = [];
		$visitedCityList = [];
		$firstCompetition = $lastCompetition = null;
		foreach ($data['competitions'] as $competition) {
			if ($competition->year == $this->year) {
				if ($lastCompetition === null) {
					$lastCompetition = $competition;
				}
				$firstCompetition = $competition;
				$competitions++;
				$competitionIds[] = $competition->id;
				if (!in_array($competition->countryId, ['XA', 'XE', 'XS'])) {
					if (!isset($visitedRegionList[$competition->countryId])) {
						$visitedRegionList[$competition->countryId] = [
							'name'=>$competition->countryId,
							'name_zh'=>$competition->countryId,
							'iso2'=>$competition->country->iso2,
							'count'=>0,
						];
					}
					$visitedRegionList[$competition->countryId]['count']++;
				}
				if (in_array($competition->countryId, ['Hong Kong', 'Macau'])) {
					if (!isset($visitedCityList[$competition->countryId])) {
						$visitedCityList[$competition->countryId] = [
							'name'=>$competition->countryId,
							'name_zh'=>$competition->countryId,
							'count'=>0,
						];
					}
					$visitedCityList[$competition->countryId]['count']++;
				}
			}
		}
		if ($competitions == 0) {
			return [
				'competitions'=>$competitions,
				'person'=>$person,
			];
		}
		$firstDate = strtotime(sprintf('%d-%d-%d', $firstCompetition->year, $firstCompetition->month, $firstCompetition->day));
		$lastDate = strtotime(sprintf('%d-%d-%d', $lastCompetition->year, $lastCompetition->endMonth, $lastCompetition->endDay));
		$chineseCompetitions = Competition::model()->findAllByAttributes([
			'wca_competition_id'=>$competitionIds,
		]);
		foreach ($chineseCompetitions as $competition) {
			if (!$competition->isMultiLocation()) {
				$location = $competition->location[0];
				//Hong Kong, Macau and Taiwan
				if (in_array($location->province_id, [2, 3, 4])) {
					continue;
				}
				$city = in_array($location->province_id, [215, 525, 567, 642]) ? $location->province : $location->city;
				if (!isset($visitedCityList[$city->id])) {
					$visitedCityList[$city->id] = [
						'name'=>$city->name,
						'name_zh'=>$city->name_zh,
						'count'=>0,
					];
				}
				$visitedCityList[$city->id]['count']++;
			}
		}
		usort($visitedRegionList, function($dataA, $dataB) {
			return $dataB['count'] - $dataA['count'];
		});
		usort($visitedCityList, function($dataA, $dataB) {
			return $dataB['count'] - $dataA['count'];
		});
		$visitedRegions = count($visitedRegionList);
		$visitedCities = count($visitedCityList);

		//solves, rounds, records, medals
		$solvesTemplate = [
			'solve'=>0,
			'attempt'=>0,
		];
		$solves = [
			'total'=>$solvesTemplate,
		];
		$events = [];
		$rounds = 0;
		$records = [
			'WR'=>0,
			'CR'=>0,
			'NR'=>0,
		];
		$recordList = [];
		$medalKeys = ['gold', 'silver', 'bronze'];
		$medalsTemplate = [
			'gold'=>0,
			'silver'=>0,
			'bronze'=>0,
		];
		$medals = $medalsTemplate;
		$medalList = [];
		foreach ($data['byEvent'] as $result) {
			if ($result->competition->year != $this->year) {
				continue;
			}
			$rounds++;
			$events[$result->eventId] = 1;
			if (!isset($solves['events'][$result->eventId])) {
				$solves['events'][$result->eventId] = $solvesTemplate;
				$solves['events'][$result->eventId]['event'] = $result->eventId;
			}
			for ($i = 1; $i <= 5; $i++) {
				$value = $result['value' . $i];
				if ($value != 0) {
					$solves['total']['attempt']++;
					$solves['events'][$result->eventId]['attempt']++;
					if ($value > 0) {
						$solves['total']['solve']++;
						$solves['events'][$result->eventId]['solve']++;
					}
				}
			}
			if ($result->best > 0) {
				if ($result->pos <= 3 && in_array($result->roundId, ['c', 'f'])) {
					$medals[$medalKeys[$result->pos - 1]]++;
					if (!isset($medalList[$result->eventId])) {
						$medalList[$result->eventId] = $medalsTemplate;
						$medalList[$result->eventId]['event'] = $result->eventId;
					}
					$medalList[$result->eventId][$medalKeys[$result->pos - 1]]++;
				}
				$hasRecord = false;
				foreach (['regionalSingleRecord', 'regionalAverageRecord'] as $attribute) {
					$record = strtoupper($result->$attribute);
					if ($record != '') {
						$hasRecord = true;
						if ($record == 'WR' || $record == 'NR') {
							$records[$record]++;
						} else {
							$records['CR']++;
						}
					}
				}
				if ($hasRecord) {
					$recordList[] = $result;
				}
			}
		}
		usort($solves['events'], function($solvesA, $solvesB) {
			return $solvesB['solve'] - $solvesA['solve'];
		});
		$events = array_sum($events);
		usort($medalList, function($medalA, $medalB) {
			$temp = $medalB['gold'] - $medalA['gold'];
			if ($temp == 0) {
				$temp = $medalB['silver'] - $medalA['silver'];
			}
			if ($temp == 0) {
				$temp = $medalB['bronze'] - $medalA['bronze'];
			}
			return $temp;
		});

		//personalbests
		$personalBests = [];
		$personalBestsComparison = [];
		if (isset($data['personalBests']['years'][$this->year])) {
			$personalBests = [
				'events'=>$data['personalBests']['years'][$this->year],
			];
			foreach (['best', 'average', 'total'] as $key) {
				$personalBests['total'][$key] = array_sum(array_map(function($pb) use($key) {
					return $pb[$key];
				}, $personalBests['events']));
			}
			uasort($personalBests['events'], function($pbA, $pbB) {
				return $pbB['total'] - $pbA['total'];
			});
			$thisYearsBests = $data['personalBestResults'][$this->year];
			foreach ($personalBests['events'] as $event=>$pb) {
				$personalBests['events'][$event]['event'] = $event;
				$personalBestsComparison['best'][$event] = $this->getBestsComparison($thisYearsBests, $data['personalBestResults'], "$event", 'best');
				$personalBestsComparison['average'][$event] = $this->getBestsComparison($thisYearsBests, $data['personalBestResults'], "$event", 'average');
			}
			$personalBests['events'] = array_values($personalBests['events']);
			foreach (['best', 'average'] as $key) {
				$personalBestsComparison[$key] = array_filter($personalBestsComparison[$key], function($data) {
					return $data['thisYearsBest'] != null && ($data['improvement'] > 0 || $data['event'] == '333mbf' || $data['lastYearsBest'] == null);
				});
				usort($personalBestsComparison[$key], function($dataA, $dataB) {
					return floatval($dataB['improvementPercent']) - floatval($dataA['improvementPercent']) > 0 ? 1 : -1;
				});
			}
		}

		//closest cubers and seen cubers
		$db = Yii::app()->wcaDb;
		$allCubers = $db->createCommand()
		->select(array(
			'personId',
			'personName',
			'count(DISTINCT competitionId) AS count',
		))
		->from('Results')
		->where(array('in', 'competitionId', $competitionIds))
		->group('personId')
		->having('count>1')
		->order('count ASC, personName DESC')
		// ->limit(21)
		->queryAll();
		$closestCubers = array_values(array_filter(array_slice(array_reverse($allCubers), 0, 11), function($cuber) use($person, $competitions) {
			return $cuber['personId'] != $person->id && $cuber['count'] > 1;
		}));
		$onlyOne = [];
		foreach ($closestCubers as $cuber) {
			if ($cuber['count'] == $competitions) {
				if ($onlyOne === []) {
					$onlyOne = $cuber;
				} else {
					$onlyOne = false;
				}
			}
		}
		if ($competitions == 1 || $onlyOne === []) {
			$onlyOne = false;
		}
		$seenCubers = [];
		foreach ($allCubers as $cuber) {
			$count = $cuber['count'];
			if (!isset($seenCubers[$count])) {
				$seenCubers[$count] = [
					'count'=>$count,
					'competitors'=>0,
				];
				if ($count == $competitions) {
					$seenCubers[$count]['competitors']--;
				}
			}
			$seenCubers[$count]['competitors']++;
		}
		ksort($seenCubers);
		$allSeenCubers = $db->createCommand()
		->select(array(
			'count(DISTINCT personId) AS count',
		))
		->from('Results')
		->where(array('in', 'competitionId', $competitionIds))
		->queryScalar();
		$sum = array_sum(array_map(function($data) {
			return $data['competitors'];
		}, $seenCubers));
		array_unshift($seenCubers, [
			'count'=>1,
			'competitors'=>$allSeenCubers - $sum,
		]);
		$seenCubers[] = [
			'count'=>'All',
			'competitors'=>$allSeenCubers,
		];
		$seenCubers = array_values(array_filter($seenCubers, function($data) {
			return $data['competitors'] > 0;
		}));
		return [
			'year'=>$this->year,
			'person'=>$person,
			'competitions'=>$competitions,
			'firstDate'=>$firstDate,
			'lastDate'=>$lastDate,
			'rounds'=>$rounds,
			'events'=>$events,
			'solves'=>$solves,
			'records'=>$records,
			'recordList'=>$recordList,
			'medals'=>$medals,
			'medalList'=>$medalList,
			'visitedRegionList'=>$visitedRegionList,
			'visitedRegions'=>$visitedRegions,
			'visitedCityList'=>$visitedCityList,
			'visitedCities'=>$visitedCities,
			'personalBests'=>$personalBests,
			'personalBestsComparison'=>$personalBestsComparison,
			'seenCubers'=>$seenCubers,
			'closestCubers'=>$closestCubers,
			'cubers'=>$allSeenCubers,
			'onceCubers'=>$allSeenCubers - $sum,
			'onlyOne'=>$onlyOne,
		];
	}

	public function getBestsComparison($thisYearsBests, $personalBestResults, $event, $type) {
		$thisYearsBest = $thisYearsBests[$event][$type];
		$lastYearsBest = null;
		foreach ($personalBestResults as $year=>$value) {
			if ($value != $thisYearsBests && isset($value[$event][$type])) {
				$lastYearsBest = $value[$event][$type];
				break;
			}
		}
		if ($lastYearsBest === null) {
			$improvement = null;
			$improvementPercent = null;
		} else {
			if ($event === '333mbf') {
				$thisYearsScore = Results::getMBFPoints($thisYearsBest->$type);
				$lastYearsScore = Results::getMBFPoints($lastYearsBest->$type);
				$improvement = $thisYearsScore - $lastYearsScore;
				$improvementPercent = number_format($improvement / $lastYearsScore * 100, 2, '.', '');
				//if two years' scores are the same
				if ($improvement == 0) {

				}
			} else {
				$improvement = $lastYearsBest->$type - $thisYearsBest->$type;
				$improvementPercent = number_format($improvement / $thisYearsBest->$type * 100, 2, '.', '');
			}
		}
		return compact('event', 'improvement', 'improvementPercent', 'thisYearsBest', 'lastYearsBest');
	}

	public static function getRecordsDetail($records, $person) {
		$types = 0;
		foreach (['WR', 'CR', 'NR'] as $key) {
			if ($records[$key] > 0) {
				$types++;
			}
		}
		switch ($types) {
			case 1:
				$temp = array_sum($records);
				$key = array_search($temp, $records);
				$params = [
					'{type}'=>CHtml::tag('span', ['class'=>'record'], Yii::t('common', self::translateCR($key, $person))),
				];
				switch ($temp) {
					case 1:
						return Yii::t('summary', 'which was a {type}', $params);
					case 2:
						return Yii::t('summary', 'and both were {type}s', $params);
					default:
						return Yii::t('summary', 'and all were {type}s', $params);
				}
				break;
			case 2:
				$key = array_search(0, $records);
				unset($records[$key]);
				$keys = array_keys($records);
				return Yii::t('summary', 'including {n1} {type1}{s1} and {n2} {type2}{s2}', [
					'{n1}'=>CHtml::tag('span', ['class'=>'num'], $records[$keys[0]]),
					'{n2}'=>CHtml::tag('span', ['class'=>'num'], $records[$keys[1]]),
					'{type1}'=>CHtml::tag('span', ['class'=>'record'], Yii::t('common', self::translateCR($keys[0], $person))),
					'{type2}'=>CHtml::tag('span', ['class'=>'record'], Yii::t('common', self::translateCR($keys[1], $person))),
					'{s1}'=>$records[$keys[0]] > 1 ? 's' : '',
					'{s2}'=>$records[$keys[1]] > 1 ? 's' : '',
				]);
			default:
				$keys = array_keys($records);
				return Yii::t('summary', 'including {n1} {type1}{s1}, {n2} {type2}{s2}, and {n3} {type3}{s3}', [
					'{n1}'=>CHtml::tag('span', ['class'=>'num'], $records[$keys[0]]),
					'{n2}'=>CHtml::tag('span', ['class'=>'num'], $records[$keys[1]]),
					'{n3}'=>CHtml::tag('span', ['class'=>'num'], $records[$keys[2]]),
					'{type1}'=>CHtml::tag('span', ['class'=>'record'], Yii::t('common', self::translateCR($keys[0], $person))),
					'{type2}'=>CHtml::tag('span', ['class'=>'record'], Yii::t('common', self::translateCR($keys[1], $person))),
					'{type3}'=>CHtml::tag('span', ['class'=>'record'], Yii::t('common', self::translateCR($keys[2], $person))),
					'{s1}'=>$records[$keys[0]] > 1 ? 's' : '',
					'{s2}'=>$records[$keys[1]] > 1 ? 's' : '',
					'{s3}'=>$records[$keys[2]] > 1 ? 's' : '',
				]);
		}
	}

	public static function translateCR($record, $person) {
		if ($record != 'CR') {
			return $record;
		}
		$continent = $person->country->continent;
		return $continent->recordName;
	}

	public static function getMedalsDetail($medals, $person) {
		$types = 0;
		foreach (['gold', 'silver', 'bronze'] as $key) {
			if ($medals[$key] > 0) {
				$types++;
			}
		}
		switch ($types) {
			case 1:
				$temp = array_sum($medals);
				$key = array_search($temp, $medals);
				$params = [
					'{type}'=>CHtml::tag('span', ['class'=>'medal'], Yii::t('common', $key)),
				];
				switch ($temp) {
					case 1:
						return Yii::t('summary', 'which was a {type}', $params);
					case 2:
						return Yii::t('summary', 'and both were {type}s', $params);
					default:
						return Yii::t('summary', 'and all were {type}s', $params);
				}
				break;
			case 2:
				$key = array_search(0, $medals);
				unset($medals[$key]);
				$keys = array_keys($medals);
				return Yii::t('summary', 'including {n1} {type1}{s1} and {n2} {type2}{s2}', [
					'{n1}'=>CHtml::tag('span', ['class'=>'num'], $medals[$keys[0]]),
					'{n2}'=>CHtml::tag('span', ['class'=>'num'], $medals[$keys[1]]),
					'{type1}'=>CHtml::tag('span', ['class'=>'medal'], Yii::t('common', $keys[0])),
					'{type2}'=>CHtml::tag('span', ['class'=>'medal'], Yii::t('common', $keys[1])),
					'{s1}'=>$medals[$keys[0]] > 1 ? 's' : '',
					'{s2}'=>$medals[$keys[1]] > 1 ? 's' : '',
				]);
			default:
				$keys = array_keys($medals);
				return Yii::t('summary', 'including {n1} {type1}{s1}, {n2} {type2}{s2}, and {n3} {type3}{s3}', [
					'{n1}'=>CHtml::tag('span', ['class'=>'num'], $medals[$keys[0]]),
					'{n2}'=>CHtml::tag('span', ['class'=>'num'], $medals[$keys[1]]),
					'{n3}'=>CHtml::tag('span', ['class'=>'num'], $medals[$keys[2]]),
					'{type1}'=>CHtml::tag('span', ['class'=>'medal'], Yii::t('common', $keys[0])),
					'{type2}'=>CHtml::tag('span', ['class'=>'medal'], Yii::t('common', $keys[1])),
					'{type3}'=>CHtml::tag('span', ['class'=>'medal'], Yii::t('common', $keys[2])),
					'{s1}'=>$medals[$keys[0]] > 1 ? 's' : '',
					'{s2}'=>$medals[$keys[1]] > 1 ? 's' : '',
					'{s3}'=>$medals[$keys[2]] > 1 ? 's' : '',
				]);
		}
	}
}