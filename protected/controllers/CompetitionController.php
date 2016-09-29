<?php
class CompetitionController extends Controller {

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

	public function init() {
		if (!DEV) {
			Yii::app()->urlManager->setBaseUrl('http://cubingchina.com');
		}
		parent::init();
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
		$this->pageTitle = array($competition->getAttributeValue('name'));
		if (preg_match_all('|<img[^>]+src="([^"]+)"[^>]*>|i', $competition->information_zh, $matches)) {
			$this->setWeiboSharePic($matches[1]);
		}
		$this->setWeiboShareDefaultText($competition->getDescription(), false);
		$this->render('detail', array(
			'competition'=>$competition,
		));
	}

	public function actionIndex() {
		$model = new Competition('search');
		$model->unsetAttributes();
		$model->year = $this->sGet('year', 'current');
		$model->type = $this->sGet('type', '');
		$model->province = $this->sGet('province', '');
		$model->event = $this->sGet('event', '');
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

	public function actionSignin() {
		$code = $this->sGet('code');
		$registration = Registration::model()->findByAttributes(array(
			'code'=>substr($code, 0, 64),
		));
		if ($registration === null) {
			throw new CHttpException(404, 'Error');
		}
		$this->redirect($registration->competition->getUrl());
	}

	public function actionRegistration() {
		$competition = $this->getCompetition();
		$user = $this->getUser();
		$registration = Registration::getUserRegistration($competition->id, $user->id);
		if (!$competition->isPublic() || !$competition->isRegistrationStarted() || $competition->tba) {
			Yii::app()->user->setFlash('info', Yii::t('Competition', 'The registration is not open yet.'));
			$this->redirect($competition->getUrl('competitors'));
		}
		$showRegistration = $registration !== null && $registration->isAccepted();
		if ($competition->isRegistrationEnded() && !$showRegistration) {
			Yii::app()->user->setFlash('info', Yii::t('Competition', 'The registration has been closed.'));
			$this->redirect($competition->getUrl('competitors'));
		}
		if ($competition->isRegistrationFull() && !$showRegistration) {
			Yii::app()->user->setFlash('info', Yii::t('Competition', 'The limited number of competitor has been reached.'));
			$this->redirect($competition->getUrl('competitors'));
		}
		if ($user->isUnchecked()) {
			$this->render('registration403', array(
				'competition'=>$competition,
			));
			Yii::app()->end();
		}
		if ($registration !== null) {
			$registration->formatEvents();
			$this->setWeiboShareDefaultText($competition->getRegistrationDoneWeiboText(), false);
			$this->render('registrationDone', array(
				'user'=>$user,
				'accepted'=>$registration->isAccepted(),
				'competition'=>$competition,
				'registration'=>$registration,
			));
			Yii::app()->end();
		}
		$model = new Registration('register');
		$model->unsetAttributes();
		$model->competition = $competition;
		$model->competition_id = $competition->id;
		if ($competition->isMultiLocation()) {
			$model->location_id = null;
		}
		if (isset($_POST['Registration'])) {
			$model->attributes = $_POST['Registration'];
			$model->user_id = $this->user->id;
			$model->total_fee = $model->getTotalFee(true);
			$model->ip = Yii::app()->request->getUserHostAddress();
			$model->date = time();
			$model->status = Registration::STATUS_WAITING;
			if ($competition->check_person == Competition::NOT_CHECK_PERSON && $competition->online_pay != Competition::ONLINE_PAY) {
				$model->status = Registration::STATUS_ACCEPTED;
			}
			// for FMC Asia
			if ($competition->multi_countries && $model->location->country_id != 1) {
				$model->status = Registration::STATUS_ACCEPTED;
			}
			if ($model->save()) {
				Yii::app()->mailer->sendRegistrationNotice($model);
				$this->setWeiboShareDefaultText($competition->getRegistrationDoneWeiboText(), false);
				$model->formatEvents();
				$this->render('registrationDone', array(
					'user'=>$user,
					'accepted'=>$model->isAccepted(),
					'competition'=>$competition,
					'registration'=>$model,
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
		$userSchedules = [];
		if (!Yii::app()->user->isGuest) {
			$user = $this->getUser();
			$registration = Registration::getUserRegistration($competition->id, $user->id);
			if ($registration !== null) {
				$userSchedules = $competition->getUserSchedules($registration->user);
			}
		}
		$this->render('schedule', array(
			'competition'=>$competition,
			'userSchedules'=>$userSchedules,
		));
	}

	public function actionTravel() {
		$competition = $this->getCompetition();
		$this->render('travel', array(
			'competition'=>$competition,
		));
	}

	protected function getCompetition() {
		$name = $this->sGet('name');
		$competition = Competition::getCompetitionByName($name);
		if ($competition === null || strtolower($name) != strtolower($competition->getUrlName())) {
			throw new CHttpException(404, 'Error');
		}
		// if (!$competition->isPublic() && !Yii::app()->user->checkRole(User::ROLE_ORGANIZER)) {
		// 	throw new CHttpException(404, 'Error');
		// }
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
				$competition->getAttributeValue('name')=>$competition->getUrl(),
				ucfirst($this->action->id),
			);
		} else {
			$this->breadcrumbs = array(
				'Competitions'=>array('/competition/index'),
				$competition->getAttributeValue('name'),
			);
		}
	}

	private function setCompetitionNavibar($competition) {
		$showResults = $competition->hasResults && $this->id != 'live';
		$showLive = $competition->live == Competition::YES && !$competition->canRegister();
		$navibar = array(
			array(
				'label'=>Html::fontAwesome('home', 'a') . Yii::t('Competition', 'Cubing China'),
				'url'=>array('/site/index'),
				'itemOptions'=>array(
					'class'=>'nav-item',
				),
			),
			array(
				'label'=>Html::fontAwesome('info-circle', 'a') . Yii::t('Competition', 'Main Page'),
				'url'=>$competition->getUrl('detail'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-red',
				),
			),
			array(
				'label'=>Html::fontAwesome('tasks', 'a') . Yii::t('Competition', 'Regulations'),
				'url'=>$competition->getUrl('regulations'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-orange',
				),
			),
			array(
				'label'=>Html::fontAwesome('calendar', 'a') . Yii::t('Competition', 'Schedule'),
				'url'=>$competition->getUrl('schedule'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-yellow',
				),
			),
			array(
				'label'=>Html::fontAwesome('taxi', 'a') . Yii::t('Competition', 'Travel'),
				'url'=>$competition->getUrl('travel'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-green',
				),
			),
			array(
				'label'=>Html::fontAwesome('users', 'a') . Yii::t('Competition', 'Competitors'),
				'url'=>$competition->getUrl('competitors'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-blue',
				),
			),
			array(
				'label'=>Html::fontAwesome('sign-in', 'a') . Yii::t('Competition', 'Registration'),
				'url'=>$competition->getUrl('registration'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-white',
				),
				'visible'=>(!$showResults && !$showLive) || $competition->show_qrcode,
			),
			array(
				'label'=>Html::fontAwesome('table', 'a') . Yii::t('Competition', 'Results'),
				'url'=>array('/results/c', 'id'=>$competition->wca_competition_id),
				'itemOptions'=>array(
					'class'=>'nav-item cube-purple',
				),
				'visible'=>$showResults,
			),
			array(
				'label'=>Html::fontAwesome('play', 'a') . Yii::t('Competition', 'Live'),
				'url'=>$competition->getUrl('live'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-pink',
				),
				'visible'=>!$showResults && $showLive,
			),
		);
		$this->navibar = $navibar;
	}
}
