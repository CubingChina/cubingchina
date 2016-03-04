<?php

class LiveController extends Controller {

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$min = DEV ? '' : '.min';
		$version = Yii::app()->params->jsVer;
		$clientScript = Yii::app()->clientScript;
		$clientScript->registerScriptFile('/f/js/websocket' . $min . '.js');
		$clientScript->registerScriptFile('/f/js/live' . $min . '.js');
		$this->render('index', array(
		));
	}
}
