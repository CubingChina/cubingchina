<?php

Yii::import('application.controllers.CompetitionController');

class LiveController extends CompetitionController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionLive() {
		$competition = $this->getCompetition();
		if ($competition->live == Competition::NO || $competition->canRegister()) {
			$this->redirect($competition->getUrl());
		}
		$competition->initLiveData();
		$min = DEV ? '' : '.min';
		$clientScript = Yii::app()->clientScript;
		$clientScript->registerScriptFile('/f/js/websocket' . $min . '.js?ver=20171007');
		$clientScript->registerScriptFile('/f/js/store.min.js');
		$clientScript->registerScriptFile('/f/plugins/vue/vue' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/vue-router/vue-router' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/vuex/vuex' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/moment/moment' . $min . '.js');
		$clientScript->registerScriptFile('/f/js/live' . $min . '.js?ver=20171011');
		$events = $competition->getEventsRoundTypes();
		$params = $competition->getLastActiveEventRound($events);
		$htmlOptions = [
			'id'=>'live-container',
			'data-c'=>$competition->id,
			'data-events'=>json_encode($events),
			'data-params'=>json_encode($params),
			'data-filters'=>json_encode([
				[
					'label'=>Yii::t('common', 'All'),
					'value'=>'all',
				],
				[
					'label'=>Yii::t('live', 'Females'),
					'value'=>'females',
				],
				[
					'label'=>Yii::t('live', 'Children'),
					'value'=>'children',
				],
				[
					'label'=>Yii::t('live', 'New Comers'),
					'value'=>'newcomers',
				],
			]),
			'data-user'=>json_encode([
				'isGuest'=>Yii::app()->user->isGuest,
				'isOrganizer'=>!Yii::app()->user->isGuest && $this->user->isOrganizer() && isset($competition->organizers[$this->user->id]),
				'isDelegate'=>!Yii::app()->user->isGuest && $this->user->isDelegate() && isset($competition->delegates[$this->user->id]),
				'isAdmin'=>Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR),
				'name'=>Yii::app()->user->isGuest ? '' : $this->user->getCompetitionName(),
			]),
			'data-static-messages'=>[],
			'v-cloak'=>true,
		];
		if ($competition->live_stream_url) {
			$htmlOptions['data-static-messages'][] = [
				'id'=>'static-live-stream',
				'type'=>'static',
				'user'=>[
					'name'=>'System'
				],
				'time'=>time(),
				'content'=>Yii::t('live', 'Live stream can be found here: {link}', [
					'{link}'=>CHtml::link($competition->live_stream_url, $competition->live_stream_url, ['target'=>'_blank']),
				]),
			];
		}
		$htmlOptions['data-static-messages'] = json_encode($htmlOptions['data-static-messages']);
		$this->render('competition', array(
			'competition'=>$competition,
			'htmlOptions'=>$htmlOptions,
		));
	}

	public function actionUserResults() {
		$user = $this->aRequest('user');
		$number = $user['number'] ?? 0;
		$wcaid = $user['wcaid'] ?? '';
		$competition = $this->getCompetition();
		if ($competition === null) {
			$this->ajaxError(404, 'Unknown ID');
		}

		$results = LiveResult::model()->findAllByAttributes(array(
			'competition_id'=>$competition->id,
			'number'=>$number,
		));
		usort($results, function($resA, $resB) {
			if ($resA->wcaEvent === null) {
				return 1;
			}
			if ($resB->wcaEvent === null) {
				return -1;
			}
			$temp = $resA->wcaEvent->rank - $resB->wcaEvent->rank;
			if ($temp == 0) {
				$temp = $resA->wcaRound->rank - $resB->wcaRound->rank;
			}
			return $temp;
		});
		$temp = array();
		foreach ($results as $result) {
			if ($result->best == 0) {
				continue;
			}
			if (!isset($temp[$result->event])) {
				$temp[$result->event] = array(
					'event'=>$result->event,
					'results'=>array(),
				);
			}
			$temp[$result->event]['results'][] = $result->getShowAttributes(true);
		}
		$events = array();
		if ($wcaid != '') {
			$personResults = Persons::getResults($wcaid);
			foreach ($personResults['personRanks'] as $rank) {
				$events[$rank->eventId] = $rank->eventId;
				if (!isset($temp[$rank->eventId])) {
					continue;
				}
				$best = $rank->best;
				$average = $rank->average == null ? PHP_INT_MAX : $rank->average->best;
				foreach ($temp[$rank->eventId]['results'] as $key=>$result) {
					if ($result['b'] > 0 && $result['b'] <= $best) {
						$temp[$rank->eventId]['results'][$key]['nb'] = true;
						$best = $result['b'];
					}
					if ($result['a'] > 0 && $result['a'] <= $average) {
						$temp[$rank->eventId]['results'][$key]['na'] = true;
						$average = $result['a'];
					}
				}
			}
		}
		foreach ($temp as $event=>$results) {
			//event didn't attend before
			if (!isset($events[$event])) {
				$best = $average = PHP_INT_MAX;
				foreach ($results['results'] as $key=>$result) {
					if ($result['b'] > 0 && $result['b'] <= $best) {
						$results['results'][$key]['nb'] = true;
						$best = $result['b'];
					}
					if ($result['a'] > 0 && $result['a'] <= $average) {
						$results['results'][$key]['na'] = true;
						$average = $result['a'];
					}
				}
			}
			$temp[$event]['results'] = array_reverse($results['results']);
		}
		$userResults = array();
		foreach ($temp as $event=>$results) {
			$userResults[] = array(
				't'=>'e',
				'e'=>$event,
			);
			foreach ($results['results'] as $key=>$result) {
				$result['t'] = 'r';
				$userResults[] = $result;
			}
		}
		$this->ajaxOk($userResults);
	}

	public function actionPodiums() {
		$competition = $this->getCompetition();
		$data = $competition->getLivePodiums();
		$data['competition'] = $competition;
		$this->render('podiums', $data);
	}

	public function actionStatistics() {
		$competition = $this->getCompetition();
		$type = $this->sGet('type');
		$names = array_map('ucfirst', explode('-', $type));
		$class = implode('', $names);
		if ($class !== '') {
			if (method_exists($this, $method = 'stat' . $class)) {
				$this->$method($competition);
				Yii::app()->end();
			} else {
				throw new CHttpException(404);
			}
		}
		$this->redirect($competition->getUrl('live'));
	}

	private function statSumOfRanks($competition) {
		$type = $this->sGet('type', 'single');
		$gender = $this->sGet('gender', 'all');
		$eventIds = $this->aGet('event');
		if (!in_array($type, Results::getRankingTypes())) {
			$type = 'single';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		$events = $competition->associatedEvents;
		if (array_intersect($eventIds, array_keys($events)) === array()) {
			$eventIds = array_keys($events);
		}
		$name = $competition->getAttributeValue('name');
		$this->title = implode(' - ', array(
			$name,
			Yii::t('common', 'Live'),
			Yii::t('statistics', 'Sum of Ranks'),
		));
		$this->pageTitle = array($this->title);
		$this->breadcrumbs = array(
			'Competitions'=>array('/competition/index'),
			$name=>$competition->getUrl(),
			'Live'=>$competition->getUrl('live'),
			'Sum of Ranks',
		);
		$liveResults = LiveResult::model()->with('eventRound')->findAllByAttributes(array(
			'competition_id'=>$competition->id,
		), array(
			'condition'=>'t.best != 0',
		));
		$liveResults = array_filter($liveResults, function($result) use($eventIds) {
			$eventRound = $result->eventRound;
			$eventRound->wcaEvent;
			$eventRound->wcaRound;
			return in_array($result->event, $eventIds);
		});
		usort($liveResults, function($resA, $resB) {
			$eventA = $resA->eventRound->wcaEvent;
			$eventB = $resB->eventRound->wcaEvent;
			if ($eventA && $eventB) {
				$temp = $eventA->rank - $eventB->rank;
			} elseif ($eventA && !$eventB) {
				$temp = -1;
			} elseif (!$eventA && $eventB) {
				$temp = 1;
			} else {
				$temp = 0;
			}
			if ($temp == 0) {
				$temp = $resA->eventRound->wcaRound->rank - $resB->eventRound->wcaRound->rank;
			}
			return $temp;
		});
		$groupedResults = array();
		foreach ($liveResults as $result) {
			//touch relations
			$result->eventRound->wcaEvent;
			$result->eventRound->wcaRound;
			$groupedResults[$result->event][$result->round][] = $result;
		}
		$penalty = array();
		foreach ($groupedResults as $event=>$eventResults) {
			foreach ($eventResults as $results) {
				usort($results, array($this, 'compareResult'));
				$this->calculatePos($results);
				if (!isset($penalty[$event])) {
					$penalty[$event] = count($results) + 1;
				}
			}
		}
		$columns = array(
			array(
				'header'=>'Yii::t("statistics", "Person")',
				'value'=>'$data["user"]->getWcaLink()',
				'type'=>'raw',
			),
			array(
				'header'=>'Yii::t("common", "Region")',
				'value'=>'Yii::t("Region", $data["user"]->country->getAttributeValue("name"))',
				'type'=>'raw',
				'htmlOptions'=>array('class'=>'region'),
			),
			array(
				'header'=>'Yii::t("statistics", "Sum")',
				'value'=>'CHtml::tag("b", array(), $data["sum"])',
				'type'=>'raw',
			),
		);
		//计算每个人的排名
		$rankSum = array();
		foreach ($eventIds as $event) {
			if (!isset($groupedResults[$event])) {
				continue;
			}
			foreach ($groupedResults[$event] as $results) {
				foreach ($results as $result) {
					$personId = $result->user_type . '_' . $result->user_id;
					if(!isset($rankSum[$personId])) {
						$rankSum[$personId] = array(
							'user'=>$result->user,
						);
					}
					$rankSum[$personId][$event] = $result->pos;
				}
			}
			$columns[] = array(
				'header'=>"Events::getFullEventNameWithIcon('$event')",
				'name'=>$event,
				'type'=>'raw',
			);
		}
		foreach ($rankSum as $personId=>$ranks) {
			$ranks['sum'] = 0;
			foreach ($eventIds as $event) {
				if (isset($ranks[$event])) {
					$ranks['sum'] += $ranks[$event];
				} elseif (isset($penalty[$event])) {
					$ranks[$event] = CHtml::tag('span', array('class'=>'penalty'), $penalty[$event]);
					$ranks['sum'] += $penalty[$event];
				}
				$ranks['hasPenalty'] = !isset($ranks[$event]);
			}
			$rankSum[$personId] = $ranks;
		}
		uasort($rankSum, function($rankA, $rankB) {
			return $rankA['sum'] - $rankB['sum'];
		});
		if ($gender != 'all') {
			switch ($gender) {
				case 'male':
					$rankSum = array_filter($rankSum, function($rank) {
						return $rank['user']->gender == User::GENDER_MALE;
					});
					break;
				case 'female':
					$rankSum = array_filter($rankSum, function($rank) {
						return $rank['user']->gender == User::GENDER_FEMALE;
					});
					break;
			}
		}
		$statistic = array(
			'count'=>count($rankSum),
			'rows'=>array_values($rankSum),
			'columns'=>$columns,
			'rankKey'=>'sum',
		);
		$this->render('stat/sumOfRanks', array(
			'statistic'=>$statistic,
			'competition'=>$competition,
			'events'=>$events,
			'type'=>$type,
			'gender'=>$gender,
			'eventIds'=>$eventIds,
		));
	}

	private function calculatePos($results) {
		$count = count($results);
		for ($i = 0; $i < $count; $i++) {
			if (!isset($results[$i - 1]) || $this->compareResult($results[$i - 1], $results[$i]) < 0) {
				$results[$i]->pos = $i + 1;
			} else {
				$results[$i]->pos = $results[$i - 1]->pos;
			}
		}
	}

	private function compareResult($resA, $resB) {
		$round = $resA->eventRound;
		$temp = 0;
		if ($round->format == 'm' || $round->format == 'a') {
			if ($resA->average > 0 && $resB->average <= 0) {
				return -1;
			}
			if ($resB->average > 0 && $resA->average <= 0) {
				return 1;
			}
			$temp = $resA->average - $resB->average;
		}
		if ($temp == 0) {
			if ($resA->best > 0 && $resB->best <= 0) {
				return -1;
			}
			if ($resB->best > 0 && $resA->best <= 0) {
				return 1;
			}
			$temp = $resA->best - $resB->best;
		}
		return $temp;
	}
}
