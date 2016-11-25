<?php

class ConfigController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'roles'=>array(
					'role'=>User::ROLE_ADMINISTRATOR,
				),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionEdit() {
		$id = $this->sGet('id');
		$model = Config::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['Config'])) {
			$model->attributes = $_POST['Config'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新配置成功');
				$this->redirect($this->getReferrer());
			}
		}
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionIndex() {
		$model = new Config();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Config');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionShow() {
		$id = $this->sGet('id');
		$model = Config::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = Config::STATUS_SHOW;
		$model->save();
		Yii::app()->user->setFlash('success', '发布配置成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionHide() {
		$id = $this->sGet('id');
		$model = Config::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = Config::STATUS_HIDE;
		$model->save();
		Yii::app()->user->setFlash('success', '隐藏配置成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}
}
