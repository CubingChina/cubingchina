<?php

class PayController extends Controller {
	public function accessRules() {
		return array(
			array(
				'deny',
				'users'=>array('?'),
				'actions'=>array('registration'),
			),
			array(
				'allow',
				'users'=>array('@'),
				'actions'=>array('reactivate'),
			),
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionRegistration() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null || $model->user_id != Yii::app()->user->id) {
			throw new CHttpException(401, 'Unauthorized Access');
		}
		$this->render('registration', array(
			'model'=>$model,
		));

	}
}