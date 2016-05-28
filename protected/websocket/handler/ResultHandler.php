<?php

class ResultHandler extends MsgHandler {

	public function process() {
		$action = $this->getAction();
		if ($action !== '') {
			$method = 'action' . ucfirst($action);
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}
	}

	public function actionFetch() {
		$round = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->params->event}",
			'round'=>"{$this->msg->params->round}",
		));
		$results = LiveResult::model()->findAllByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->params->event}",
			'round'=>"{$this->msg->params->round}",
		));
		if ($round !== null && $round->isClosed) {
			$results = array_values(array_filter($results, function($result) use ($round) {
				if ($result->best == 0) {
					return false;
				}
				return true;
			}));
		}
		$this->success('result.all', array_map(function($result) {
			return $result->getShowAttributes();
		}, $results));
	}

	public function actionUpdate() {
		$data = $this->msg->result;
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
		$result->regional_single_record = $result->caculateRecord('single');
		$result->regional_average_record = $result->caculateRecord('average');
		$result->save();
		$this->broadcastSuccess('result.update', $result->getShowAttributes());
	}

	public function actionResult() {
	}

	public function actionAttribute() {  

	}

	public function actionRound() {

	}

	public function actionEvent() {

	}

	public function actionPerson() {

	}

	private function getAction() {
		if (isset($this->msg->action)) {
			return $this->msg->action;
		}
		return '';
	}
}
