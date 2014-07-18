<?php
class Adapter extends CApplicationComponent {

	const PC = 'pc';
	const WAP = 'wap';
	protected $requestName = 'pf';
	protected $cookieName = 'PLATFORM';
	protected $expire = 604800;

	private $_isWap = false;
	private $_browserInfo;

	public function init() {
		parent::init();
		$this->detect();
		if ($this->Browser == 'IE' && version_compare($this->Version, '9.0', '<')) {
			echo '<!doctype html>
<html lang="en">
<head>
	<title>奇遇！</title>
	<meta charset="UTF-8">
</head>
<body>
恶灵退散！亲，升级浏览器吧！
</body>
</html>';
			Yii::app()->end();
		}
	}

	protected function detect() {
		// $platform = isset($_REQUEST[$this->requestName]) ? trim($_REQUEST[$this->requestName]) : (isset($_COOKIE[$this->cookieName]) ? trim($_COOKIE[$this->cookieName]) : '');
		// if (!empty($platform)) {
		// 	$this->setIsWap($platform === self::WAP);
		// 	return;
		// }
		$browscap = new Browscap(Yii::app()->runtimePath);
		$browscap->localFile = Yii::app()->basePath . '/data/browscap.ini';
		$this->_browserInfo = $browscap->getBrowser();
		$this->setIsWap($this->_browserInfo->isMobileDevice);
	}

	public function getBrowserInfo() {
		return $this->_browserInfo;
	}

	public function __get($name) {
		if (isset($this->_browserInfo->$name)) {
			return $this->_browserInfo->$name;
		}
		return parent::__get($name);
	}

	public function __set($name, $value) {
		if (isset($this->_browserInfo->$name)) {
			$this->_browserInfo->$name = $value;
			return true;
		}
		return parent::__set($name, $value);
	}

	public function setIsWap($isWap) {
		$this->_isWap = $isWap;
		//$platform = $isWap ? self::WAP : self::PC;
		//setcookie($this->cookieName, $platform, time() + $this->expire);
	}

	public function getIsWap() {
		return $this->_isWap;
	}

}