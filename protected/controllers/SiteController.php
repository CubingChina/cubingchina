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
		try {
			$application = $this->getWechatApplication(['oauth'=>[]]);
			$user = $application->oauth->user();
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
		$session = Yii::app()->session;
		$step = $session->get('registerStep', 1);
		$userStep = $this->iGet('step', 1);
		if (!Yii::app()->user->isGuest && $step !== 3) {
			$this->redirect(Yii::app()->homeUrl);
		}
		if ($userStep < $step) {
			$step = min($userStep, 1);
			Yii::app()->session->remove(RegisterForm::REGISTER_WCAID);
		}
		$model = new RegisterForm('step' . $step);
		$model->step = $step;
		$model->loadData();

		// collect user input data
		if (isset($_POST['RegisterForm'])) {
			$model->attributes = $_POST['RegisterForm'];
			// validate user input and redirect to the previous page if valid
			if ($model->validate()) {
				$session->add('registerStep', ++$step);
				if ($model->isLastStep()) {
					if (!$model->register()) {
						throw new CHttpException(500, Yii::t('common', 'Something goes wrong'));
					}
				}
				$this->redirect(array('/site/register', 'step'=>$step));
			}
			if (ctype_digit($model->birthday)) {
				$model->birthday = date($model::$dateFormat, $model->birthday);
			}
		}
		$this->pageTitle = array('Register');
		$model->verifyCode = '';
		$this->title = 'Register';
		$this->render('register' . $step, array(
			'model'=>$model,
			'step'=>$step,
		));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout() {
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

	public function actionResetPassword() {
		$model = new ResetPasswordForm();
		if (!$model->checkCode()) {
			$this->redirect(Yii::app()->homeUrl);
		}

		// collect user input data
		if(isset($_POST['ResetPasswordForm'])) {
			$model->attributes = $_POST['ResetPasswordForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate()) {
				if($model->changePassword()) {
					$model->clear();
					Yii::app()->user->logout(false);
					Yii::app()->user->setFlash('success', Yii::t('common', 'Password is changed successfully. Log in with your new password.'));
					$this->redirect(array('/site/login'));
				} else {
					Yii::app()->user->setFlash('error', Yii::t('common', 'Something goes wrong'));
				}
			}
		}
		$this->pageTitle = array('Reset Password');
		$this->render('resetPassword', array(
			'model'=>$model,
		));
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
		$user = $this->getUser();
		if (!$user->isUnchecked()) {
			$this->redirect(Yii::app()->homeUrl);
		}
		$model = new ActivateForm();
		$model->email = $user->email;
		if (isset($_GET['done'])) {
			if (isset($_SESSION['reactivateDone'])) {
				unset($_SESSION['reactivateDone']);
				$this->render('reactivateDone', array(
					'model'=>$model,
				));
				Yii::app()->end();
			} else {
				$this->redirect(array('/site/reactivate'));
			}
		}
		if (isset($_POST['ActivateForm'])) {
			$model->attributes = $_POST['ActivateForm'];
			if ($model->validate() && $model->sendMail()) {
				$_SESSION['reactivateDone'] = true;
				$this->redirect(array('/site/reactivate', 'done'=>1));
			}
		}
		$this->pageTitle = array('Activate Account');
		$this->render('reactivate', array(
			'model'=>$model,
		));
	}

	public function actionForgetPassword() {
		$model = new ForgetPasswordForm();
		if (isset($_GET['done'])) {
			if (isset($_SESSION['forgetPasswordDone'])) {
				unset($_SESSION['forgetPasswordDone']);
				$this->render('forgetPasswordDone', array(
					'model'=>$model,
				));
				Yii::app()->end();
			} else {
				$this->redirect(array('/site/forgetPassword'));
			}
		}

		if (!Yii::app()->user->isGuest) {
			$model->email = Yii::app()->user->name;
		}

		if (isset($_POST['ForgetPasswordForm'])) {
			$model->attributes = $_POST['ForgetPasswordForm'];
			if ($model->validate() && $model->sendMail()) {
				$_SESSION['forgetPasswordDone'] = true;
				$this->redirect(array('/site/forgetPassword', 'done'=>1));
			}
		}

		$this->pageTitle = array('Forget Password');
		$this->render('forgetPassword', array(
			'model'=>$model,
		));
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
