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
			//check if it has last round
			if (($lastRound = $round->lastRound) !== null) {
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
