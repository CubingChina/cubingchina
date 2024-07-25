<?php

class CompetitionController extends Controller {

	public function accessRules() {
		return array(
			array(
				'deny',
				'users'=>array('?'),
				'actions'=>array('registration', 'ticket'),
			),
			array(
				'allow',
				'users'=>array('@'),
				'actions'=>array('registration', 'ticket'),
			),
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}

	public function init() {
		if (!DEV) {
			Yii::app()->urlManager->setBaseUrl(Yii::app()->params->baseUrl);
		}
		parent::init();
	}

	public function actionCompetitors() {
		$competition = $this->getCompetition();
		$model = new Registration('search');
		$model->unsetAttributes();
		$model->competition_id = $competition->id;
		$model->competition = $competition;
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

	public function actionScan() {
		$competition = $this->getCompetition();
		$session = Yii::app()->session;
		$scanCode = $this->sRequest('scan_code');
		if ($scanCode) {
			$scanAuth = ScanAuth::model()->findByAttributes([
				'competition_id'=>$competition->id,
				'code'=>$scanCode,
			]);
			if ($scanAuth !== null) {
				$session->add('scan_code', $scanAuth->code);
				$this->redirect($scanAuth->competition->getUrl('scan'));
			}
		}
		if ($session->get('scan_code') === null) {
			if ($competition->checkPermission($this->user)) {
				$session->add('scan_code', 'user_' . $this->user->id);
			} else {
				$this->render('scanAuth', [
					'competition'=>$competition,
				]);
				Yii::app()->end();
			}
		}

		$this->getWechatOfficialAccount([
			'jsConfig'=>[
				'hideAllNonBaseMenuItem',
				'scanQRCode',
			],
		]);
		$min = DEV ? '' : '.min';
		$version = '201809301932';
		$clientScript = Yii::app()->clientScript;
		$clientScript->registerScriptFile('/f/plugins/vue/vue' . $min . '.js');
		$clientScript->registerScriptFile('/f/js/scan.js?ver=' . $version);
		$this->render('scan', [
			'competition'=>$competition,
		]);
	}

	public function actionRegistration() {
		$competition = $this->getCompetition();
		$user = $this->getUser();
		$registration = Registration::getUserRegistration($competition->id, $user->id);
		if (!$competition->isPublic() || $competition->tba || (!$competition->isRegistrationStarted() && !$user->canPriorRegister($competition))) {
			Yii::app()->user->setFlash('info', Yii::t('Competition', 'The registration is not open yet.'));
			$this->redirect($competition->getUrl('detail'));
		}
		$showRegistration = $registration !== null && !$registration->isPending();
		if (!$showRegistration) {
			$flashes = [];
			if ($competition->isRegistrationEnded()) {
				$flashes['info'] = Yii::t('Competition', 'The registration has been closed.');
			}
			if ($competition->isRegistrationFull()) {
				if (time() < $competition->cancellation_end_time) {
					$flashes['info'] = Yii::t('Competition', 'The registration has been paused and it will be restarted after {time}.', [
						'{time}'=>date('Y-m-d H:i:s', $competition->reg_reopen_time),
					]);
				} else {
					$flashes['info'] = Yii::t('Competition', 'The registration has been closed.');
				}
			}
			if ($competition->has_been_full && !$competition->isRegistrationFull() &&  time() < $competition->cancellation_end_time) {
				$flashes['info'] = Yii::t('Competition', 'The registration has been paused and it will be restarted after {time}.', [
					'{time}'=>date('Y-m-d H:i:s', $competition->reg_reopen_time),
				]);
			}
			if ($competition->isRegistrationPaused() && !$competition->isRegistrationFull()) {
				$flashes['info'] = Yii::t('Competition', 'The registration has been paused and it will be restarted after {time}.', [
					'{time}'=>date('Y-m-d H:i:s', $competition->reg_reopen_time),
				]);
			}
			if (!empty($flashes)) {
				foreach ($flashes as $type=>$message) {
					Yii::app()->user->setFlash($type, $message);
				}
			}
			if ($competition->person_num > 0) {
				Yii::app()->user->setFlash('warning', Yii::t('Competition', 'Remaining place{s} for registration: {num}.', [
					'{s}'=>$competition->getRemainedNumber() > 1 ? 's' : '',
					'{num}'=>$competition->getRemainedNumber(),
				]));
			}
			if (!empty($flashes)) {
				$this->redirect($competition->getUrl('competitors'));
			}
		}
		if ($user->isUnchecked()) {
			$this->render('registration403', array(
				'competition'=>$competition,
			));
			Yii::app()->end();
		}
		$unmetEvents = [];
		if ($competition->has_qualifying_time) {
			$unmetEvents = $competition->getUserUnmetEvents($this->user);
		}
		$canRegister = true;
		if ($competition->series) {
			$otherRegistration = $this->user->getOtherSeriesRegistration($competition);
			if ($otherRegistration) {
				$canRegister = false;
				Yii::app()->user->setFlash(
					'danger',
					Yii::t(
						'Registration',
						'You successfully registered for {otherCompetition}. You can only register for one competition among {thisCompetition} and {otherCompetition}. Please cancel your registration for {otherCompetition} to continue.',
						[
							'{otherCompetition}'=>CHtml::link($otherRegistration->competition->getAttributeValue('name'), $otherRegistration->competition->getUrl('registration')),
							'{thisCompetition}'=>CHtml::link($competition->getAttributeValue('name'), $competition->url),
						]
					)
				);
			}
		}
		if ($registration !== null) {
			$overseaUserVerifyForm = new OverseaUserVerifyForm();
			if (isset($_POST['OverseaUserVerifyForm']) && $this->user->country_id > 1) {
				$overseaUserVerifyForm->attributes = $_POST['OverseaUserVerifyForm'];
				if ($overseaUserVerifyForm->validate()) {
					if ($registration->isPending()) {
						$registration->accept();
						if ($registration->isAccepted()) {
							Yii::app()->user->getFlashes();
							Yii::app()->user->setFlash('success', Yii::t('Registration', 'Your registration has been accepted.'));
							$this->redirect($competition->getUrl('registration'));
						}
					} elseif ($registration->isAcceptedOrWaiting()) {
						$payment = $registration->getUnpaidPayment();
						$registration->accept($payment);
						Yii::app()->user->setFlash('success', Yii::t('Registration', 'Your registration has been updated successfully.'));
						$this->redirect($competition->getUrl('registration'));
					}
				}
			}
			if (isset($_POST['cancel']) && $registration->isCancellable()) {
				if ($registration->cancel()) {
					Yii::app()->user->setFlash('success', Yii::t('Registration', 'Your registration has been cancelled.'));
					$this->redirect($competition->getUrl('registration'));
				}
			}
			if (isset($_POST['update']) && $registration->isEditable()) {
				$events = $_POST['Registration']['events'] ?? [];
				if ($registration->isAcceptedOrWaiting() || $events !== []) {
					$registration->updateEvents($events);
					Yii::app()->user->setFlash('success', Yii::t('Registration', 'Your registration has been updated successfully.'));
					$this->redirect($competition->getUrl('registration'));
				}
			}
			if (isset($_POST['reset'])) {
				if ($registration->resetPayment()) {
					Yii::app()->user->setFlash('success', Yii::t('Registration', 'Your order has been reset successfully.'));
					$this->redirect($competition->getUrl('registration'));
				}
			}
			if (($payment = $registration->getUnpaidPayment()) != null) {
				$payment->reviseAmount();
			}
			$this->getWechatOfficialAccount([
				'jsConfig'=>[
					'chooseWXPay',
				],
			]);
			$this->render('registrationDone', array(
				'user'=>$user,
				'competition'=>$competition,
				'registration'=>$registration,
				'overseaUserVerifyForm'=>$overseaUserVerifyForm,
				'unmetEvents'=>$unmetEvents,
				'canRegister'=>$canRegister,
			));
			Yii::app()->end();
		}
		$model = new Registration('register');
		// $model->unsetAttributes();
		$model->competition = $competition;
		$model->competition_id = $competition->id;
		$model->events = array_values(PreferredEvent::getUserEvents($user));
		if ($competition->shouldDisableUnmetEvents) {
			$model->events = array_diff($model->events, array_keys($unmetEvents));
		}
		if ($competition->isMultiLocation()) {
			$model->location_id = null;
		}
		if (isset($_POST['Registration']) && $canRegister) {
			if (!$competition->fill_passport || $this->user->passport_type != User::NO) {
				$model->attributes = $_POST['Registration'];
				if (!isset($_POST['Registration']['events'])) {
					$model->events = null;
				}
				if ($competition->shouldDisableUnmetEvents) {
					$model->events = array_diff($model->events, array_keys($unmetEvents));
				}
				$model->user_id = $this->user->id;
				$model->total_fee = $model->getTotalFee(true);
				$model->ip = Yii::app()->request->getUserHostAddress();
				$model->date = time();
				$model->status = Registration::STATUS_PENDING;
				if ($competition->auto_accept == Competition::YES && $competition->online_pay != Competition::ONLINE_PAY) {
					$model->status = Registration::STATUS_ACCEPTED;
				}
				// for FMC Asia
				if ($competition->multi_countries && $model->location->country_id != 1) {
					$model->status = Registration::STATUS_ACCEPTED;
				}
				if ($model->save()) {
					$model->updateEvents($model->events);
					Yii::app()->mailer->sendRegistrationNotice($model);
					if ($model->isAccepted()) {
						$model->accept();
					}
					$this->redirect($competition->getUrl('registration'));
				}
			}
		}
		$this->render('registration', array(
			'competition'=>$competition,
			'model'=>$model,
			'unmetEvents'=>$unmetEvents,
			'canRegister'=>$canRegister,
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
			$userId = $user->id;
			if ($competition->checkPermission($user)) {
				$userId = $this->iGet('user_id', $userId);
				$number = $this->iGet('number');
				if ($number) {
					$registrations = Registration::getRegistrations($competition);
					$registration = $registrations[$number - 1] ?? null;
				}
			}
			$registration = $registration ?? Registration::getUserRegistration($competition->id, $userId);
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
			'showMap'=>true,
		));
	}

	public function actionTicket() {
		$competition = $this->getCompetition();
		$user = $this->getUser();
		$registration = Registration::getUserRegistration($competition->id, $user->id);
		$tickets = $competition->tickets;
		if ($tickets !== [] && $tickets[0]->competitors_only && ($registration === null || !$registration->isAccepted())) {
			Yii::app()->user->setFlash('danger', Yii::t('Competition', 'Only competitors can buy tickets.'));
			$this->redirect($competition->getUrl());
		}
		$id = $this->iGet('id');
		if ($id && ($userTicket = UserTicket::model()->findByPk($id)) !== null && $userTicket->isEditable()) {
			if ($user->isAdministrator() || $userTicket->user_id == $user->id) {
				$userTicket->repeatPassportNumber = $userTicket->passport_number;
				$userTicket->setScenario('edit');
				if (isset($_POST['UserTicket'])) {
					foreach (['name', 'passport_type', 'passport_number', 'passport_name', 'repeatPassportNumber'] as $attribute) {
						if (isset($_POST['UserTicket'][$attribute])) {
							$userTicket->$attribute = $_POST['UserTicket'][$attribute];
						}
					}
					if ($userTicket->save()) {
						Yii::app()->user->setFlash('success', Yii::t('Competition', 'Update ticket info successfully.'));
						$this->redirect($competition->getUrl('ticket', ['id'=>$id]));
					}
				}
				$this->getWechatOfficialAccount([
					'jsConfig' => [
						'chooseWXPay',
					],
				]);
				$this->render('editTicket', [
					'user'=>$user,
					'competition'=>$competition,
					'model'=>$userTicket,
				]);
				Yii::app()->end();
			}
		}
		$model = new UserTicket();
		$model->unsetAttributes();
		$model->user_id = $this->user->id;
		$model->user = $this->user;
		if ($model->hasDiscount($competition)) {
			$model->discount = Ticket::CHILDREN_DISCOUNT;
		}
		if (isset($_POST['UserTicket'])) {
			$model->attributes = $_POST['UserTicket'];
			$model->calculateFee();
			if ($model->ticket->purchase_limit > 0) {
				$userTickets = $this->user->getTickets($competition, UserTicket::STATUS_PAID);
				if (count($userTickets) >= $model->ticket->purchase_limit) {
					Yii::app()->user->setFlash('danger', Yii::t('Competition', 'You can only buy {num} tickets.', [
						'{num}'=>$model->ticket->purchase_limit,
					]));
					$this->redirect($competition->getUrl('ticket'));
				}
			}
			if ($model->save()) {
				$model->createPayment();
				$this->redirect($competition->getUrl('ticket', ['id'=>$model->id]));
			}
		}
		$this->render('ticket', [
			'user'=>$user,
			'competition'=>$competition,
			'tickets'=>$competition->tickets,
			'model'=>$model,
		]);
	}

	protected function getCompetition() {
		$alias = $this->sGet('alias');
		$competition = Competition::getCompetitionByName($alias);
		if ($competition === null || strtolower($alias) != strtolower($competition->getUrlName())) {
			throw new CHttpException(404, 'Error');
		}
		if (!$competition->isPublicVisible() && !$competition->checkPermission($this->user)) {
			throw new CHttpException(404, 'Error');
		}
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
		$showOthers = !$competition->special;
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
				'visible'=>$showOthers,
			),
			array(
				'label'=>Html::fontAwesome('calendar', 'a') . Yii::t('Competition', 'Schedule'),
				'url'=>$competition->getUrl('schedule'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-yellow',
				),
				'visible'=>$showOthers,
			),
			array(
				'label'=>Html::fontAwesome('taxi', 'a') . Yii::t('Competition', 'Travel'),
				'url'=>$competition->getUrl('travel'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-green',
				),
				'visible'=>$showOthers,
			),
			array(
				'label'=>Html::fontAwesome('users', 'a') . Yii::t('Competition', 'Competitors'),
				'url'=>$competition->getUrl('competitors'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-blue',
				),
			),
			array(
				'label'=>Html::fontAwesome('sign-in', 'a') . Yii::t('Competition', 'Ticket'),
				'url'=>$competition->getUrl('ticket'),
				'itemOptions'=>array(
					'class'=>'nav-item cube-indigo',
				),
				'visible'=>$competition->tickets !== [] && ($competition->end_date ?: $competition->date) > time() - 86400,
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
