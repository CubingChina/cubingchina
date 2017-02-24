<?php

class CompetitionController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'actions'=>array('index', 'application', 'apply', 'edit', 'editApplication', 'view', 'confirm'),
				'users'=>array('@'),
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
		$model->formatEvents();
		if (isset($_POST['Competition']) && $this->user->isAdministrator()) {
			$model->attributes = $_POST['Competition'];
			$model->formatDate();
			if ($model->save()) {
				switch ($model->isAccepted()) {
					case true:
						Yii::app()->user->setFlash('success', '通过比赛成功');
						$this->redirect(['/board/competition/index']);
						break;
					case false:
						Yii::app()->user->setFlash('success', '拒绝/驳回比赛成功');
						$this->redirect(['/board/competition/application']);
						break;
				}
			}
			$this->handleDate();
		}
		$this->render('view', [
			'competition'=>$model,
		]);
	}

	public function actionApply() {
		$user = $this->user;
		if (!$user->isAdministrator() && Competition::getUnacceptedCount($user) >= 1) {
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
			$model->formatSchedule();
		}
		$model->formatEvents();
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
		$cannotEditAttr = array(
			'name',
			'name_zh',
			'check_person',
			'type',
			'wca_competition_id',
			'entry_fee',
			'online_pay',
			'person_num',
			'second_stage_date',
			'second_stage_ratio',
			'second_stage_all',
			'third_stage_date',
			'third_stage_ratio',
			'date',
			'end_date',
			'reg_start',
			'reg_end',
			'delegates',
			'locations',
			'events',
		);
		if (isset($_POST['Competition'])) {
			foreach ($cannotEditAttr as $attr) {
				$$attr = $model->$attr;
			}
			$model->attributes = $_POST['Competition'];
			if ($this->user->isOrganizer() && $model->isPublic()) {
				foreach ($cannotEditAttr as $attr) {
					$model->$attr = $$attr;
				}
				$model->formatEvents();
				$model->formatDate();
			}
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新比赛信息成功');
				$this->redirect($this->getReferrer());
			}
			$model->formatSchedule();
		}
		$model->formatEvents();
		$model->formatDate();
		$this->render('edit', $this->getCompetitionData($model));
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
		$checkPersons = Competition::getCheckPersons();
		$normalEvents = Events::getNormalEvents();
		$otherEvents = Events::getOtherEvents();
		$cities = Region::getAllCities();
		return array(
			'model'=>$model,
			'normalEvents'=>$normalEvents,
			'otherEvents'=>$otherEvents,
			'cities'=>$cities,
			'wcaDelegates'=>$wcaDelegates,
			'ccaDelegates'=>$ccaDelegates,
			'organizers'=>$organizers,
			'types'=>$types,
			'checkPersons'=>$checkPersons,
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
		$model->formatEvents();
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
		$model->formatEvents();
		$model->formatDate();
		$model->status = Competition::STATUS_CONFIRMED;
		if ($model->save()) {
			Yii::app()->mailer->sendCompetitionConfirmNotice($model);
			$this->ajaxOk([]);
		} else {
			throw new CHttpException(500, json_encode($model->errors));
		}
	}
}
