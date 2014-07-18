<?php

class Mf8Controller extends Controller {
	protected $logAction = false;

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}
	public function actionBbs() {
		$upcomingCompetitions = Competition::getUpcomingRegistrableCompetitions(100);
		$this->renderPartial('bbs', array(
			'upcomingCompetitions'=>$upcomingCompetitions,
		));
	}
}