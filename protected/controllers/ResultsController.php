<?php

class ResultsController extends Controller {
	public function actionRanking() {
		$this->render('ranking');
	}

	public function actionRecords() {
		$this->render('records');
	}

	public function actionStatistics() {
		$this->render('statistics');
	}
}