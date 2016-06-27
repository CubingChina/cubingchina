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

	public function actionLive() {
		$competition = $this->getCompetition();
		if ($competition->live == Competition::NO || $competition->canRegister()) {
			$this->redirect($competition->getUrl());
		}
		$competition->initLiveData();
		$min = DEV ? '' : '.min';
		$version = Yii::app()->params->jsVer;
		$clientScript = Yii::app()->clientScript;
		$clientScript->registerScriptFile('/f/js/websocket' . $min . '.js');
		$clientScript->registerScriptFile('/f/js/store.min.js');
		$clientScript->registerScriptFile('/f/plugins/vue/vue' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/vue-router/vue-router' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/vuex/vuex' . $min . '.js');
		$clientScript->registerScriptFile('/f/plugins/moment/moment' . $min . '.js');
		$clientScript->registerScriptFile('/f/js/live' . $min . '.js?ver=' . $version);
		$this->render('competition', array(
			'competition'=>$competition,
		));
	}
}
