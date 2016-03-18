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
		$results = LiveResult::model()->findAllByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->params->event}",
			'round'=>"{$this->msg->params->round}",
		));
		$this->success('results', array_map(function($result) {
			return $result->getShowAttributes();
		}, $results));
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
