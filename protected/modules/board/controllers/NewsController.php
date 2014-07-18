<?php
class NewsController extends AdminController {
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
				$this->redirect(array('/board/news/index'));
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
