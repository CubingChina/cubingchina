<?php

class ResultsController extends Controller {
	protected $logAction = false;

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionRanking() {
		$this->render('ranking');
	}

	public function actionRecords() {
		$this->render('records');
	}

	public function actionStatistics() {
		$statistics = Results::getStatistics();
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			ucfirst($this->action->id),
		);
		$this->render('statistics', array(
			'statistics' => $statistics,
		));
	}
}