<?php

class BoardModule extends CWebModule {
	public function init() {
		$this->setImport(array(
			'board.models.*',
			'board.components.*',
		));
		$this->setViewPath(Yii::getPathOfAlias('application.views.board'));
	}

	public function beforeControllerAction($controller, $action) {
		Yii::app()->language = 'zh_cn';
		return parent::beforeControllerAction($controller, $action);
	}
}
