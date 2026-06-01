<?php

class ResultHandler extends MsgHandler {

	private static $whiteListActions = ['fetch', 'user', 'record', 'roundtypes'];

	public function process() {
		if ($this->competition == null) {
			return;
		}
		$action = $this->getAction();
		if ($action !== '') {
			if (!in_array($action, self::$whiteListActions) && !$this->checkAccess()) {
				return;
			}
			$method = 'action' . ucfirst($action);
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}
	}

	public function actionRecord() {
		$records = LiveResult::model()->with('user')->findAllByAttributes([
			'competition_id'=>$this->competition->id,
		], [
			'condition'=>'regional_single_record!="" OR regional_average_record!=""',
		]);
		$this->success('record.all', array_map(function($record) {
			return $record->getShowAttributes();
		}, $records));
	}

	public function actionFetch() {
		$round = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->params->event}",
			'round'=>"{$this->msg->params->round}",
		));
		$combine = isset($this->msg->params->combine) && $this->msg->params->combine;
		if ($combine && $round !== null && $this->isDualRound($round)) {
			return $this->fetchCombined($round);
		}
		$results = LiveResult::model()->with('user')->findAllByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->params->event}",
			'round'=>"{$this->msg->params->round}",
		));
		if ($round !== null && $round->isClosed) {
			$results = array_filter($results, function($result) use ($round) {
				if ($result->best == 0) {
					return false;
				}
				return true;
			});
		}
		switch ($this->msg->params->filter) {
			case 'females':
				$results = array_filter($results, function($result) {
					return $result->user->gender == User::GENDER_FEMALE;
				});
				break;
			case 'children':
				$birthday = $this->competition->date - (365 * 12 + 3) * 86400;
				$results = array_filter($results, function($result) use($birthday) {
					return $result->user->birthday >= $birthday;
				});
				break;
			case 'newcomers':
				$newcomer = $this->competition->newcomer;
				$currentYear = date('Y', $this->competition->date);
				$results = array_filter($results, function($result) use($newcomer, $currentYear) {
					return $result->user->wcaid == '' || ($newcomer && substr($result->user->wcaid, 0, 4) == $currentYear);
				});
		}
		$this->success('result.all', array_map(function($result) {
			return $result->getShowAttributes();
		}, array_values($results)), $this->competition);
	}

	private function isDualRound($round) {
		$dualRounds = $round->dualRounds;
		if ($dualRounds === array()) {
			return false;
		}
		foreach ($dualRounds as $dualRound) {
			if ($dualRound->id == $round->id) {
				return true;
			}
		}
		return false;
	}

	private function fetchCombined($round) {
		$dualRounds = $round->dualRounds;
		$round1 = $dualRounds[0];
		$round2 = $dualRounds[1];
		$rows = LiveEventRound::getCombinedRanking($round1, $round2);
		$bothClosed = $round1->isClosed && $round2->isClosed;
		$filter = $this->msg->params->filter;
		$competition = $this->competition;
		$birthday = $competition->date - (365 * 12 + 3) * 86400;
		$newcomer = $competition->newcomer;
		$currentYear = date('Y', $competition->date);
		$results = array();
		foreach ($rows as $row) {
			$better = $row['better'];
			if ($bothClosed && $better->best == 0) {
				continue;
			}
			$user = $better->user;
			switch ($filter) {
				case 'females':
					if ($user->gender != User::GENDER_FEMALE) {
						continue 2;
					}
					break;
				case 'children':
					if ($user->birthday < $birthday) {
						continue 2;
					}
					break;
				case 'newcomers':
					if (!($user->wcaid == '' || ($newcomer && substr($user->wcaid, 0, 4) == $currentYear))) {
						continue 2;
					}
					break;
			}
			$results[] = array(
				'n'=>intval($row['number']),
				'e'=>$round->event,
				'r'=>$round->round,
				'f'=>$round->format,
				'b'=>intval($better->best),
				'a'=>intval($better->average),
				'v'=>$this->dualValues($better),
				'sr'=>$better->regional_single_record,
				'ar'=>$better->regional_average_record,
				'dual'=>true,
				'rr'=>$row['betterRound'],
				'r1id'=>$round1->round,
				'r2id'=>$round2->round,
				'd1'=>$this->dualSubResult($row['r1']),
				'd2'=>$this->dualSubResult($row['r2']),
			);
		}
		$this->success('result.all', $results, $this->competition);
	}

	private function dualValues($result) {
		return array(
			intval($result->value1),
			intval($result->value2),
			intval($result->value3),
			intval($result->value4),
			intval($result->value5),
		);
	}

	private function dualSubResult($result) {
		if ($result === null) {
			return null;
		}
		return array(
			'i'=>$result->id,
			'r'=>$result->round,
			'b'=>intval($result->best),
			'a'=>intval($result->average),
			'v'=>$this->dualValues($result),
			'sr'=>$result->regional_single_record,
			'ar'=>$result->regional_average_record,
		);
	}

	public function actionUpdate() {
		$data = $this->msg->result;
		$competition = $this->competition;
		if (!isset($data->id)) {
			return;
		}
		$result = LiveResult::model()->findByPk($data->id);
		if ($result == null) {
			return;
		}
		$result->value1 = $data->value1;
		$result->value2 = $data->value2;
		$result->value3 = $data->value3;
		$result->value4 = $data->value4;
		$result->value5 = $data->value5;
		$result->best = $data->best;
		$result->average = $data->average;
		$result->regional_single_record = $data->regional_single_record;
		$result->regional_average_record = $data->regional_average_record;
		if ($result->best == 0) {
			$result->create_time = 0;
			$result->update_time = 0;
		} else {
			if ($result->create_time == 0) {
				$result->create_time = time();
			}
			$result->update_time = time();
		}
		$result->operator_id = $this->user->id;
		$result->save();
		$this->broadcastSuccess('result.update', $result->getShowAttributes(), $competition);
		$eventRound = $result->eventRound;
		$this->broadcastSuccessToDataTaker('round.update', $eventRound->getBroadcastAttributes(), $competition);
		if ($eventRound->status == LiveEventRound::STATUS_OPEN) {
			$eventRound->status = LiveEventRound::STATUS_LIVE;
			$eventRound->save();
			$this->broadcastSuccess('round.update', $eventRound->getBroadcastAttributes(), $competition);
		}
		$result->competition = $competition;
		if ($result->shouldComputeRecord()) {
			$this->addToQueue('record.compute', [
				'competitionId'=>$competition->id,
				'event'=>$result->event,
			]);
		}
	}

	public function actionUser() {
		$results = LiveResult::model()->findAllByAttributes(array(
			'competition_id'=>$this->competition->id,
			'number'=>$this->msg->user->number,
			// 'user_id'=>$this->msg->user->id,
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
		if ($this->msg->user->wcaid != '') {
			$personResults = Persons::getResults($this->msg->user->wcaid);
			foreach ($personResults['personRanks'] as $rank) {
				$events[$rank->event_id] = $rank->event_id;
				if (!isset($temp[$rank->event_id])) {
					continue;
				}
				$best = $rank->best;
				$average = $rank->average == null ? PHP_INT_MAX : $rank->average->best;
				foreach ($temp[$rank->event_id]['results'] as $key=>$result) {
					if ($result['b'] > 0 && $result['b'] <= $best) {
						$temp[$rank->event_id]['results'][$key]['nb'] = true;
						$best = $result['b'];
					}
					if ($result['a'] > 0 && $result['a'] <= $average) {
						$temp[$rank->event_id]['results'][$key]['na'] = true;
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
		$this->success('result.user', $userResults);
	}

	public function actionAttribute() {

	}

	public function actionRoundTypes() {
		$this->success('round.all', array_map(function($round) {
			return $round->getBroadcastAttributes();
		}, LiveEventRound::model()->findAllByAttributes(array(
			'competition_id'=>$this->competition->id,
		))), $this->competition);
	}

	public function actionRound() {
		$round = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->round->event}",
			'round'=>"{$this->msg->round->id}",
		));
		if ($round != null) {
			$hasPermission = !$this->competition->isWCACompetition() || $this->user->isDelegate() || $this->user->isAdministrator();
			foreach (array('number', 'cut_off', 'time_limit', 'format') as $attribute) {
				if (isset($this->msg->round->$attribute) && $hasPermission) {
					$round->$attribute = $this->msg->round->$attribute;
				}
			}
			if (isset($this->msg->round->status)) {
				$round->status = $this->msg->round->status;
			}
			$round->save();
			$this->broadcastSuccess('round.update', $round->getBroadcastAttributes(), $this->competition);
		}
	}

	public function actionRefresh() {
		$round = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->round->event}",
			'round'=>"{$this->msg->round->id}",
		));
		if ($round != null) {
			$competition = $this->competition;
			$oldResults = [];
			$advancedNumbers = [];
			foreach ($round->getAllResults() as $result) {
				$oldResults[$result->number] = $result;
			}
			$results = array();
			$dualRounds = $round->dualRounds;
			$roundIndex = $round->roundIndex;
			if ($dualRounds !== array() && $roundIndex === 1) {
				//second of the Dual Rounds: advance every competitor of the first
				//round, no eliminations regardless of the advancement number (Reg 9v5)
				foreach ($dualRounds[0]->allResults as $result) {
					$results[] = $this->addResult($result, $oldResults, $round);
					$advancedNumbers[] = $result->number;
				}
			} elseif ($dualRounds !== array() && $roundIndex === 2) {
				//round after the Dual Rounds: rank by the better of the two dual
				//rounds and take the top N (Reg 9v4)
				$ranking = LiveEventRound::getCombinedRanking($dualRounds[0], $dualRounds[1]);
				$ranking = array_values(array_filter($ranking, function($row) {
					return $row['better']->best > 0;
				}));
				foreach (array_slice($ranking, 0, $round->number) as $row) {
					$results[] = $this->addResult($row['better'], $oldResults, $round);
					$advancedNumbers[] = $row['number'];
				}
			} elseif (($lastRound = $round->lastRound) !== null) {
				//check if it has last round
				foreach (array_slice($lastRound->results, 0, $round->number) as $result) {
					$results[] = $this->addResult($result, $oldResults, $round);
					$advancedNumbers[] = $result->number;
				}
			} else {
				//empty results of first rounds
				$registrations = Registration::getRegistrations($competition);
				foreach ($registrations as $registration) {
					if (in_array($round->event, $registration->events)) {
						$results[] = $this->addResult($registration, $oldResults, $round);
						$advancedNumbers[] = $registration->number;
					}
				}
			}
			foreach ($round->getAllResults() as $result) {
				if (!in_array($result->number, $advancedNumbers)) {
					$result->delete();
				}
			}
			$this->success('result.all', array_map(function($result) {
				return $result->getShowAttributes();
			}, $results));
		}
	}

	private function addResult($result, $oldResults, $round) {
		if (!isset($oldResults[$result->number])) {
			$model = new LiveResult();
			$model->competition_id = $result->competition_id;
			$model->user_id = $result->user_id;
			$model->number = $result->number;
			$model->event = $round->event;
			$model->round = $round->round;
			$model->format = $round->format;
			$model->save();
			return $model;
		} else {
			$oldResults[$result->number]->user_id = $result->user_id;
			$oldResults[$result->number]->save();
			return $oldResults[$result->number];
		}
	}

	public function actionEvent() {

	}

	public function actionPerson() {

	}
}
