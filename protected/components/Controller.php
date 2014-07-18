<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController {
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout = '/layouts/main';
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs = array();
	protected $captchaAction = 'site/captcha';
	protected $zh2Hant;
	protected $logAction = true;
	private $_user;
	private $_description;
	private $_title;
	private $_navibar;
	private $_weiboShareDefaultText;
	private $_weiboSharePic;

	public function filters() {
		return array(
			'accessControl',
		);
	}

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('@'),
			),
			array(
				'deny',
				'users'=>array('*'),
			),
		);
	}

	public function getAttributeName($name = 'name') {
		if (Yii::app()->language{0} == 'z' && Yii::app()->language{1} == 'h') {
			$name .= '_zh';
		}
		return $name;
	}

	public function getNavibar() {
		if ($this->_navibar === null) {
			$this->setNavibar(array(
				array(
					'label'=>'<i class="fa fa-home"></i> ' . Yii::t('common', 'Home'),
					'url'=>array('/site/index'),
					'itemOptions'=>array(
						'class'=>'nav-item',
					),
				),
				array(
					'label'=>'<i class="fa fa-cubes"></i> ' . Yii::t('common', 'Competitions') . ' <i class="fa fa-angle-down"></i>',
					'url'=>array('/competition/index'),
					'itemOptions'=>array(
						'class'=>'nav-item dropdown',
					),
					'linkOptions'=>array(
						'class'=>'dropdown-toggle',
						// 'data-toggle'=>'dropdown',
						'data-hover'=>'dropdown',
						'data-delay'=>0,
						'data-close-others'=>'false',
					),
					'items'=>Competition::getRecentCompetitionsForNavibar(),
				),
				// array(
				// 	'label'=>Yii::t('common', 'Statistics'),
				// 	'url'=>array('/statistics/index'),
				// 	'itemOptions'=>array(
				// 		'class'=>'nav-item',
				// 	),
				// ),
				array(
					'label'=>'<i class="fa fa-info-circle"></i> ' . Yii::t('common', 'More Info') . ' <i class="fa fa-angle-down"></i>',
					'url'=>'#',
					'itemOptions'=>array(
						'class'=>'nav-item dropdown',
					),
					'linkOptions'=>array(
						'class'=>'dropdown-toggle',
						'data-toggle'=>'dropdown',
						'data-hover'=>'dropdown',
						'data-delay'=>0,
						'data-close-others'=>'false',
					),
					'items'=>array(
						array(
							'url'=>array('/site/page', 'view'=>'about'),
							'label'=>Yii::t('common', 'About'),
						),
						array(
							'url'=>array('/site/page', 'view'=>'contact'),
							'label'=>Yii::t('common', 'Contact'),
						),
						array(
							'url'=>array('/site/page', 'view'=>'links'),
							'label'=>Yii::t('common', 'Links'),
						),
					),
				),
			));
		}
		return $this->_navibar;
	}

	public function setNavibar($navibar) {
		$navibar = array_merge($navibar, array(
			array(
				'label'=>sprintf('<i class="fa fa-user"></i> %s <i class="fa fa-angle-down"></i>', Yii::app()->user->isGuest ? '' : $this->user->getAttributeValue('name')),
				'url'=>'#',
				'active'=>$this->id === 'user',
				'itemOptions'=>array(
					'class'=>'nav-item dropdown',
				),
				'linkOptions'=>array(
					'class'=>'dropdown-toggle',
					'data-toggle'=>'dropdown',
					'data-hover'=>'dropdown',
					'data-delay'=>0,
					'data-close-others'=>'false',
				),
				'items'=>array(
					array(
						'label'=>Yii::t('common', 'Profile'),
						'url'=>array('/user/profile'),
					),
					array(
						'label'=>Yii::t('common', 'My Competitions'),
						'url'=>array('/user/competitions'),
					),
					array(
						'label'=>Yii::t('common', 'Board'),
						'url'=>array('/board/competition/index'),
						'visible'=>Yii::app()->user->checkAccess(User::ROLE_ORGANIZER),
					),
					array(
						'label'=>Yii::t('common', 'Logout'),
						'url'=>array('/site/logout'),
					),
				),
				'visible'=>!Yii::app()->user->isGuest,
			),
			array(
				'label'=>Yii::t('common', 'Login'),
				'url'=>array('/site/login'),
				'itemOptions'=>array(
					'class'=>'nav-item visible-sm visible-xs',
				),
				'visible'=>Yii::app()->user->isGuest,
			),
			array(
				'label'=>Yii::t('common', 'Register'),
				'url'=>array('/site/register'),
				'itemOptions'=>array(
					'class'=>'nav-item visible-sm visible-xs',
				),
				'visible'=>Yii::app()->user->isGuest,
			),
			array(
			'label'=>Yii::t('common', 'Language') . ' <i class="fa fa-angle-down"></i>',
			'url'=>'#',
			'itemOptions'=>array(
				'class'=>'nav-item dropdown visible-sm visible-xs',
			),
			'linkOptions'=>array(
				'class'=>'dropdown-toggle',
				'data-toggle'=>'dropdown',
				'data-hover'=>'dropdown',
				'data-delay'=>0,
				'data-close-others'=>'false',
			),
			'items'=>array(
				array(
					'label'=>'简体中文',
					'url'=>'?lang=zh_cn',
				),
				array(
					'label'=>'繁体中文',
					'url'=>'?lang=zh_tw',
				),
				array(
					'label'=>'English',
					'url'=>'?lang=en',
				),
			),
		)));
		$this->_navibar = $navibar;
	}

	public function init() {
		if(isset($_REQUEST['lang']) && $_REQUEST['lang'] != '') {
			$this->setLanguage($_REQUEST['lang'], true);
		} else if(isset($_COOKIE['language']) && $_COOKIE['language'] != '') {
			$this->setLanguage($_COOKIE['language']);
		} else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$this->setLanguage(strtolower(str_replace('-', '_', current(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])))));
		}
		parent::init();
	}

	public function setLanguage($language, $setCookie = false) {
		if (!in_array($language, array('en', 'zh_cn', 'zh_tw'))) {
			return;
		}
		Yii::app()->language = $language;
		if ($setCookie) {
			$_COOKIE['language'] = $language;
			setcookie('language', $language, time() + 365 * 86400, '/');
		}
	}

	public function getIsCN() {
		return Yii::app()->language == 'zh_cn' || Yii::app()->language == 'zh_tw';
	}

	public function translateTWInNeed($data) {
		if (Yii::app()->language !== 'zh_tw') {
			return $data;
		}
		if ($this->zh2Hant === null) {
			include APP_PATH . '/protected/data/ZhConversion.php';
			$this->zh2Hant = $zh2Hant;
		}
		if (is_string($data)) {
			return strtr($data, $this->zh2Hant);
		} elseif (is_array($data)) {
			$data = var_export($data, true);
			$data = strtr($data, $this->zh2Hant);
			$data = eval('return ' . $data . ';');
			return $data;
		}
	}

	protected function beforeAction($action) {
		if ($this->logAction) {
			$params = array(
				'get'=>$_GET,
				'post'=>$_POST,
				'cookie'=>$_COOKIE,
				'session'=>$_SESSION,
				'server'=>$_SERVER,
			);
			Yii::log(json_encode($params), 'test', $this->id . '.' . $action->id);
		}
		$this->setPageTitle(Yii::app()->name);
		if (!Yii::app()->user->isGuest && $this->user && $this->user->isBanned()
			&& $this->id !== 'site' && $action->id !== 'banned' && $action->id !== 'logout'
		) {
			$this->redirect(array('/site/banned'));
		}
		return parent::beforeAction($action);
	}

	public function setWeiboShareDefaultText($weiboShareDefaultText, $appendTitle = true) {
		$weiboShareDefaultText = strip_tags($weiboShareDefaultText);
		$weiboShareDefaultText = preg_replace('{[\r\n]+}', ' ', $weiboShareDefaultText);
		if ($appendTitle) {
			$weiboShareDefaultText .= $this->getPageTitle();
		}
		$this->_weiboShareDefaultText = rawurlencode($weiboShareDefaultText);
	}

	public function getWeiboShareDefaultText() {
		if ($this->_weiboShareDefaultText === null) {
			$this->_weiboShareDefaultText = rawurlencode($this->getPageTitle());
		}
		return $this->_weiboShareDefaultText;
	}

	public function setWeiboSharePic($weiboSharePic) {
		if (is_array($weiboSharePic)) {
			$weiboSharePic = urlencode(implode('||', $weiboSharePic));
		} else {
			$weiboSharePic = urlencode($weiboSharePic);
		}
		$this->_weiboSharePic = $weiboSharePic;
	}

	public function getWeiboSharePic() {
		if ($this->_weiboSharePic === null) {
			$this->_weiboSharePic = urlencode(Yii::app()->params->weiboSharePic);
		}
		return $this->_weiboSharePic;
	}

	public function setDescription($description) {
		$description = strip_tags($description);
		$description = preg_replace('{[\r\n]+}', ' ', $description);
		$this->_description = $description;
	}

	public function getDescription() {
		if ($this->_description === null) {
			$this->_description = Yii::t('description', Yii::app()->params->description);
		}
		return $this->_description;
	}

	public function setTitle($title) {
		$this->_title = Yii::t('common', $title);
	}

	public function getTitle() {
		// if ($this->_title === null) {
		// 	$this->_title = Yii::t('common', Yii::app()->name);
		// }
		return $this->_title;
	}

	public function setPageTitle($pageTitle) {
		if (is_string($pageTitle)) {
			return parent::setPageTitle(Yii::t('common', $pageTitle));
		} elseif (is_array($pageTitle)) {
			$pageTitle[] = Yii::t('common', Yii::app()->name);
			return parent::setPageTitle(implode(' - ', array_map(function($s) {
				return Yii::t('common', strip_tags($s));
			}, $pageTitle)));
		}
	}

	protected function getCaptchaAction() {
		if(($captcha = Yii::app()->getController()->createAction($this->captchaAction)) === null) {
			if(strpos($this->captchaAction,'/') !== false) {
				if(($ca = Yii::app()->createController($this->captchaAction)) !== null) {
					list($controller,$actionID) = $ca;
					$captcha = $controller->createAction($actionID);
				}
			}
			if($captcha === null) {
				throw new CException(Yii::t('yii','CCaptchaValidator.action "{id}" is invalid. Unable to find such an action in the current controller.',
						array('{id}'=>$this->captchaAction)));
			}
		}
		return $captcha;
	}

	public function getUser() {
		if ($this->_user !== null) {
			return $this->_user;
		}
		return $this->_user = User::model()->findByPk(Yii::app()->user->id);
	}

	public function ajaxReturn($status, $data, $message = '') {
		echo CJSON::encode(array(
			'status'=>$status,
			'data'=>$data,
			'msg'=>$message,
		));
		Yii::app()->end();
	}

	public function ajaxOK($data) {
		$this->ajaxReturn(Constant::AJAX_OK, $data);
	}

	public function ajaxError($status, $message = null) {
		if ($message === null) {
			$message = Constant::getAjaxMessage($status);
		}
		$this->ajaxReturn($status, array(), $message);
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param int $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return int the intvaled GET parameter value
	 */
	public function iGet($name, $defaultValue = 0) {
		return isset($_GET[$name]) ? intval($_GET[$name]) : $defaultValue;
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param string $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return string the strvaled GET parameter value
	 */
	public function sGet($name, $defaultValue = '') {
		return isset($_GET[$name]) ? trim(strval($_GET[$name])) : $defaultValue;
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param array $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return array the strvaled GET parameter value
	 */
	public function aGet($name, $defaultValue = array()) {
		return isset($_GET[$name]) ? (array)$_GET[$name] : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param int $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return int the intvaled POST parameter value
	 */
	public function iPost($name, $defaultValue = 0) {
		return isset($_POST[$name]) ? intval($_POST[$name]) : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the POST parameter name
	 * @param string $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return string the strvaled POST parameter value
	 */
	public function sPost($name, $defaultValue = '') {
		return isset($_POST[$name]) ? trim(strval($_POST[$name])) : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the POST parameter name
	 * @param array $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return array the strvaled POST parameter value
	 */
	public function aPost($name, $defaultValue = array()) {
		return isset($_POST[$name]) ? (array)$_POST[$name] : $defaultValue;
	}

	/**
	 * Returns the named REQUEST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the REQUEST parameter name
	 * @param int $defaultValue the default parameter value if the REQUEST parameter does not exist.
	 * @return int the intvaled REQUEST parameter value
	 */
	public function iRequest($name, $defaultValue = 0) {
		return isset($_REQUEST[$name]) ? intval($_REQUEST[$name]) : $defaultValue;
	}

	/**
	 * Returns the named REQUEST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the REQUEST parameter name
	 * @param string $defaultValue the default parameter value if the REQUEST parameter does not exist.
	 * @return string the strvaled REQUEST parameter value
	 */
	public function sRequest($name, $defaultValue = '') {
		return isset($_REQUEST[$name]) ? trim(strval($_REQUEST[$name])) : $defaultValue;
	}

	/**
	 * Returns the named REQUEST parameter value.
	 * If the REQUEST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the REQUEST parameter name
	 * @param array $defaultValue the default parameter value if the REQUEST parameter does not exist.
	 * @return array the strvaled REQUEST parameter value
	 */
	public function aRequest($name, $defaultValue = array()) {
		return isset($_REQUEST[$name]) ? (array)$_REQUEST[$name] : $defaultValue;
	}

}