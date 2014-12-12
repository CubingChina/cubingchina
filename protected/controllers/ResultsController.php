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
		$cacheKey = $this->getCacheKey('data');
		$cache = Yii::app()->cache;
		if (($statistics = $cache->get($cacheKey)) === false) {
			Yii::import('application.statistics.*');
			$statistics = Statistics::getData();
			$cache->set($cacheKey, $statistics, 86400 * 7);
		}
		$this->breadcrumbs = array(
			// 'Results'=>array('/results/index'),
			'Results',
			ucfirst($this->action->id),
		);
		$this->pageTitle = array('Fun Statistics');
		$this->title = 'Fun Statistics';
		$this->description = Yii::t('statistics', 'Based on the official WCA competition results, we generated several WCA statistics about Chinese competitions and competitors, which were regularly up-to-date.');
		$this->setWeiboShareDefaultText('关于中国WCA官方比赛及选手成绩的一系列趣味统计', false);
		$this->render('statistics', array(
			'statistics' => $statistics,
		));
	}
}