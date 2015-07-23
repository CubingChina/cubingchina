<?php

class PayController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'roles'=>array(User::ROLE_ADMINISTRATOR),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$model = new Pay();
		$model->unsetAttributes();
		$model->type = Pay::TYPE_REGISTRATION;
		$model->attributes = $this->aRequest('Pay');
		$this->render('index', array(
			'model'=>$model,
		));
	}
}