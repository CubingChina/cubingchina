<?php
class RegistrationController extends AdminController {
	public function actionIndex() {
		$model = new Registration();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Registration');
		if ($model->competition_id == '') {
			$model->competition_id = 0;
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionExport() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && !isset($model->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		$model->formatEvents();
		if (isset($_POST['event'])) {
			$model->export($this->aPost('event'), $this->iPost('all'), $this->iPost('xlsx'), $this->iPost('extra'), $this->sPost('order'));
		}
		$exportFormsts = Events::getAllExportFormats();
		$this->render('export', array(
			'model'=>$model,
			'competition'=>$model,
			'exportFormsts'=>$exportFormsts,
		));
	}

	public function actionScoreCard() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && !isset($model->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		if (isset($_POST['order'])) {
			$model->exportScoreCard($this->iPost('all'), $this->sPost('order'));
		}
		$this->render('scoreCard', array(
			'model'=>$model,
			'competition'=>$model,
		));
	}

	public function actionSendNotice() {
		$id = $this->iGet('id');
		$competition = Competition::model()->findByPk($id);
		if ($competition === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && !isset($competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		$competition->formatEvents();
		$registration = new Registration();
		$registration->unsetAttributes();
		$registration->competition_id = $id;
		$model = new SendNoticeForm();
		if (isset($_POST['SendNoticeForm'])) {
			$model->attributes = $_POST['SendNoticeForm'];
			if ($model->validate() && $model->send($competition)) {
				Yii::app()->user->setFlash('success', '发送成功！');
				$this->redirect(array('/board/registration/index', 'Registration'=>array('competition_id'=>$id)));
			}
		}
		$this->render('sendNotice', array(
			'model'=>$model,
			'competition'=>$competition,
			'registration'=>$registration,
		));
	}

	public function actionPreviewNotice() {
		$id = $this->iGet('id');
		$competition = Competition::model()->findByPk($id);
		if ($competition === null) {
			throw new CHttpException(404, '未知比赛ID');
		}
		if ($this->user->isOrganizer() && !isset($competition->organizers[$this->user->id])) {
			throw new CHttpException(403, '权限不足');
		}
		$competition->formatEvents();
		$registration = new Registration();
		$registration->unsetAttributes();
		$registration->competition_id = $id;
		$model = new SendNoticeForm();
		if (isset($_POST['SendNoticeForm'])) {
			$model->attributes = $_POST['SendNoticeForm'];
		}
		echo json_encode($model->getPreview($competition));
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if ($this->user->isOrganizer() && $model->competition && !isset($model->competition->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect(array('/board/registration/index'));
		}
		if (isset($_POST['Registration'])) {
			$model->attributes = $_POST['Registration'];
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新报名信息成功');
				$this->redirect(array('/board/registration/index', 'Registration'=>array(
					'competition_id'=>$model->competition_id,
				)));
			}
		}
		$model->competition->formatEvents();
		$this->render('edit', array(
			'model'=>$model,
		));
	}

	public function actionShow() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatEvents();
		$model->status = Registration::STATUS_ACCEPTED;
		$model->save();
		Yii::app()->user->setFlash('success', '通过报名成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionHide() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->status = Registration::STATUS_WAITING;
		$model->save();
		Yii::app()->user->setFlash('success', '取消报名成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionPaid() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatEvents();
		$model->paid = Registration::PAID;
		$model->save();
		Yii::app()->user->setFlash('success', $model->user->name . '已付！');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionUnpaid() {
		$id = $this->iGet('id');
		$model = Registration::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->paid = Registration::UNPAID;
		$model->save();
		Yii::app()->user->setFlash('success', $model->user->name . '未付！');
		$this->redirect(Yii::app()->request->urlReferrer);
	}
}
