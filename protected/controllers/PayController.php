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
		if ($model->pay === null) {
			$model->pay = $model->createPay();
		}
		$this->render('pay', array(
			'model'=>$model->pay,
		));
	}

	public function actionUrl() {
		$id = $this->iGet('id');
		$isMobile = $this->iRequest('is_mobile');
		$model = Pay::model()->findByPk($id);
		if ($model === null || $model->user_id !== Yii::app()->user->id) {
			throw new CHttpException(401, 'Unauthorized Access');
		}
		$url = '';
		if ($model->isPaid()) {
			switch ($model->type) {
				case Pay::TYPE_REGISTRATION:
					$competition = Competition::model()->findByPk($model->type_id);
					$url = $competition->getUrl('registration');
					break;
			}
		} else {
			$url = $model->generateNowPayUrl($isMobile);
		}
		$this->ajaxOk(array(
			'url'=>$url,
		));
	}
}