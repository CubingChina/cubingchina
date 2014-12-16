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

	protected function beforeAction($action) {
		if (parent::beforeAction($action)) {
			$this->breadcrumbs = array(
				'Results'=>array('/results/index'),
				ucfirst($this->action->id),
			);
			return true;
		}
	}

	public function actionIndex() {
		
	}

	public function actionRanking() {
		$this->render('ranking');
	}

	public function actionRecords() {
		$this->render('records');
	}

	public function actionStatistics() {
		Yii::import('application.statistics.*');
		$data = Statistics::getData();
		extract($data);
		$this->pageTitle = array('Fun Statistics');
		$this->title = 'Fun Statistics';
		$this->description = Yii::t('statistics', 'Based on the official WCA competition results, we generated several WCA statistics about Chinese competitions and competitors, which were regularly up-to-date.');
		$this->setWeiboShareDefaultText('关于中国WCA官方比赛及选手成绩的一系列趣味统计', false);
		$this->render('statistics', array(
			'statistics' => $statistics,
			'time' => $time,
		));
	}
}