<?php
class CompetitionController extends Controller {

	public function filters() {
		return array(
			'accessControl',
			// array(
			// 	'COutputCache - registration',
			// 	'duration'=>3600,
			// 	'varyByLanguage'=>true,
			// 	'varyByParam'=>array('name', 'sort'),
			// ),
		);
	}
	public function accessRules() {
		return array(
			array(
				'deny',
				'users'=>array('?'),
				'actions'=>array('registration'),
			),
			array(
				'allow',
				'users'=>array('@'),
				'actions'=>array('registration'),
			),
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}
	public function actionCompetitors() {
		$competition = $this->getCompetition();
		$model = new Registration('search');
		$model->unsetAttributes();
		$model->competition_id = $competition->id;
		$model->status = Registration::STATUS_ACCEPTED;
		$this->render('competitors', array(
			'model'=>$model,
			'competition'=>$competition,
		));
	}
	public function actionDetail() {
		$competition = $this->getCompetition();
		$this->pageTitle = array($competition->getAttribute($this->getAttributeName('name')));
		$this->setWeiboShareDefaultText($competition->getDescription(), false);
		$this->render('detail', array(
			'competition'=>$competition,
		));
	}
	public function actionIndex() {
		$model = new Competition('search');
		$model->unsetAttributes();
		$model->status = Competition::STATUS_SHOW;
		$this->title = 'Competition List';
		$this->pageTitle = array('Competition List');
		$this->appendKeywords('Competition List');
		$this->breadcrumbs = array(
			'Competitions',
		);
		$this->render('index', array(
			'model'=>$model,
		));
	}
	public function actionRegistration() {
		$competition = $this->getCompetition();
		$user = $this->getUser();
		$registration = Registration::getUserRegistration($competition->id, $user->id);
		if ($competition->isRegistrationEnded() && $registration === null) {
			Yii::app()->user->setFlash('info', Yii::t('Competition', 'The registration has been closed.'));
			$this->redirect($competition->getUrl('competitors'));
		}
		if ($user->isUnchecked()) {
			$this->render('registration403', array(
				'competition'=>$competition,
			));
			Yii::app()->end();
		}
		if ($registration !== null) {
			$this->setWeiboShareDefaultText($competition->getRegistrationDoneWeiboText(), false);
			$this->render('registrationDone', array(
				'user'=>$user,
				'accepted'=>$registration->isAccepted(),
				'competition'=>$competition,
			));
			Yii::app()->end();
		}
		$model = new Registration();
		if (isset($_POST['Registration'])) {
			$model->attributes = $_POST['Registration'];
			$model->user_id = $this->user->id;
			$model->competition_id = $competition->id;
			$model->date = time();
			$model->status = Registration::STATUS_WAITING;
			if ($competition->check_person == Competition::NOT_CHECK_PERSON) {
				$model->status = Registration::STATUS_ACCEPTED;
			}
			if ($model->save()) {
				$this->setWeiboShareDefaultText($competition->getRegistrationDoneWeiboText(), false);
				$this->render('registrationDone', array(
					'user'=>$user,
					'accepted'=>$model->isAccepted(),
					'competition'=>$competition,
				));
				Yii::app()->end();
			}
		}
		$model->formatEvents();
		$this->render('registration', array(
			'competition'=>$competition,
			'model'=>$model,
		));
	}
	public function actionRegulations() {
		$competition = $this->getCompetition();
		$this->render('regulations', array(
			'competition'=>$competition,
		));
	}
	public function actionSchedule() {
		$competition = $this->getCompetition();
		$this->render('schedule', array(
			'competition'=>$competition,
		));
	}
	public function actionTravel() {
		$competition = $this->getCompetition();
		$this->render('travel', array(
			'competition'=>$competition,
		));
	}

	private function getCompetition() {
		$name = $this->sGet('name');
		$competition = Competition::getCompetitionByName($name);
		if ($competition === null || $name != $competition->getUrlName()) {
			throw new CHttpException(404, 'Error');
		}
		if (!$competition->isPublic() && !Yii::app()->user->checkAccess(User::ROLE_ORGANIZER)) {
			throw new CHttpException(404, 'Error');
		}
		$competition->formatEvents();
		$this->setCompetitionNavibar($competition);
		$this->setCompetitionBreadcrumbs($competition);
		$name = $competition->getAttributeValue('name');
		if ($this->action->id === 'detail') {
			$this->title = $name;
		} else {
			$this->title = $name . '-' . Yii::t('common', ucfirst($this->action->id));
		}
		$this->pageTitle = array($name, ucfirst($this->action->id));
		$this->appendKeywords($name);
		$this->setDescription($competition->getDescription());
		return $competition;
	}

	private function setCompetitionBreadcrumbs($competition) {
		if ($this->action->id !== 'detail') {
			$this->breadcrumbs = array(
				'Competitions'=>array('/competition/index'),
				$competition->getAttribute($this->getAttributeName('name'))=>$competition->getUrl(),
				ucfirst($this->action->id),
			);
		} else {
			$this->breadcrumbs = array(
				'Competitions'=>array('/competition/index'),
				$competition->getAttribute($this->getAttributeName('name')),
			);
		}
	}

	private function setCompetitionNavibar($competition) {
		$navibar = array(
			array(
				'label'=>'<i class="fa fa-home"></i> ' . Yii::t('Competition', 'Cubing China'),
				'url'=>array('/site/index'),
				'itemOptions'=>array(
					'class'=>'nav-item',
				),
			),
			array(
				'label'=>'<i class="fa fa-info-circle"></i> ' . Yii::t('Competition', 'Main Page'),
				'url'=>$competition->getUrl('detail'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-red',
				),
			),
			array(
				'label'=>'<i class="fa fa-calendar"></i> ' . Yii::t('Competition', 'Schedule'),
				'url'=>$competition->getUrl('schedule'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-orange',
				),
			),
			array(
				'label'=>'<i class="fa fa-taxi"></i> ' . Yii::t('Competition', 'Travel'),
				'url'=>$competition->getUrl('travel'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-yellow',
				),
			),
			array(
				'label'=>'<i class="fa fa-tasks"></i> ' . Yii::t('Competition', 'Regulations'),
				'url'=>$competition->getUrl('regulations'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-green',
				),
			),
			array(
				'label'=>'<i class="fa fa-users"></i> ' . Yii::t('Competition', 'Competitors'),
				'url'=>$competition->getUrl('competitors'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-blue',
				),
			),
			array(
				'label'=>'<i class="fa fa-sign-in"></i> ' . Yii::t('Competition', 'Registration'),
				'url'=>$competition->getUrl('registration'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-white',
				),
			),
		);
		$this->navibar = $navibar;
	}
}
