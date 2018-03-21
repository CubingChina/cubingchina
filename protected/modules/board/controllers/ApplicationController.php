<?php

class ApplicationController extends AdminController {

	public function actionIndex() {
		$model = new Application();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Application');
		$this->render('index', [
			'model'=>$model,
		]);
	}

	public function actionAdd() {
		$model = new Application();
		$model->status = Application::STATUS_DISABLED;
		if (isset($_POST['Application'])) {
			$model->attributes = $_POST['Application'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '新增应用成功');
				$this->redirect(['/board/application/index']);
			}
		}
		$this->render('edit', [
			'model'=>$model,
		]);
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = Application::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['Application'])) {
			$model->attributes = $_POST['Application'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新应用成功');
				$this->redirect($this->getReferrer());
			}
		}
		$this->render('edit', [
			'model'=>$model,
		]);
	}

	public function actionEnable() {
		$id = $this->iGet('id');
		$model = Application::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = Application::STATUS_ENABLED;
		$model->save();
		Yii::app()->user->setFlash('success', '启用应用成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionDisable() {
		$id = $this->iGet('id');
		$model = Application::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = Application::STATUS_DISABLED;
		$model->save();
		Yii::app()->user->setFlash('success', '禁用应用成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}
}
