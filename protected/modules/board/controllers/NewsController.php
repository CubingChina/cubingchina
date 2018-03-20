<?php

class NewsController extends AdminController {

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

	public function actionAdd() {
		$model = new News();
		$model->user_id = $this->user->id;
		$model->date = time();
		$model->status = News::STATUS_HIDE;
		// $model->unsetAttributes();
		if (isset($_POST['News'])) {
			$model->attributes = $_POST['News'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '新加新闻成功');
				$this->redirect(array('/board/news/index'));
			}
		}
		$model->formatDate();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = News::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['News'])) {
			$model->attributes = $_POST['News'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新新闻成功');
				$this->redirect($this->getReferrer());
			}
		}
		$model->formatDate();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionIndex() {
		$model = new News();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('News');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionEditTemplate() {
		$id = $this->iGet('id');
		$model = NewsTemplate::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['NewsTemplate'])) {
			$model->attributes = $_POST['NewsTemplate'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新新闻模板成功');
				$this->redirect($this->getReferrer());
			}
		}
		$this->render('editTemplate', array(
			'model'=>$model,
		));
	}

	public function actionTemplate() {
		$model = new NewsTemplate();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('NewsTemplate');
		$this->render('template', array(
			'model'=>$model,
		));
	}

	public function actionRender() {
		$competition = Competition::model()->findByPk($this->iRequest('competition_id'));
		$template = NewsTemplate::model()->findByPk($this->iRequest('template_id'));
		if ($competition === null || $template === null) {
			$this->ajaxOK(null);
		}
		$data = $competition->generateTemplateData();
		$contents = $template->render($data);
		$this->ajaxOK($contents);
	}

	public function actionShow() {
		$id = $this->iGet('id');
		$model = News::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatDate();
		$model->status = News::STATUS_SHOW;
		$model->save();
		Yii::app()->user->setFlash('success', '发布新闻成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionHide() {
		$id = $this->iGet('id');
		$model = News::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatDate();
		$model->status = News::STATUS_HIDE;
		$model->save();
		Yii::app()->user->setFlash('success', '隐藏新闻成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}
}
