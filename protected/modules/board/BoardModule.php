<?php

class BoardModule extends CWebModule {
	public function init() {
		$this->setImport(array(
			'board.models.*',
			'board.components.*',
		));
		$this->setViewPath(Yii::getPathOfAlias('application.views.board'));
		Yii::app()->errorHandler->errorAction = '/board/default/error';
		Yii::app()->language = 'zh_cn';
	}

	public function beforeControllerAction($controller, $action) {
		return parent::beforeControllerAction($controller, $action);
	}
}
