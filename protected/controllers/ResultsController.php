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
			Events::getFullEventName($event),
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
			$pageTitle[] = Events::getFullEventName($event);
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
		$id = strtoupper($this->sGet('id'));
		$person = Persons::model()->with('country')->findByAttributes(array('wca_id' => $id, 'sub_id'=>1));
		if ($person == null) {
			$this->redirect(array('/results/person'));
		}
		$data = Yii::app()->cache->getData(array('Persons', 'getResults'), $id);
		$data['person'] = $person;
		$data['user'] = $user = User::model()->findByAttributes(array(
			'wcaid'=>$person->wca_id,
			'status'=>User::STATUS_NORMAL,
		));
		$data['organizedCompetitions'] = [];
		if ($user) {
			$data['organizedCompetitions'] = Competition::model()->with([
				'organizer'=>[
					'together'=>true,
					'condition'=>'organizer.organizer_id=' . $user->id,
				],
			])->findAllByAttributes([
				'status'=>Competition::STATUS_SHOW,
			], [
				'order'=>'date DESC, end_date DESC',
			]);
		}
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Persons'=>array('/results/person'),
			$person->name,
		);
		$this->pageTitle = array($person->name, 'Personal Page');
		$this->title = Yii::t('common', 'Personal Page');
		$this->setWeiboShareDefaultText($person->name . '选手的魔方速拧成绩页 - 粗饼·中国魔方赛事网', false);
		$data['year'] = Summary::getCurrentYear();
		$this->render('p', $data);
	}

	public function actionCert() {
		$hash = $this->sGet('hash');
		if (!$hash) {
			throw new CHttpException(404, 'Error');
		}
		$cert = CompetitionCert::model()->findByAttributes([
			'hash'=>$hash,
		]);
		if ($cert === null) {
			throw new CHttpException(404, 'Error');
		}
		$competition = $cert->competition;
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Competitions'=>array('/results/competition'),
			$competition->getAttributeValue('name')=>$competition->getUrl(),
			'Certificate',
		);
		$this->getWechatOfficialAccount([
			'jsConfig'=>[
				'onMenuShareTimeline',
				'onMenuShareAppMessage',
				'onMenuShareQQ',
				'onMenuShareWeibo',
				'onMenuShareQZone',
			],
		]);
		$this->pageTitle = array($competition->getAttributeValue('name'), 'Certificate');
		$this->title = $competition->getAttributeValue('name') . '-' . Yii::t('common', 'Certificate');
		$this->render('cert', [
			'cert'=>$cert,
			'competition'=>$competition,
			'user'=>$cert->user,
		]);
	}

	public function actionBattle() {
		$ids = isset($_GET['ids']) ? $_GET['ids'] : array();
		if (is_string($ids)) {
			$ids = explode('-', $ids);
		}
		$ids = array_unique(array_map('strtoupper', $ids));
		$ids = array_slice($ids, 0, 4);
		$persons = array();
		$names = array();
		foreach ($ids as $id) {
			$person = Persons::model()->findByAttributes(array('wca_id' => $id, 'sub_id'=>1));
			if ($person !== null) {
				$persons[] = array(
					'person'=>$person,
					'results'=>Yii::app()->cache->getData(array('Persons', 'getResults'), $id),
				);
				$names[] = $person->name;
			}
		}
		if (count($persons) === 1 && !Yii::app()->user->isGuest && $this->user->wcaid != '' && $persons[0]['person']->wca_id !== $this->user->wcaid) {
			$person = Persons::model()->findByAttributes(array('wca_id' => $this->user->wcaid, 'sub_id'=>1));
			if ($person !== null) {
				$persons[] = array(
					'person'=>$person,
					'results'=>Yii::app()->cache->getData(array('Persons', 'getResults'), $this->user->wcaid),
				);
				$names[] = $person->name;
			}
		}
		switch (count($persons)) {
			case 0:
				$this->redirect(array('/results/person'));
				break;
			case 1:
				$this->redirect(array('/results/p', 'id'=>$persons[0]['person']->wca_id));
				break;
		}
		$persons = array_slice($persons, 0, 4);
		$data = $this->handlePKPersons($persons);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Persons'=>array('/results/person'),
			'Battle',
		);
		$names[] = 'Battle';
		$this->pageTitle = $names;
		$this->title = Yii::t('common', 'Battle');
		$this->render('battle', $data);
	}

	private function handlePKPersons($persons) {
		//id
		$event_ids = array();
		$winners = array();
		$bestData = array(
			'competitions'=>array(
				'expression'=>'count($results["competitions"])',
				'type'=>'max',
			),
			'career'=>array(
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
			'singleSumOfNR'=>array(
				'expression'=>'$results["sumOfRanks"][0]->country_rank',
				'type'=>'min',
			),
			'singleSumOfCR'=>array(
				'expression'=>'$results["sumOfRanks"][0]->continent_rank',
				'type'=>'min',
			),
			'singleSumOfWR'=>array(
				'expression'=>'$results["sumOfRanks"][0]->world_rank',
				'type'=>'min',
			),
			'averageSumOfNR'=>array(
				'expression'=>'$results["sumOfRanks"][1]->country_rank',
				'type'=>'min',
			),
			'averageSumOfCR'=>array(
				'expression'=>'$results["sumOfRanks"][1]->continent_rank',
				'type'=>'min',
			),
			'averageSumOfWR'=>array(
				'expression'=>'$results["sumOfRanks"][1]->world_rank',
				'type'=>'min',
			),
		);
		foreach ($bestData as $key=>$value) {
			$bestData[$key]['value'] = $this->getBestData($persons, $value['expression'], $value['type'], isset($value['canBeZero']) ? $value['canBeZero'] : false);
		}
		$countries = $continents = array();
		foreach ($persons as $person) {
			$id = $person['person']->id;
			$countries[$person['person']->country_id] = $person['person']->country_id;
			$continents[$person['person']->country->continent_id] = $person['person']->country->continent_id;
			foreach ($person['results']['personRanks'] as $event_id=>$ranks) {
				$event_ids[$event_id] = true;
			}
			foreach ($bestData as $key=>$value) {
				if ($this->evaluateExpression($value['expression'], $person) === $value['value']) {
					$winners[$id][$key] = true;
				}
			}
		}
		foreach ($event_ids as $event_id=>$value) {
			$singleExpression = "isset(\$results['personRanks']['{$event_id}']) ? \$results['personRanks']['{$event_id}']->best : -1";
			$averageExpression = "isset(\$results['personRanks']['{$event_id}']) && \$results['personRanks']['{$event_id}']->average !== null ? \$results['personRanks']['{$event_id}']->average->best : -1";
			//single devide average
			$sdaExpression = "isset(\$results['personRanks']['{$event_id}']) && \$results['personRanks']['{$event_id}']->average !== null ? \$results['personRanks']['{$event_id}']->best / \$results['personRanks']['{$event_id}']->average->best : -1";
			$singleNRExpression = "isset(\$results['personRanks']['{$event_id}']) ? \$results['personRanks']['{$event_id}']->country_rank : -1";
			$averageNRExpression = "isset(\$results['personRanks']['{$event_id}']) && \$results['personRanks']['{$event_id}']->average !== null ? \$results['personRanks']['{$event_id}']->average->country_rank : -1";
			$singleCRExpression = "isset(\$results['personRanks']['{$event_id}']) ? \$results['personRanks']['{$event_id}']->continent_rank : -1";
			$averageCRExpression = "isset(\$results['personRanks']['{$event_id}']) && \$results['personRanks']['{$event_id}']->average !== null ? \$results['personRanks']['{$event_id}']->average->continent_rank : -1";
			$medalsExpression = "isset(\$results['personRanks']['{$event_id}']) ? \$results['personRanks']['{$event_id}']->medals['gold'] * 1e8 + \$results['personRanks']['{$event_id}']->medals['silver'] * 1e4 + \$results['personRanks']['{$event_id}']->medals['bronze'] : 0";
			$solvesExpression = "isset(\$results['personRanks']['{$event_id}']) ? \$results['personRanks']['{$event_id}']->medals['solve'] * 10000000 - \$results['personRanks']['{$event_id}']->medals['attempt'] : -1";
			$bestSingle = $this->getBestData($persons, $singleExpression);
			$bestAverage = $this->getBestData($persons, $averageExpression);
			$bestSDA = $this->getBestData($persons, $sdaExpression);
			$bestSingleNR = $this->getBestData($persons, $singleNRExpression);
			$bestAverageNR = $this->getBestData($persons, $averageNRExpression);
			$bestSingleCR = $this->getBestData($persons, $singleCRExpression);
			$bestAverageCR = $this->getBestData($persons, $averageCRExpression);
			$bestMedals = $this->getBestData($persons, $medalsExpression, 'max');
			$bestSolves = $this->getBestData($persons, $solvesExpression, 'max');
			$event_ids[$event_id] &= $bestAverage > 0;
			foreach ($persons as $person) {
				$id = $person['person']->id;
				$single = $this->evaluateExpression($singleExpression, $person);
				$average = $this->evaluateExpression($averageExpression, $person);
				$sda = $this->evaluateExpression($sdaExpression, $person);
				$singleNR = $this->evaluateExpression($singleNRExpression, $person);
				$averageNR = $this->evaluateExpression($averageNRExpression, $person);
				$singleCR = $this->evaluateExpression($singleCRExpression, $person);
				$averageCR = $this->evaluateExpression($averageCRExpression, $person);
				$medals = $this->evaluateExpression($medalsExpression, $person);
				$solves = $this->evaluateExpression($solvesExpression, $person, 'max');
				if (isset($person['results']['personRanks'][$event_id])) {
					$person['results']['personRanks'][$event_id]->medals['sda'] = $sda > 0 ? number_format($event_id === '333fm' ? $sda * 100 : $sda, 4) : '-';
				}
				if ($single === $bestSingle) {
					$winners[$id][$event_id . 'Single'] = true;
					$winners[$id][$event_id . 'SingleWR'] = true;
				}
				if ($singleNR === $bestSingleNR) {
					$winners[$id][$event_id . 'SingleNR'] = true;
				}
				if ($singleCR === $bestSingleCR) {
					$winners[$id][$event_id . 'SingleCR'] = true;
				}
				if ($average === $bestAverage && $bestAverage > 0) {
					$winners[$id][$event_id . 'Average'] = true;
					$winners[$id][$event_id . 'AverageWR'] = true;
				}
				if ($averageNR === $bestAverageNR && $bestAverage > 0) {
					$winners[$id][$event_id . 'AverageNR'] = true;
				}
				if ($averageCR === $bestAverageCR && $bestAverage > 0) {
					$winners[$id][$event_id . 'AverageCR'] = true;
				}
				if ($medals === $bestMedals) {
					$winners[$id][$event_id . 'Medals'] = true;
				}
				if ($solves === $bestSolves) {
					$winners[$id][$event_id . 'Solves'] = true;
				}
				if ($sda === $bestSDA && $sda > 0) {
					$winners[$id][$event_id . 'SDA'] = true;
				}
			}
		}
		$rivalries = array();
		if (count($persons) === 2) {
			$person1Results = array();
			$id1 = $persons[0]['person']->id;
			$id2 = $persons[1]['person']->id;
			foreach ($persons[0]['results']['byEvent'] as $result) {
				$person1Results[$result->competition_id][$result->event_id][$result->round_type_id] = $result->pos;
			}
			foreach ($persons[1]['results']['byEvent'] as $result) {
				$event_id = $result->event_id;
				$round_type_id = $result->round_type_id;
				if (isset($person1Results[$result->competition_id][$event_id][$round_type_id])) {
					$pos = $person1Results[$result->competition_id][$event_id][$round_type_id];
					if (!isset($rivalries[$event_id][$id1]['overAll'])) {
						$rivalries[$event_id][$id1]['overAll'] = array(
							'wins'=>0,
							'loses'=>0,
							'ties'=>0,
						);
						$rivalries[$event_id][$id1]['final'] = array(
							'wins'=>0,
							'loses'=>0,
							'ties'=>0,
						);
						$rivalries[$event_id][$id2]['overAll'] = array(
							'wins'=>0,
							'loses'=>0,
							'ties'=>0,
						);
						$rivalries[$event_id][$id2]['final'] = array(
							'wins'=>0,
							'loses'=>0,
							'ties'=>0,
						);
					}
					$key1 = $key2 = '';
					if ($pos < $result->pos) {
						$key1 = 'wins';
						$key2 = 'loses';
					} elseif ($pos > $result->pos) {
						$key1 = 'loses';
						$key2 = 'wins';
					} else {
						$key1 = $key2 = 'ties';
					}
					if ($key1 !== '') {
						$rivalries[$event_id][$id1]['overAll'][$key1]++;
						$rivalries[$event_id][$id2]['overAll'][$key2]++;
						if (in_array($round_type_id, array('c', 'f'))) {
							$rivalries[$event_id][$id1]['final'][$key1]++;
							$rivalries[$event_id][$id2]['final'][$key2]++;
						}
					}
				}
			}
		}
		return array(
			'persons'=>$persons,
			'event_ids'=>$event_ids,
			'bestData'=>$bestData,
			'winners'=>$winners,
			'sameCountry'=>count($countries) === 1,
			'sameContinent'=>count($continents) === 1,
			'rivalries'=>$rivalries,
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

	protected function getRivalryWinnerCSSClass($person, $event_id, $rivalries, $type) {
		$rivalry = $rivalries[$event_id][$person['person']->id][$type];
		if ($rivalry['wins'] >= $rivalry['loses'] && array_sum($rivalry) > 0) {
			return ' class="winner"';
		}
		return '';
	}

	protected function getRivalryResult($rivalry) {
		$result = array();
		foreach (array('wins', 'loses', 'ties') as $type) {
			$count = $rivalry[$type];
			if ($type === 'ties' && $count == 0) {
				continue;
			}
			if ($count <= 1) {
				$type = substr($type, 0, -1);
			}
			$result[] = $count;
			$result[] = Yii::t('common', $type);
		}
		$rounds = array('(');
		$rounds[] = array_sum($rivalry);
		$rounds[] = ' ';
		if (array_sum($rivalry) > 1) {
			$rounds[] = Yii::t('common', 'rounds');
		} else {
			$rounds[] = Yii::t('common', 'round');
		}
		$rounds[] = ')';
		$result[] = implode('', $rounds);
		return implode(' ', $result);
	}

	protected function getPersonRankValue($results, $event_id, $attribute) {
		if (!isset($results['personRanks'][$event_id])) {
			return '-';
		}
		$model = $results['personRanks'][$event_id];
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
		$value = isset($model[$attribute]) ? $model[$attribute] : '-';
		if ($attribute === 'best') {
			$value = Results::formatTime($value, "$event_id");
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
		$types = Competitions::getResultsTypes();
		$competition = Competitions::model()->findByAttributes(array('id' => $id));
		if ($competition == null) {
			$this->redirect(array('/results/competition'));
		}
		if (!array_key_exists($type, $types)) {
			$type = 'winners';
		}
		if (($c = Competition::model()->findByAttributes(array(
			'wca_competition_id'=>$id,
			'status'=>Competition::STATUS_SHOW,
		))) !== null) {
			$competition->name = $c->getAttributeValue('name');
			$competition->location = $c->isMultiLocation() ? $c->getLocationInfo('venue') : $c->location[0]->getFullAddress(false);
			$competition->c = $c;
		}
		$data = Yii::app()->cache->getData([$competition, 'getResults'], $id);
		if (empty($data['records']) && $type !== 'records') {
			unset($types['records']);
		}
		if (empty($data['scrambles']) && $type !== 'scrambles') {
			unset($types['scrambles']);
		}
		$data['competition'] = $competition;
		$data['type'] = $type;
		$data['types'] = $types;
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
		$page = $this->iGet('page', 1);
		$name = $this->sGet('name');
		$names = array_map('ucfirst', explode('-', $name));
		$class = implode('', $names);
		$this->description = Yii::t('statistics', 'Based on the official WCA competition results, we generated several WCA statistics about Chinese competitions and competitors, which were regularly up-to-date.');
		if ($class !== '') {
			if (method_exists($this, $method = 'stat' . $class)) {
				$this->$method(implode(' ', $names));
				Yii::app()->end();
			} else {
				throw new CHttpException(404);
			}
		}
		$data = Statistics::getData($page);
		extract($data);
		$this->pageTitle = array('Fun Statistics');
		$this->title = 'Fun Statistics';
		$this->setWeiboShareDefaultText('关于中国WCA官方比赛及选手成绩的一系列趣味统计', false);
		$this->render('statistics', array(
			'statistics'=>$statistics,
			'time'=>$time,
		));
	}

	private function statMostPersons() {
		$page = $this->iGet('page', 1);
		$region = $this->sGet('region', 'China');
		$gender = $this->sGet('gender', 'all');
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		$statistic = array(
			'class'=>'MostNumber',
			'region'=>$region,
			'group'=>'competition_id',
			'gender'=>$gender,
		);
		if ($page < 1) {
			$page = 1;
		}
		$this->title = Yii::t('statistics', 'Most Persons');
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
		$this->render('stat/mostPersons', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'region'=>$region,
			'gender'=>$gender,
		));
	}

	private function statMostCompetitions() {
		$page = $this->iGet('page', 1);
		$region = $this->sGet('region', 'China');
		$gender = $this->sGet('gender', 'all');
		$year = $this->iGet('year', null);
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (!array_key_exists($year, Competitions::getYears(false))) {
			$year = null;
		}
		$statistic = array(
			'class'=>'MostNumber',
			'region'=>$region,
			'gender'=>$gender,
			'year'=>$year,
			'group'=>'person_id',
		);
		if ($page < 1) {
			$page = 1;
		}
		$this->title = Yii::t('statistics', 'Most Competitions');
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
		$this->render('stat/mostCompetitions', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'region'=>$region,
			'gender'=>$gender,
			'year'=>$year,
		));
	}

	private function statSumOfRanks() {
		$page = $this->iGet('page', 1);
		$type = $this->sGet('type', 'single');
		$gender = $this->sGet('gender', 'all');
		$event_ids = $this->aGet('event');
		$region = $this->sGet('region', 'China');
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		if (!in_array($type, Results::getRankingTypes())) {
			$type = 'single';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (array_intersect($event_ids, array_keys(Events::getNormalEvents())) === array()) {
			$event_ids = array();
		}
		$statistic = array(
			'class'=>'SumOfRanks',
			'type'=>$type,
			'region'=>$region,
			'event_ids'=>$event_ids,
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
			'region'=>$region,
			'gender'=>$gender,
			'event_ids'=>$event_ids,
		));
	}

	private function statSumOfCountryRanks() {
		$page = $this->iGet('page', 1);
		$type = $this->sGet('type', 'single');
		$gender = $this->sGet('gender', 'all');
		$event_ids = $this->aGet('event');
		if (!in_array($type, Results::getRankingTypes())) {
			$type = 'single';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (array_intersect($event_ids, array_keys(Events::getNormalEvents())) === array()) {
			$event_ids = array();
		}
		$statistic = array(
			'class'=>'SumOfCountryRanks',
			'type'=>$type,
			'event_ids'=>$event_ids,
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
			'event_ids'=>$event_ids,
		));
	}

	private function statBestPodiums() {
		$page = $this->iGet('page', 1);
		$event_id = $this->sGet('event');
		if (!in_array($event_id, array_keys(Events::getNormalEvents()))) {
			$event_id = '333';
		}
		$statistic = array(
			'class'=>'BestPodiums',
			'type'=>'single',
			'event_id'=>$event_id,
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
			'event'=>$event_id,
		));
	}

	private function statUncrownedKings($name) {
		$statistic = [
			'exclude'=>'pos',
			'pos'=>[1],
		];
		$this->statBestMissers($name, $statistic);
	}

	private function statPodiumMissers($name) {
		$statistic = [
			'exclude'=>'pos',
			'pos'=>[1, 2, 3],
		];
		$this->statBestMissers($name, $statistic);
	}

	private function statRecordMissers($name) {
		$type = $this->sGet('type', 'single');
		if (!in_array($type, Results::getRankingTypes())) {
			$type = 'single';
		}
		$statistic = [
			'exclude'=>'record',
			'rankType'=>$type,
		];
		$this->statBestMissers($name, $statistic);
	}

	private function statBestMissers($name, $statistic) {
		$page = $this->iGet('page', 1);
		$event_id = $this->sGet('event');
		$region = $this->sGet('region', 'China');
		$gender = $this->sGet('gender', 'all');
		$type = $this->sGet('type', 'single');
		if (!in_array($type, Results::getRankingTypes())) {
			$type = 'single';
		}
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (!in_array($event_id, array_keys(Events::getNormalEvents()))) {
			$event_id = '333';
		}
		$statistic = array_merge($statistic, [
			'class'=>'BestMisser',
			'type'=>'single',
			'event_id'=>$event_id,
			'region'=>$region,
			'gender'=>$gender,
		]);
		if ($page < 1) {
			$page = 1;
		}
		$this->title = Yii::t('statistics', $name);
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
		$descriptions = [
			'Uncrowned Kings'=>Yii::t('statistics', 'Competitors who never won a champion in the event, ranked by the results of preferred format.'),
			'Podium Missers'=>Yii::t('statistics', 'Competitors who were never on the podium in the event, ranked by the results of preferred format.'),
			'Record Missers'=>Yii::t('statistics', 'Competitors who never broke a single/average record in the event, ranked by single/average.'),
		];
		$this->render('stat/bestMissers', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'event'=>$event_id,
			'gender'=>$gender,
			'region'=>$region,
			'type'=>$type,
			'name'=>$name,
			'description'=>$descriptions[$name],
			'hasType'=>isset($statistic['rankType']),
		));
	}

	private function statAllEventsAchiever() {
		$page = 1;
		$region = $this->sGet('region', 'China');
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		$statistic = array(
			'class'=>'AllEventsAchiever',
			'region'=>$region,
		);
		$this->title = Yii::t('statistics', 'All Events Achiever');
		$this->pageTitle = array('Fun Statistics', $this->title);
		$this->breadcrumbs = array(
			'Results'=>array('/results/index'),
			'Statistics'=>array('/results/statistics'),
			$this->title,
		);
		$data = Statistics::buildRankings($statistic, $page, 500);
		extract($data);
		$this->render('stat/allEventsAchiever', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'region'=>$region,
		));
	}

	private function statMostSolves() {
		$page = $this->iGet('page', 1);
		$gender = $this->sGet('gender', 'all');
		$year = $this->iGet('year', null);
		$event_ids = $this->aGet('event');
		$region = $this->sGet('region', 'China');
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (!array_key_exists($year, Competitions::getYears(false))) {
			$year = null;
		}
		if (array_intersect($event_ids, array_keys(Events::getNormalEvents())) === array()) {
			$event_ids = array();
		}
		$statistic = array(
			'class'=>'MostSolves',
			'type'=>'all',
			'event_ids'=>$event_ids,
			'gender'=>$gender,
			'year'=>$year,
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
			'year'=>$year,
			'event_ids'=>$event_ids,
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
		$event_ids = $this->aGet('event');
		$region = $this->sGet('region', 'China');
		$gender = $this->sGet('gender', 'all');
		$year = $this->iGet('year', null);
		if (array_intersect($event_ids, array_keys(Events::getNormalEvents())) === array()) {
			$event_ids = array();
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		if (!array_key_exists($year, Competitions::getYears(false))) {
			$year = null;
		}
		$statistic = array(
			'class'=>'MedalCollection',
			'type'=>'all',
			'event_ids'=>$event_ids,
			'region'=>$region,
			'gender'=>$gender,
			'year'=>$year,
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
			'event_ids'=>$event_ids,
			'region'=>$region,
			'gender'=>$gender,
			'year'=>$year,
		));
	}

	private function statMostPos() {
		$page = $this->iGet('page', 1);
		$pos = $this->iGet('pos', 2);
		$region = $this->sGet('region', 'China');
		$event_ids = $this->aGet('event');
		$gender = $this->sGet('gender', 'all');
		$includeDNF = $this->iGet('includeDNF', 0);
		if (!in_array($pos, MostPos::$positions)) {
			$pos = 2;
		}
		if (!Region::isValidRegion($region)) {
			$region = 'China';
		}
		if (array_intersect($event_ids, array_keys(Events::getNormalEvents())) === array()) {
			$event_ids = array();
		}
		if (!array_key_exists($gender, Persons::getGenders())) {
			$gender = 'all';
		}
		$statistic = array(
			'class'=>'MostPos',
			'type'=>'all',
			'region'=>$region,
			'pos'=>$pos,
			'region'=>$region,
			'event_ids'=>$event_ids,
			'gender'=>$gender,
			'includeDNF'=>$includeDNF,
		);
		if ($page < 1) {
			$page = 1;
		}
		$this->title = Yii::t('statistics', 'Most nth Place');
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
		$this->render('stat/mostPos', array(
			'statistic'=>$statistic,
			'time'=>$time,
			'page'=>$page,
			'pos'=>$pos,
			'region'=>$region,
			'event_ids'=>$event_ids,
			'gender'=>$gender,
			'includeDNF'=>$includeDNF,
		));
	}
}
