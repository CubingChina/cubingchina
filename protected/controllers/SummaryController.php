<?php

class SummaryController extends Controller {
	protected $logAction = false;

	public function accessRules() {
		return [
			[
				'allow',
				'users'=>['*'],
			],
		];
	}

	protected function beforeAction($action) {
		Yii::import('application.summary.*');
		return parent::beforeAction($action);
	}

	public function actionIndex() {
		$year = $this->iGet('year');
		$summary = new Summary($year, 'site');
		if (!$summary->isExists()) {
			throw new CHttpException(404);
		}
		$data = Yii::app()->cache->getData([$summary, 'site'], [$year]);
		$this->title = Yii::t('common', '{year} Annual Summary', [
			'{year}'=>$year,
		]);
		$this->pageTitle = [$this->title];
		$this->description = $this->title;
		$this->breadcrumbs = [
			$this->title,
		];
		$application = $this->getWechatApplication([
			'js'=>true,
		]);
		$js = $application->js;
		$js->setUrl(Yii::app()->request->getBaseUrl(true) . Yii::app()->request->url);
		try {
			$config = $js->config(array(
				'onMenuShareTimeline',
				'onMenuShareAppMessage',
				'onMenuShareQQ',
				'onMenuShareWeibo',
				'onMenuShareQZone',
			), YII_DEBUG);
		} catch (Exception $e) {
			$config = '{}';
		}
		$data['config'] = $config;
		$this->render('index', $data);
	}

	public function actionPerson() {
		$year = $this->iGet('year');
		$id = strtoupper($this->sGet('id'));
		$summary = new Summary($year);
		$person = Persons::model()->with('country')->findByAttributes(['id' => $id]);
		if ($person == null) {
			throw new CHttpException(404);
		}
		if (!$summary->isExists($person)) {
			throw new CHttpException(404);
		}
		$personData = Yii::app()->cache->getData(['Persons', 'getResults'], $id);
		$data = Yii::app()->cache->getData([$summary, 'person'], [$person, $personData, $year]);
		$this->title = Yii::t('common', '{personName}\'s {year} Annual Summary', [
			'{personName}'=>$person->name,
			'{year}'=>$year,
		]);
		$this->pageTitle = [$this->title];
		$this->description = $this->title;
		$this->breadcrumbs = [
			'Annual Summary',
			$person->name,
		];
		$application = $this->getWechatApplication([
			'js'=>true,
		]);
		$js = $application->js;
		$js->setUrl(Yii::app()->request->getBaseUrl(true) . Yii::app()->request->url);
		try {
			$config = $js->config(array(
				'onMenuShareTimeline',
				'onMenuShareAppMessage',
				'onMenuShareQQ',
				'onMenuShareWeibo',
				'onMenuShareQZone',
			), YII_DEBUG);
		} catch (Exception $e) {
			$config = '{}';
		}
		$data['config'] = $config;
		$this->render('person', $data);
	}
}
