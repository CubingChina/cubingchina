<?php
class FaqController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'actions'=>array('index', 'add', 'edit'),
				'roles'=>array(
					'permission'=>'faq',
				),
			),
			array(
				'allow',
				'roles'=>array(
					'permission'=>'faq_admin',
				),
			),
			array(
				'allow',
				'roles'=>[
					'permission'=>'wct',
				]
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionAdd() {
		$model = new Faq();
		$model->user_id = $this->user->id;
		$model->date = time();
		$model->status = Faq::STATUS_HIDE;
		// $model->unsetAttributes();
		if (isset($_POST['Faq'])) {
			$model->attributes = $_POST['Faq'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '新加FAQ成功');
				$this->redirect(array('/board/faq/index'));
			}
		}
		$model->formatDate();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = Faq::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['Faq'])) {
			$model->attributes = $_POST['Faq'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新FAQ成功');
				$this->redirect($this->getReferrer());
			}
		}
		$model->formatDate();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionIndex() {
		$model = new Faq();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Faq');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionShow() {
		$id = $this->iGet('id');
		$model = Faq::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatDate();
		$model->status = Faq::STATUS_SHOW;
		$model->save();
		Yii::app()->user->setFlash('success', '发布FAQ成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionHide() {
		$id = $this->iGet('id');
		$model = Faq::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatDate();
		$model->status = Faq::STATUS_HIDE;
		$model->save();
		Yii::app()->user->setFlash('success', '隐藏FAQ成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionAddCategory() {
		$model = new FaqCategory();
		$model->user_id = $this->user->id;
		$model->date = time();
		$model->status = FaqCategory::STATUS_HIDE;
		// $model->unsetAttributes();
		if (isset($_POST['FaqCategory'])) {
			$model->attributes = $_POST['FaqCategory'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '新加FAQ分类成功');
				$this->redirect(array('/board/faq/category'));
			}
		}
		$model->formatDate();
		$this->render('editCategory', array(
			'model'=>$model,
		));
	}

	public function actionEditCategory() {
		$id = $this->iGet('id');
		$model = FaqCategory::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (isset($_POST['FaqCategory'])) {
			$model->attributes = $_POST['FaqCategory'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新FAQ分类成功');
				$this->redirect($this->getReferrer());
			}
		}
		$model->formatDate();
		$this->render('editCategory', array(
			'model'=>$model,
		));
	}

	public function actionCategory() {
		$model = new FaqCategory();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('FaqCategory');
		$this->render('category', array(
			'model'=>$model,
		));
	}

	public function actionShowCategory() {
		$id = $this->iGet('id');
		$model = FaqCategory::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = FaqCategory::STATUS_SHOW;
		$model->save();
		Yii::app()->user->setFlash('success', '发布FAQ分类成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionHideCategory() {
		$id = $this->iGet('id');
		$model = FaqCategory::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = FaqCategory::STATUS_HIDE;
		$model->save();
		Yii::app()->user->setFlash('success', '隐藏FAQ分类成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}
}
