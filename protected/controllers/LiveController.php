<?php

Yii::import('application.controllers.CompetitionController');

class LiveController extends CompetitionController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$this->render('index', array(
		));
	}

	public function actionCompetition() {
		$competition = $this->getCompetition();
		$this->title = $competition->getAttributeValue('name') . '-' . Yii::t('common', 'Live');
		$min = DEV ? '' : '.min';
		$version = Yii::app()->params->jsVer;
		$clientScript = Yii::app()->clientScript;
		$clientScript->registerScriptFile('/f/js/websocket' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/vue/vue' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/vue-router/vue-router' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/vuex/vuex' . $min . '.js');
		$clientScript->registerScriptFile('/f/js/live' . $min . '.js');
		$this->render('competition', array(
			'competition'=>$competition,
		));
	}
}
