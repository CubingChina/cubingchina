<?php

class CompetitionController extends AdminController {

	public function accessRules() {
		return array(
			array(
				'allow',
				'actions'=>array('index', 'add', 'edit'),
				'users'=>array('@'),
			),
			array(
				'allow',
				'actions'=>array('toggle'),
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

	public function actionAdd() {
		$user = $this->user;
		if (!$user->isAdministrator() && Competition::getUnacceptedCount($user) >= 1) {
			Yii::app()->user->setFlash('danger', '如需申请更多比赛，请与管理员联系 admin@cubingchina.com');
			$this->redirect(array('/board/competition/index'));
		}
		$model = new Competition();
		$model->date = $model->end_date = $model->reg_start = $model->reg_end = '';
		$model->province_id = $model->city_id = '';
		if (isset($_POST['Competition'])) {
			$model->attributes = $_POST['Competition'];
			if ($model->save()) {
				if ($user->isOrganizer()) {
					Yii::app()->mailer->sendAddCompetitionNotice($model);
				}
				Yii::app()->user->setFlash('success', '新加比赛成功');
				$this->redirect(array('/board/competition/index'));
			}
			$model->formatSchedule();
		}
		if ($user->isAdministrator()) {
			$organizer = new CompetitionOrganizer();
			$organizer->organizer_id = $user->id;
			$organizer->user = $user;
			$model->organizer = array(
				$organizer,
			);
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
		if ($this->user->isOrganizer() && !isset($model->organizers[$this->user->id])) {
			Yii::app()->user->setFlash('danger', '权限不足！');
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
		if ($this->user->isOrganizer() && $attribute == 'status') {
			throw new CHttpException(401, 'Unauthorized');
		}
		$model->formatEvents();
		$model->formatDate();
		$model->$attribute = 1 - $model->$attribute;
		$model->save();
		$this->ajaxOk(array(
			'value'=>$model->$attribute,
		));
	}
}
