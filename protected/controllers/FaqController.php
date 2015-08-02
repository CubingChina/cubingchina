<?php

class FaqController extends Controller {
	protected $logAction = false;

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex() {
		$categoryId = $this->iGet('category_id', 1);
		$model = new Faq();
		$model->unsetAttributes();
		$model->category_id = $categoryId;
		$model->status = Faq::STATUS_SHOW;
		$categories = FaqCategory::getCategoryMenu();
		$this->title = Yii::t('common', 'Frequently Asked Questions');
		$this->pageTitle = array($this->title);
		if ($model->category) {
			$this->pageTitle = array($this->title, $model->category->getAttributeValue('name'));
		}
		$this->breadcrumbs = array(
			'FAQ',
		);
		$this->render('index', array(
			'model'=>$model,
			'categories'=>$categories,
		));
	}
}