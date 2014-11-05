<?php
class CompetitionController extends AdminController {
	public function actionIndex() {
		$model = new Competition();
		$model->unsetAttributes();
		$model->attributes = $this->aRequest('Competition');
		$this->render('index', array(
			'model'=>$model,
		));
	}

	public function actionAdd() {
		if ($this->user->isOrganizer() && Competition::getUnpublicCount() >= 2) {
			Yii::app()->user->setFlash('danger', '仅可同时创建两场比赛，如有疑问，请与管理员联系 admin@cubingchina.com');
			$this->redirect(array('/board/competition/index'));
		}
		$model = new Competition();
		$model->date = $model->end_date = $model->reg_start = $model->reg_end = '';
		$model->province_id = $model->city_id = '';
		// $model->unsetAttributes();
		if (isset($_POST['Competition'])) {
			$model->attributes = $_POST['Competition'];
			if ($model->save()) {
				if ($this->user->isOrganizer()) {
					Yii::app()->mailer->sendAddCompetitionNotice($model);
				}
				Yii::app()->user->setFlash('success', '新加比赛成功');
				$this->redirect(array('/board/competition/index'));
			}
			$model->formatSchedule();
		}
		if ($this->user->isOrganizer()) {
			$organizer = new CompetitionOrganizer();
			$organizer->organizer_id = $this->user->id;
			$organizer->user = $this->user;
			$model->organizer = array(
				$organizer,
			);
		}
		$model->formatEvents();
		$model->formatDate();
		$wcaDelegates = User::getDelegates(User::IDENTITY_WCA_DELEGATE);
		$ccaDelegates = User::getDelegates(User::IDENTITY_CCA_DELEGATE);
		$organizers = User::getOrganizers();
		$types = Competition::getTypes();
		$checkPersons = Competition::getCheckPersons();
		$normalEvents = Events::getNormalEvents();
		$otherEvents = Events::getOtherEvents();
		$cities = Region::getAllCities();
		$this->render('edit', array(
			'model'=>$model,
			'normalEvents'=>$normalEvents,
			'otherEvents'=>$otherEvents,
			'cities'=>$cities,
			'wcaDelegates'=>$wcaDelegates,
			'ccaDelegates'=>$ccaDelegates,
			'organizers'=>$organizers,
			'types'=>$types,
			'checkPersons'=>$checkPersons,
		));
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
		// if ($this->user->isOrganizer() && $model->isPublic()) {
		// 	Yii::app()->user->setFlash('warning', '该比赛已公示，编辑请联系代表');
		// 	$this->redirect($this->getReferrer());
		// }
		// $model->unsetAttributes();
		$cannotEditAttr = array(
			'name',
			'name_zh',
			'type',
			// 'province_id',
			// 'city_id',
			'date',
			'end_date',
			'delegates',
			'locations',
			// 'venue',
			// 'venue_zh',
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
				$model->date = date('Y-m-d', $model->date);
				$model->end_date = date('Y-m-d', $model->end_date);
			}
			// CVarDumper::dump($model->attributes, 10, 1);exit;
			if ($model->save()) {
				Yii::app()->user->setFlash('success', '更新比赛信息成功');
				$this->redirect($this->getReferrer());
			}
			$model->formatSchedule();
		}
		$model->formatEvents();
		$model->formatDate();
		$wcaDelegates = User::getDelegates(User::IDENTITY_WCA_DELEGATE);
		$ccaDelegates = User::getDelegates(User::IDENTITY_CCA_DELEGATE);
		$organizers = User::getOrganizers();
		$types = Competition::getTypes();
		$checkPersons = Competition::getCheckPersons();
		$normalEvents = Events::getNormalEvents();
		$otherEvents = Events::getOtherEvents();
		$cities = Region::getAllCities();
		if ($this->user->isOrganizer() && $model->isPublic()) {
			Yii::app()->user->setFlash('warning', '该比赛已公示，名字、时间等部分信息不能修改，如需修改请联系管理员');
		}
		$this->render('edit', array(
			'model'=>$model,
			'normalEvents'=>$normalEvents,
			'otherEvents'=>$otherEvents,
			'cities'=>$cities,
			'wcaDelegates'=>$wcaDelegates,
			'ccaDelegates'=>$ccaDelegates,
			'organizers'=>$organizers,
			'types'=>$types,
			'checkPersons'=>$checkPersons,
		));
	}

	public function actionShow() {
		if ($this->user->isOrganizer()) {
			throw new CHttpException(403, '权限不足');
		}
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatEvents();
		$model->formatDate();
		$model->status = Competition::STATUS_SHOW;
		$model->save();
		Yii::app()->user->setFlash('success', '公示比赛成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	public function actionHide() {
		if ($this->user->isOrganizer()) {
			throw new CHttpException(403, '权限不足');
		}
		$id = $this->iGet('id');
		$model = Competition::model()->findByPk($id);
		if ($model === null) {
			$this->redirect(Yii::app()->request->urlReferrer);
		}
		$model->formatEvents();
		$model->formatDate();
		$model->status = Competition::STATUS_HIDE;
		$model->save();
		Yii::app()->user->setFlash('success', '隐藏比赛成功');
		$this->redirect(Yii::app()->request->urlReferrer);
	}
}
