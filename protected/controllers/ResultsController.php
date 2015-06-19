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
		Yii::import('application.statistics.*');
		if (parent::beforeAction($action)) {
			$this->breadcrumbs = array(
				'Results'=>array('/results/index'),
				ucfirst($this->action->id),
			);
			return true;
		}
	}

	public function actionIndex() {
		$this->breadcrumbs = array(
			'Results',
		);
		$this->title = 'Results';
		$this->pageTitle = array('Results');
		$this->description = Yii::t('statistics', 'Welcome to the Cubing China results page, where you can find the Chinese personal rankings, official records, and fun statistics.');
		$this->setWeiboShareDefaultText('粗饼的官方成绩页面，包含中国魔方选手的个人排名、官方纪录与趣味统计等信息。');
		$this->render('index');
	}

	public function actionRankings() {
		$region = $this->sGet('region', 'China');
		$type = $this->sGet('type', 'single');
		$event = $this->sGet('event', '333');
		$gender = $this->sGet('gender', 'all');
		$page = $this->iGet('page', 1);
		if (!in_array($type, Results::getRankingTypes())) {
			$type = 'single';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (!array_key_exists($event, Events::getNormalEvents())) {
			$event = '333';
		}
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		if ($page < 1) {
			$page = 1;
		}
		$rankings = Results::getRankings($region, $type, $event, $gender, $page);
		if ($page > ceil($rankings['count'] / 100)) {
			$page = ceil($rankings['count'] / 100);
		}
		$this->title = 'Personal Rankings';
		$this->pageTitle = array(
			'Personal Rankings',
			Yii::t('Region', $region),
			Yii::t('event', Events::getFullEventName($event)),
			ucfirst($gender),
			ucfirst($type),
		);
		$this->description = Yii::t('statistics', 'Global personal rankings in each official event are listed, based on the the official WCA rankings.');
		$this->setWeiboShareDefaultText('各国魔方选手在各官方项目的个人成绩排名展示');
		$this->render('rankings', array(
			'rankings'=>$rankings,
			'region'=>$region,
			'type'=>$type,
			'event'=>$event,
			'gender'=>$gender,
			'page'=>$page,
		));
	}

	public function actionRecords() {
		$type = $this->sGet('type', 'current');
		$region = $this->sGet('region', 'China');
		$event = $this->sGet('event', '333');
		if (!in_array($type, array('current', 'history'))) {
			$type = 'current';
		}
		if (!array_key_exists($event, Events::getNormalEvents())) {
			$event = '333';
		}
		if ($type !== 'history') {
			$event = '';
		}
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		$records = Results::getRecords($type, $region, $event);
		$this->title = 'Official Records';
		$pageTitle = array(
			'Official Records',
			Yii::t('Region', $region),
		);
		if ($type === 'history') {
			$pageTitle[] = Yii::t('event', Events::getFullEventName($event));
		}
		$pageTitle[] = Yii::t('Results', ucfirst($type));
		$this->pageTitle = $pageTitle;
		$this->description = Yii::t('statistics', 'Regional records are displayed on the page, based on the official WCA records.');
		$this->setWeiboShareDefaultText('世界魔方协会（WCA）所有官方项目的纪录展示');
		$this->render('records', array(
			'records'=>$records,
			'type'=>$type,
			'region'=>$region,
			'event'=>$event,
		));
	}

	public function actionPerson() {

	}

	public function actionP() {
		$id = $this->sGet('id');
		$person = Persons::model()->findByAttributes(array('id' => $id));
		if ($person == null) {
			$this->redirect(array('/results/persons'));
		}
		$data = Yii::app()->cache->getData(array('Persons', 'getResults'), $id);
		$data['person'] = $person;
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Persons'=>array('/results/person'),
			$person->name,
		);
		$this->pageTitle = array($person->name);
		$this->title = $person->name;
		// $this->setWeiboShareDefaultText('关于中国WCA官方比赛及选手成绩的一系列趣味统计', false);
		$this->render('p', $data);
	}

	public function actionStatistics() {
		$name = $this->sGet('name');
		$names = array_map('ucfirst', explode('-', $name));
		$class = implode('', $names);
		$this->description = Yii::t('statistics', 'Based on the official WCA competition results, we generated several WCA statistics about Chinese competitions and competitors, which were regularly up-to-date.');
		if ($class !== '') {
			if (method_exists($this, $method = 'stat' . $class)) {
				$this->$method();
				Yii::app()->end();
			} else {
				throw new CHttpException(404);
			}
		}
		$data = Statistics::getData();
		extract($data);
		$this->pageTitle = array('Fun Statistics');
		$this->title = 'Fun Statistics';
		$this->setWeiboShareDefaultText('关于中国WCA官方比赛及选手成绩的一系列趣味统计', false);
		$this->render('statistics', array(
			'statistics'=>$statistics,
			'time'=>$time,
		));
	}

	private function statSumOfRanks() {
		$page = $this->iGet('page', 1);
		$type = $this->sGet('type', 'single');
		$gender = $this->sGet('gender', 'all');
		$eventIds = $this->aGet('event');
		if (!in_array($type, Results::getRankingTypes())) {
			$type = 'single';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (array_intersect($eventIds, array_keys(Events::getNormalEvents())) === array()) {
			$eventIds = array();
		}
		$statistic = array(
			'class'=>'SumOfRanks',
			'type'=>$type,
			'eventIds'=>$eventIds,
			'gender'=>$gender,
		);
		if ($page < 1) {
			$page = 1;
		}
		$this->title = Yii::t('statistics', 'Sum of Ranks');
		$this->pageTitle = array('Fun Statistics', $this->title);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Statistics'=>array('/results/statistics'),
			$this->title,
		);
		$data = Statistics::buildRankings($statistic, $page);
		extract($data);
		if ($page > ceil($statistic['count'] / Statistics::$limit)) {
			$page = ceil($statistic['count'] / Statistics::$limit);
		}
		$this->render('stat/sumOfRanks', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'type'=>$type,
			'gender'=>$gender,
			'eventIds'=>$eventIds,
		));
	}

	private function statSumOfCountryRanks() {
		$page = $this->iGet('page', 1);
		$type = $this->sGet('type', 'single');
		$gender = $this->sGet('gender', 'all');
		$eventIds = $this->aGet('event');
		if (!in_array($type, Results::getRankingTypes())) {
			$type = 'single';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (array_intersect($eventIds, array_keys(Events::getNormalEvents())) === array()) {
			$eventIds = array();
		}
		$statistic = array(
			'class'=>'SumOfCountryRanks',
			'type'=>$type,
			'eventIds'=>$eventIds,
			'gender'=>$gender,
		);
		if ($page < 1) {
			$page = 1;
		}
		$this->title = Yii::t('statistics', 'Sum of Country Ranks');
		$this->pageTitle = array('Fun Statistics', $this->title);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Statistics'=>array('/results/statistics'),
			$this->title,
		);
		$data = Statistics::buildRankings($statistic, $page, 200);
		extract($data);
		if ($page > ceil($statistic['count'] / Statistics::$limit)) {
			$page = ceil($statistic['count'] / Statistics::$limit);
		}
		$this->render('stat/sumOfCountryRanks', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'type'=>$type,
			'gender'=>$gender,
			'eventIds'=>$eventIds,
		));
	}

	private function statBestPodiums() {
		$page = $this->iGet('page', 1);
		$eventId = $this->sGet('event');
		if (!in_array($eventId, array_keys(Events::getNormalEvents()))) {
			$eventId = '333';
		}
		$statistic = array(
			'class'=>'BestPodiums',
			'type'=>'single',
			'eventId'=>$eventId,
		);
		if ($page < 1) {
			$page = 1;
		}
		$this->title = Yii::t('statistics', 'Best Podiums');
		$this->pageTitle = array('Fun Statistics', $this->title);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Statistics'=>array('/results/statistics'),
			$this->title,
		);
		$data = Statistics::buildRankings($statistic, $page, 200);
		extract($data);
		if ($page > ceil($statistic['count'] / Statistics::$limit)) {
			$page = ceil($statistic['count'] / Statistics::$limit);
		}
		$this->render('stat/bestPodiums', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'event'=>$eventId,
		));
	}

	private function statMostSolves() {
		$page = $this->iGet('page', 1);
		$gender = $this->sGet('gender', 'all');
		$eventIds = $this->aGet('event');
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (array_intersect($eventIds, array_keys(Events::getNormalEvents())) === array()) {
			$eventIds = array();
		}
		$statistic = array(
			'class'=>'MostSolves',
			'type'=>'all',
			'eventIds'=>$eventIds,
			'gender'=>$gender,
		);
		if ($page < 1) {
			$page = 1;
		}
		$this->title = Yii::t('statistics', 'Most Personal Solves');
		$this->pageTitle = array('Fun Statistics', $this->title);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Statistics'=>array('/results/statistics'),
			$this->title,
		);
		$data = Statistics::buildRankings($statistic, $page);
		extract($data);
		if ($page > ceil($statistic['count'] / Statistics::$limit)) {
			$page = ceil($statistic['count'] / Statistics::$limit);
		}
		$this->render('stat/mostSolves', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'gender'=>$gender,
			'eventIds'=>$eventIds,
		));
	}

	private function statMedalCollection() {
		$page = $this->iGet('page', 1);
		$eventIds = $this->aGet('event');
		if (array_intersect($eventIds, array_keys(Events::getNormalEvents())) === array()) {
			$eventIds = array();
		}
		$statistic = array(
			'class'=>'MedalCollection',
			'type'=>'all',
			'eventIds'=>$eventIds,
		);
		if ($page < 1) {
			$page = 1;
		}
		$this->title = Yii::t('statistics', 'Medal Collection');
		$this->pageTitle = array('Fun Statistics', $this->title);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Statistics'=>array('/results/statistics'),
			$this->title,
		);
		$data = Statistics::buildRankings($statistic, $page);
		extract($data);
		if ($page > ceil($statistic['count'] / Statistics::$limit)) {
			$page = ceil($statistic['count'] / Statistics::$limit);
		}
		$this->render('stat/medalCollection', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'eventIds'=>$eventIds,
		));
	}
}