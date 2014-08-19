<?php

class DefaultController extends AdminController {
	public function actionError() {
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest) {
				echo $error['message'];
			} else {
				$this->pageTitle = array($error['code'] === 404 ? 'Not found' : 'Something goes wrong');
				$this->render('error', $error);
			}
		} else {
			throw new CHttpException(500);
		}
	}
}