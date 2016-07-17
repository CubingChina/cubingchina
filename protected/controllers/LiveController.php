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
		$version = Yii::app()->params->jsVer;
		$clientScript = Yii::app()->clientScript;
		$clientScript->registerScriptFile('/f/js/websocket' . $min . '.js');
		$clientScript->registerScriptFile('/f/js/store.min.js');
		$clientScript->registerScriptFile('/f/plugins/vue/vue' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/vue-router/vue-router' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/vuex/vuex' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/moment/moment' . $min . '.js');
		$clientScript->registerScriptFile('/f/js/live' . $min . '.js?ver=' . $version);
		$this->render('competition', array(
			'competition'=>$competition,
		));
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
		$events = $competition->events;
		$events = array_filter($events, function($event) {
			return $event['round'] > 0;
		});
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
			ucfirst($this->action->id),
			'Sum of Ranks',
		);
		$liveResults = LiveResult::model()->with('eventRound')->findAllByAttributes(array(
			'competition_id'=>$competition->id,
		), array(
			'condition'=>'t.best != 0',
		));
		$liveResults = array_filter($liveResults, function($result) use($eventIds) {
			return in_array($result->event, $eventIds);
		});
		usort($liveResults, function($resA, $resB) {
			$temp = $resA->eventRound->wcaEvent->rank - $resB->eventRound->wcaEvent->rank;
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
				'header'=>"CHtml::tag('span', array(
					'class'=>'event-icon event-icon-white event-icon-$event'
				), '&nbsp;')",
				'name'=>$event,
				'type'=>'raw',
			);
		}
		foreach ($rankSum as $personId=>$ranks) {
			$ranks['sum'] = 0;
			foreach ($eventIds as $event) {
				if (isset($ranks[$event])) {
					$ranks['sum'] += $ranks[$event];
				} else {
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
