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
					'permission'=>'caqa_member'
				],
				'actions'=>[
					'index'
				]
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
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id]) && !Yii::app()->user->checkPermission('caqa')) {
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

	public function actionTicket() {
		$model = new UserTicket('search');
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('UserTicket');
		// 默认只看已支付
		if ($model->status === null) {
			$model->status = UserTicket::STATUS_PAID;
		}

		// 主办方只能查看自己比赛的入场券
		if ($this->user->isOrganizer() && $model->competition_id && !Yii::app()->user->checkPermission('caqa')) {
			$competition = Competition::model()->findByPk($model->competition_id);
			if ($competition && !isset($competition->organizers[$this->user->id])) {
				Yii::app()->user->setFlash('danger', '权限不足！');
				$this->redirect(['/board/pay/ticket']);
			}
		}

		$this->render('ticket', [
			'model'=>$model,
		]);
	}
}
