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
		$region = $this->sGet('region', 'China');
		$gender = $this->sGet('gender', 'all');
		$name = $this->sGet('name', '');
		$page = $this->iGet('page', 1);
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		if ($page < 1) {
			$page = 1;
		}
		$persons = Yii::app()->cache->getData('Persons::getPersons', array($region, $gender, $name, $page));
		if ($page > ceil($persons['count'] / 100)) {
			$page = ceil($persons['count'] / 100);
		}
		$this->title = 'Persons';
		$this->pageTitle = array(
			'Persons',
		);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Persons',
		);
		$this->render('person', array(
			'persons'=>$persons,
			'region'=>$region,
			'gender'=>$gender,
			'name'=>$name,
			'page'=>$page,
		));
	}

	public function actionP() {
		$id = $this->sGet('id');
		$person = Persons::model()->findByAttributes(array('id' => $id));
		if ($person == null) {
			$this->redirect(array('/results/person'));
		}
		$data = Yii::app()->cache->getData(array('Persons', 'getResults'), $id);
		$data['person'] = $person;
		$data['user'] = User::model()->findByAttributes(array(
			'wcaid'=>$person->id,
			'status'=>User::STATUS_NORMAL,
		));
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Persons'=>array('/results/person'),
			$person->name,
		);
		$this->pageTitle = array($person->name, 'Personal Page');
		$this->title = Yii::t('common', 'Personal Page');
		$this->setWeiboShareDefaultText($person->name . '选手的魔方速拧成绩页 - 粗饼·中国魔方赛事网', false);
		$this->render('p', $data);
	}

	public function actionPk() {
		$ids = array_unique(array_map('strtoupper', $this->aGet('ids')));
		$ids = array_slice($ids, 0, 4);
		$persons = array();
		$names = array();
		foreach ($ids as $id) {
			$person = Persons::model()->findByAttributes(array('id' => $id));
			if ($person !== null) {
				$persons[] = array(
					'person'=>$person,
					'results'=>Yii::app()->cache->getData(array('Persons', 'getResults'), $id),
				);
				$names[] = $person->name;
			}
		}
		switch (count($persons)) {
			case 0:
				$this->redirect(array('/results/person'));
				break;
			case 1:
				$this->redirect(array('/results/p', 'id'=>$persons[0]['person']->id));
				break;
		}
		$persons = array_slice($persons, 0, 4);
		$data = $this->handlePKPersons($persons);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Persons'=>array('/results/person'),
			'PK',
		);
		$names[] = 'PK';
		$this->pageTitle = $names;
		$this->title = Yii::t('common', 'PK');
		$this->render('pk', $data);
	}

	private function handlePKPersons($persons) {
		//id
		$eventIds = array();
		$winners = array();
		$bestData = array(
			'competitions'=>array(
				'expression'=>'count($results["competitions"])',
				'type'=>'max',
			),
			'emulation'=>array(
				'expression'=>'strtotime(sprintf("%d-%02d-%02d",
					$results["lastCompetition"]->year,
					$results["lastCompetition"]->month,
					$results["lastCompetition"]->day
				)) - strtotime(sprintf("%d-%02d-%02d",
					$results["firstCompetition"]->year,
					$results["firstCompetition"]->month,
					$results["firstCompetition"]->day
				))',
				'type'=>'max',
				'canBeZero'=>true,
			),
			'records'=>array(
				'expression'=>'$results["overAll"]["WR"] * 10 + $results["overAll"]["CR"] * 5 + $results["overAll"]["NR"]',
				'type'=>'max',
			),
			'medals'=>array(
				'expression'=>'$results["overAll"]["gold"] * 1e8 + $results["overAll"]["silver"] * 1e4 + $results["overAll"]["bronze"]',
				'type'=>'max',
			),
		);
		foreach ($bestData as $key=>$value) {
			$bestData[$key]['value'] = $this->getBestData($persons, $value['expression'], $value['type'], isset($value['canBeZero']) ? $value['canBeZero'] : false);
		}
		$countries = $continents = array();
		foreach ($persons as $person) {
			$id = $person['person']->id;
			$countries[$person['person']->countryId] = $person['person']->countryId;
			$continents[$person['person']->country->continentId] = $person['person']->country->continentId;
			foreach ($person['results']['personRanks'] as $eventId=>$ranks) {
				$eventIds[$eventId] = $eventId;
			}
			foreach ($bestData as $key=>$value) {
				if ($this->evaluateExpression($value['expression'], $person) === $value['value']) {
					$winners[$id][$key] = true;
				}
			}
		}
		foreach ($eventIds as $eventId) {
			$singleExpression = "isset(\$results['personRanks']['{$eventId}']) ? \$results['personRanks']['{$eventId}']->best : -1";
			$averageExpression = "isset(\$results['personRanks']['{$eventId}']) && \$results['personRanks']['{$eventId}']->average !== null ? \$results['personRanks']['{$eventId}']->average->best : -1";
			$singleNRExpression = "isset(\$results['personRanks']['{$eventId}']) ? \$results['personRanks']['{$eventId}']->countryRank : -1";
			$averageNRExpression = "isset(\$results['personRanks']['{$eventId}']) && \$results['personRanks']['{$eventId}']->average !== null ? \$results['personRanks']['{$eventId}']->average->countryRank : -1";
			$singleCRExpression = "isset(\$results['personRanks']['{$eventId}']) ? \$results['personRanks']['{$eventId}']->continentRank : -1";
			$averageCRExpression = "isset(\$results['personRanks']['{$eventId}']) && \$results['personRanks']['{$eventId}']->average !== null ? \$results['personRanks']['{$eventId}']->average->continentRank : -1";
			$medalsExpression = "isset(\$results['personRanks']['{$eventId}']) ? \$results['personRanks']['{$eventId}']->medals['gold'] * 1e8 + \$results['personRanks']['{$eventId}']->medals['silver'] * 1e4 + \$results['personRanks']['{$eventId}']->medals['bronze'] : 0";
			$solvesExpression = "isset(\$results['personRanks']['{$eventId}']) ? \$results['personRanks']['{$eventId}']->medals['solve'] * 10000000 - \$results['personRanks']['{$eventId}']->medals['attempt'] : -1";
			$bestSingle = $this->getBestData($persons, $singleExpression);
			$bestAverage = $this->getBestData($persons, $averageExpression);
			$bestSingleNR = $this->getBestData($persons, $singleNRExpression);
			$bestAverageNR = $this->getBestData($persons, $averageNRExpression);
			$bestSingleCR = $this->getBestData($persons, $singleCRExpression);
			$bestAverageCR = $this->getBestData($persons, $averageCRExpression);
			$bestMedals = $this->getBestData($persons, $medalsExpression, 'max');
			$bestSolves = $this->getBestData($persons, $solvesExpression, 'max');
			foreach ($persons as $person) {
				$id = $person['person']->id;
				$single = $this->evaluateExpression($singleExpression, $person);
				$average = $this->evaluateExpression($averageExpression, $person);
				$singleNR = $this->evaluateExpression($singleNRExpression, $person);
				$averageNR = $this->evaluateExpression($averageNRExpression, $person);
				$singleCR = $this->evaluateExpression($singleCRExpression, $person);
				$averageCR = $this->evaluateExpression($averageCRExpression, $person);
				$medals = $this->evaluateExpression($medalsExpression, $person);
				$solves = $this->evaluateExpression($solvesExpression, $person, 'max');
				if ($single === $bestSingle) {
					$winners[$id][$eventId . 'Single'] = true;
					$winners[$id][$eventId . 'SingleWR'] = true;
				}
				if ($singleNR === $bestSingleNR) {
					$winners[$id][$eventId . 'SingleNR'] = true;
				}
				if ($singleCR === $bestSingleCR) {
					$winners[$id][$eventId . 'SingleCR'] = true;
				}
				if ($average === $bestAverage && $bestAverage > 0) {
					$winners[$id][$eventId . 'Average'] = true;
					$winners[$id][$eventId . 'AverageWR'] = true;
				}
				if ($averageNR === $bestAverageNR) {
					$winners[$id][$eventId . 'AverageNR'] = true;
				}
				if ($averageCR === $bestAverageCR) {
					$winners[$id][$eventId . 'AverageCR'] = true;
				}
				if ($medals === $bestMedals) {
					$winners[$id][$eventId . 'Medals'] = true;
				}
				if ($solves === $bestSolves) {
					$winners[$id][$eventId . 'Solves'] = true;
				}
			}
		}
		return array(
			'persons'=>$persons,
			'eventIds'=>$eventIds,
			'bestData'=>$bestData,
			'winners'=>$winners,
			'sameCountry'=>count($countries) === 1,
			'sameContinent'=>count($continents) === 1,
		);
	}

	private function getBestData($persons, $expression, $type = 'min', $canBeZero = false) {
		$temp = array();
		foreach ($persons as $person) {
			$value = $this->evaluateExpression($expression, $person);
			if ($value > 0 || $canBeZero) {
				$temp[] = $this->evaluateExpression($expression, $person);
			}
		}
		if ($temp === array()) {
			return -1;
		}
		$best = $type($temp);
		if ($best <= 0 && !$canBeZero) {
			return -1;
		}
		return $best;
	}

	protected function getWinnerCSSClass($winners, $person, $attribute) {
		if (isset($winners[$person['person']->id][$attribute])) {
			return ' class="winner"';
		}
		return '';
	}

	protected function getPersonRankValue($results, $eventId, $attribute) {
		if (!isset($results['personRanks'][$eventId])) {
			return '-';
		}
		$model = $results['personRanks'][$eventId];
		$attribute = explode('.', $attribute);
		if (isset($attribute[1])) {
			$model = $model->{$attribute[0]};
			$attribute = $attribute[1];
		} else {
			$attribute = $attribute[0];
		}
		if ($model === null) {
			return '-';
		}
		$value = $model[$attribute];
		if ($attribute === 'best') {
			$value = Results::formatTime($value, "$eventId");
		}
		if ($attribute === 'solve') {
			$value .= '/' . $model['attempt'];
		}
		return $value;
	}

	public function actionCompetition() {
		$model = new Competitions('search');
		$model->unsetAttributes();
		$model->year = $this->sGet('year', 'current');
		$model->region = $this->sGet('region', 'China');
		$model->event = $this->sGet('event');
		$model->name = $this->sGet('name', '');
		$this->title = Yii::t('Competitions', 'Competitions');
		$this->pageTitle = array(
			Yii::t('Competitions', 'Competitions'),
		);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			Yii::t('Competitions', 'Competitions'),
		);
		$this->render('competition', array(
			'model'=>$model,
		));
	}

	public function actionC() {
		$id = $this->sGet('id');
		$type = $this->sGet('type', 'winners');
		$competition = Competitions::model()->findByAttributes(array('id' => $id));
		if ($competition == null) {
			$this->redirect(array('/results/competition'));
		}
		if (!array_key_exists($type, Competitions::getResultsTypes())) {
			$type = 'winners';
		}
		if (($c = Competition::model()->findByAttributes(array('wca_competition_id' => $id))) !== null) {
			$competition->name = $c->getAttributeValue('name');
			$competition->location = $c->isMultiLocation() ? $c->getLocationInfo('venue') : $c->location[0]->getFullAddress(false);
			$competition->c = $c;
		}
		$data = Yii::app()->cache->getData(array('Competitions', 'getResults'), $id);
		$data['competition'] = $competition;
		$data['type'] = $type;
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Competitions'=>array('/results/competition'),
			$competition->name,
		);
		$this->pageTitle = array($competition->name, 'Competition Results');
		$this->title = $competition->name;
		// $this->setWeiboShareDefaultText($competition->name . '选手的魔方速拧成绩页 - 粗饼·中国魔方赛事网', false);
		$this->render('c', $data);
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
		$region = $this->sGet('region', 'China');
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
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
			'region'=>$region,
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
			'region'=>$region,
		));
	}

	private function statTop100() {
		$region = $this->sGet('region', 'China');
		$type = $this->sGet('type', 'single');
		$event = $this->sGet('event', '333');
		$gender = $this->sGet('gender', 'all');
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
		$statistic = array(
			'class'=>'Top100',
			'type'=>$type,
			'event'=>$event,
			'gender'=>$gender,
			'region'=>$region,
		);
		$this->title = Yii::t('statistics', 'Chinese Top 100 Results');
		$this->pageTitle = array('Fun Statistics', $this->title);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Statistics'=>array('/results/statistics'),
			$this->title,
		);
		$data = Statistics::buildRankings($statistic);
		extract($data);
		$this->render('stat/top100', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'region'=>$region,
			'gender'=>$gender,
			'event'=>$event,
			'type'=>$type,
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