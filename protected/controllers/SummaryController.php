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
	}

	public function actionPerson() {
		$year = $this->iGet('year');
		$id = strtoupper($this->sGet('id'));
		if ($year != 2016) {
			throw new CHttpException(404);
		}
		$summaryClassName = 'Summary' . $year;
		$summaryClass = new $summaryClassName();
		$person = Persons::model()->with('country')->findByAttributes(['id' => $id]);
		if ($person == null) {
			throw new CHttpException(404);
		}
		$data = Yii::app()->cache->getData(['Persons', 'getResults'], $id);
		$summary = Yii::app()->cache->getData([$summaryClass, 'person'], [$person, $data]);
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
		$summary['config'] = $config;
		$this->render('person' . $year, $summary);
	}
}
