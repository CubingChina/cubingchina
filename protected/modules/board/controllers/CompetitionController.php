<?php

class CompetitionController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'actions'=>array('index', 'application', 'event', 'schedule', 'apply', 'edit', 'editApplication', 'view', 'confirm'),
				'users'=>array('@'),
				'roles'=>array(
					'role'=>User::ROLE_CHECKED,
				),
			),
			array(
				'allow',
				'actions'=>array('toggle'),
				'users'=>array('@'),
				'roles'=>array(
					'role'=>User::ROLE_ORGANIZER,
				),
			),
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

	public function actionIndex() {
		if (!Yii::app()->user->checkRole(User::ROLE_ORGANIZER)) {
			$this->redirect(['/board/competition/application']);
		}
		$model = new Competition();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Competition');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionApplication() {
		$model = new Competition('application');
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Competition');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionView() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (!$model->checkPermission($this->user)) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect($this->getReferrer());
		}
		if ($model->application === null) {
			Yii::app()->user->setFlash('danger', '该比赛尚未填写申请资料！');
			$this->redirect($this->getReferrer());
		}
		if ($model->isAccepted()) {
			// $this->redirect(['/board/competition/edit', 'id'=>$model->id]);
		}
		if (isset($_POST['Competition']) && ($this->user->isAdministrator() || $this->user->isWCADelegate())) {
			$status = $model->status;
			$model->attributes = $_POST['Competition'];
			$model->formatDate();
			if ($model->isAccepted()) {
				$model->scenario = 'accept';
			}
			if ($model->save()) {
				$model->application->attributes = $_POST['CompetitionApplication'] ?? [];
				$model->application->save();
				switch ($model->isAccepted()) {
					case true:
						$user = $model->organizer[0]->user;
						if ($user->role < User::ROLE_ORGANIZER) {
							$user->role = User::ROLE_ORGANIZER;
							$user->save();
						}
						Yii::app()->mailer->sendCompetitionAcceptNotice($model);
						Yii::app()->user->setFlash('success', '通过比赛成功');
						$this->redirect(['/board/competition/index']);
						break;
					case false:
						Yii::app()->mailer->sendCompetitionRejectNotice($model);
						Yii::app()->user->setFlash('success', '拒绝/驳回比赛成功');
						$this->redirect(['/board/competition/application']);
						break;
				}
			}
			$model->status = $status;
			$model->handleDate();
		}
		$this->render('view', [
			'competition'=>$model,
			'nearbyCompetitions'=>$model->getNearbyCompetitions(),
		]);
	}

	public function actionApply() {
		$user = $this->user;
		if (!$user->isAdministrator() && (Competition::getUnacceptedCount($user) + Competition::getCurrentMonthCount($user)) >= 2) {
			Yii::app()->user->setFlash('danger', '如需申请更多比赛，请与管理员联系 admin@cubingchina.com');
			$this->redirect(array('/board/competition/application'));
		}
		$model = new Competition();
		$model->date = $model->end_date = $model->reg_start = $model->reg_end = '';
		$model->province_id = $model->city_id = '';
		if (isset($_POST['Competition'])) {
			$model->attributes = $_POST['Competition'];
			$model->status = Competition::STATUS_UNCONFIRMED;
			if (!$user->isAdministrator()) {
				$model->organizers = [$user->id];
			}
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '新加比赛成功');
				$this->redirect(array('/board/competition/application'));
			}
		}
		$model->formatDate();
		$this->render('edit', $this->getCompetitionData($model));
	}

	public function actionEdit() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (!$model->checkPermission($this->user)) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect($this->getReferrer());
		}
		if ($model->isConfirmed() && !$this->user->isAdministrator()) {
			Yii::app()->user->setFlash('danger', '申请已确认，不能编辑！');
			$this->redirect($this->getReferrer());
		}
		$protectedAttributes = Competition::getProtectedAttributes();
		if (isset($_POST['Competition'])) {
			foreach ($protectedAttributes as $attribute) {
				$protectedValues[$attribute] = $model->$attribute;
			}
			$model->attributes = $_POST['Competition'];
			foreach (Competition::getNullableAttributes() as $attribute) {
				if (!isset($_POST['Competition'][$attribute])) {
					$model->$attribute = '';
				}
			}
			if ($this->user->isOrganizer() && $model->isPublicVisible()) {
				foreach ($protectedAttributes as $attribute) {
					$model->$attribute = $protectedValues[$attribute];
				}
				$model->formatDate();
			}
			if (isset($_POST['lock']) && $this->user->canLock($model)) {
				$model->lock();
			}
			if (isset($_POST['hide']) && $this->user->canHide($model)) {
				$model->hide();
			}
			if (isset($_POST['announce']) && $this->user->canAnnounce($model)) {
				$model->announce();
			}
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新比赛信息成功');
				$this->redirect(['/board/competition/edit', 'id'=>$model->id]);
			}
		}
		$model->formatDate();
		$this->render('edit', $this->getCompetitionData($model));
	}

	public function actionEvent() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (!$model->checkPermission($this->user)) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect($this->getReferrer());
		}
		if ($model->isPublic() && !$this->user->isAdministrator()) {
			Yii::app()->user->setFlash('danger', '比赛已公示，编辑项目请联系管理员！');
			$this->redirect($this->getReferrer());
		}
		if (isset($_POST['Competition']['associatedEvents'])) {
			if ($model->updateEvents($_POST['Competition']['associatedEvents'])) {
				Yii::app()->user->setFlash('success', '更新比赛项目成功');
				$this->redirect(['/board/competition/event', 'id'=>$model->id]);
			}
		}
		$this->render('event', [
			'model'=>$model,
		]);
	}

	public function actionSchedule() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (!$model->checkPermission($this->user)) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect($this->getReferrer());
		}
		if (isset($_POST['Competition']['schedules'])) {
			$model->schedules = $_POST['Competition']['schedules'];
			if ($model->updateSchedules()) {
				Yii::app()->user->setFlash('success', '更新比赛赛程成功');
				$this->redirect(['/board/competition/schedule', 'id'=>$model->id]);
			}
		}
		$this->render('schedule', [
			'model'=>$model,
		]);
	}

	public function actionEditApplication() {
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		if (!$this->user->isAdministrator() && !isset($model->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
			$this->redirect($this->getReferrer());
		}
		if ($model->application == null) {
			$model->application = new CompetitionApplication();
			$model->application->competition_id = $model->id;
			$model->application->create_time = time();
		}
		if (isset($_POST['CompetitionApplication'])) {
			$model->application->attributes = $_POST['CompetitionApplication'];
			if ($model->application->save()) {
				Yii::app()->user->setFlash('success', '更新申请资料成功');
				$this->redirect($this->getReferrer());
			}
		}
		$this->render('editApplication', [
			'competition'=>$model,
			'model'=>$model->application,
		]);
	}

	private function getCompetitionData($model) {
		$wcaDelegates = array();
		foreach (User::getDelegates(User::IDENTITY_WCA_DELEGATE) as $delegate) {
			$wcaDelegates[$delegate->id] = $delegate->name_zh ?: $delegate->name;
		}
		$ccaDelegates = array();
		foreach (User::getDelegates(User::IDENTITY_CCA_DELEGATE) as $delegate) {
			$ccaDelegates[$delegate->id] = $delegate->name_zh ?: $delegate->name;
		}
		$organizers = User::getOrganizers();
		$types = Competition::getTypes();
		$cities = Region::getAllCities();
		return array(
			'model'=>$model,
			'cities'=>$cities,
			'wcaDelegates'=>$wcaDelegates,
			'ccaDelegates'=>$ccaDelegates,
			'organizers'=>$organizers,
			'types'=>$types,
			'isOrganizerEditable'=>!($this->user->isOrganizer() && $model->isPublic()),
			'user'=>$this->user,
		);
	}

	public function actionToggle() {
		$id = $this->iRequest('id');
		$model = Competition::model()->findByPk($id);
		$attribute = $this->sRequest('attribute');
		if ($model === null) {
			throw new CHttpException(404, 'Not found');
		}
		if (!$this->user->isAdministrator() && $attribute == 'status') {
			throw new CHttpException(401, '未授权');
		}
		$model->formatDate();
		$model->$attribute = 1 - $model->$attribute;
		$model->save();
		$this->ajaxOk(array(
			'value'=>$model->$attribute,
		));
	}

	public function actionConfirm() {
		$id = $this->iRequest('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			throw new CHttpException(404, 'Not found');
		}
		if (!$model->checkPermission($this->user)) {
			throw new CHttpException(401, '未授权');
		}
		if ($model->application === null) {
			throw new CHttpException(403, '该比赛尚未填写申请资料！');
		}
		if (!$model->isUnconfirmed()) {
			throw new CHttpException(401, '未授权的操作');
		}
		$model->formatDate();
		$model->status = Competition::STATUS_CONFIRMED;
		$model->confirm_time = time();
		if ($model->save()) {
			Yii::app()->mailer->sendCompetitionConfirmNotice($model);
			$this->ajaxOk([]);
		} else {
			throw new CHttpException(500, json_encode($model->errors));
		}
	}
}
