<?php

class PayController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'roles'=>array(
					'role'=>User::ROLE_ORGANIZER,
				),
			),
			array(
				'allow',
				'roles'=>[
					'permission'=>'caqa'
				],
				'actions'=>[
					'index'
				]
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
		if ($this->user->isOrganizer()) {
			$model->type = Pay::TYPE_REGISTRATION;
			if ($model->type_id == null) {
				$model->type_id = 0;
			}
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/pay/index'));
		}
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionBill() {
		$model = new Pay();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Pay');
		$model->channel = Pay::CHANNEL_BALIPAY;
		$model->status = Pay::STATUS_PAID;
		$this->render('bill', [
			'model'=>$model,
		]);
	}
}
