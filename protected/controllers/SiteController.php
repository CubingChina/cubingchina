<?php

class SiteController extends Controller {

	public function filters() {
		return array(
			'accessControl',
			// array(
			// 	'COutputCache + page, index',
			// 	'duration'=>3600,
			// 	'varyByParam'=>array('view'),
			// ),
		);
	}

	public function accessRules() {
		return array(
			array(
				'deny',
				'users'=>array('?'),
				'actions'=>array('reactivate'),
			),
			array(
				'allow',
				'users'=>array('@'),
				'actions'=>array('reactivate'),
			),
			array(
				'allow',
				'users'=>array('*'),
			),
		);
	}
	/**
	 * Declares class-based actions.
	 */
	public function actions() {
		return array_merge(parent::actions(), array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'foreColor'=>0x6091ba,
				'backColor'=>0xFFFFFF,
				'testLimit'=>1,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		));
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex() {
		$news = new News('search');
		$news->status = News::STATUS_SHOW;
		$upcomingCompetitions = Competition::getUpcomingCompetitions(8);
		$this->render('index', array(
			'news'=>$news,
			'upcomingCompetitions'=>$upcomingCompetitions,
		));
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError() {
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest) {
				echo $error['message'];
			} else {
				$this->title = 'Error ' . $error['code'];
				$this->pageTitle = array($error['code'] === 404 ? 'Not found' : 'Something goes wrong');
				$this->render('error', $error);
			}
		} else {
			throw new CHttpException(500);
		}
	}

	public function actionBanned() {
		$this->render('banned');
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin() {
		if (!Yii::app()->user->isGuest) {
			$this->redirect(Yii::app()->homeUrl);
		}
		$referrer = Yii::app()->request->urlReferrer;
		$hostInfo = Yii::app()->request->hostInfo;
		if (strpos($referrer, $hostInfo) !== false && strpos($referrer, 'login') === false && Yii::app()->user->returnUrl == '') {
			Yii::app()->user->returnUrl = $referrer;
		}

		$model = new LoginForm();

		// collect user input data
		if (isset($_POST['LoginForm'])) {
			$model->attributes = $_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if ($model->validate() && $model->login()) {
				$this->redirect(Yii::app()->user->returnUrl);
			}
		}

		$this->pageTitle = array('Login');
		// display the login form
		$this->render('login', array(
			'model'=>$model
		));
	}

	public function actionWechatLogin() {
		$dev = $this->iGet('dev');
		if ($dev && !DEV) {
			$this->redirect('https://staging.cubingchina.com/site/wechatLogin?' . http_build_query($_GET));
		}
		try {
			$officialAccount = $this->getWechatOfficialAccount(['oauth'=>[]]);
			$user = $officialAccount->oauth->userFromCode($this->sGet('code'));
			$wechatUser = WechatUser::getOrCreate($user);
			$session = Yii::app()->session;
			$session->add(Constant::WECHAT_SESSION_KEY, $user);
			if ($wechatUser->user) {
				$userIdentify = new UserIdentity($wechatUser->user->email, $wechatUser->user->password);
				$userIdentify->ID = $wechatUser->user->id;
				Yii::app()->user->login($userIdentify, 30 * 86400);
			}
			$this->redirect($session->get(Constant::CURRENT_URL_KEY) ?: Yii::app()->homeUrl);
		} catch (Exception $e) {
			$this->redirect(Yii::app()->homeUrl);
		}
	}

	public function actionRegister() {
		$this->renderArchiveClosed();
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout() {
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

	public function actionResetPassword() {
		$this->renderArchiveClosed();
	}

	public function actionActivate() {
		$model = new ActionFormModel();
		if (!$model->checkCode()) {
			$this->redirect(Yii::app()->homeUrl);
		}
		$user = $model->getUserAction()->user;
		if ($user->role == User::ROLE_UNCHECKED) {
			$user->role = User::ROLE_CHECKED;
		}
		if (!$user->save()) {
			throw new CHttpException(500);
		}
		$model->clear();
		$this->pageTitle = array('Activated Successful');
		$this->render('activate', array(
			'model'=>$model,
		));
	}

	public function actionReactivate() {
		$this->renderArchiveClosed();
	}

	public function actionForgetPassword() {
		$this->renderArchiveClosed();
	}

	public function actionBaiduMap() {
		$this->layout = '/layouts/simple';
		$this->render('baiduMap');
	}

	public function actionBaiduMapSearch() {
		$this->layout = '/layouts/simple';
		$this->render('baiduMapSearch');
	}
}
