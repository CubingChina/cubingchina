<?php

class Mailer extends CApplicationComponent {
	public $host;
	public $username;
	public $password;
	public $smtp = true;
	public $smtpSecure = '';
	public $smtpAuth = true;
	public $port = 25;
	public $from;
	public $fromName;
	public $html = true;
	public $charset = 'utf-8';

	protected $titlePrefix = 'Cubing China (粗饼·中国魔方赛事网) - ';
	protected $viewPath;
	private $_mail;

	public function init() {
		parent::init();
		$this->viewPath = dirname(__FILE__) . '/views/';
	}

	public function getMail() {
		if ($this->_mail === null) {
			$this->_mail = $mail = new PHPMailer();
			$mail->Host = $this->host;
			$mail->Username = $this->username;
			$mail->Password = $this->password;
			if ($this->smtp) {
				$mail->isSMTP();
			}
			$mail->SMTPSecure = $this->smtpSecure;
			$mail->SMTPAuth = $this->smtpAuth;
			$mail->Port = $this->port;
			$mail->From = $this->from;
			$mail->FromName = $this->fromName;
			$mail->isHTML($this->html);
			$mail->CharSet = $this->charset;
		}
		return $this->_mail;
	}

	public function sendActivate($user) {
		$to = $user->email;
		$subject = $this->makeTitle('注册激活邮件');
		$message = $this->render('activate', array(
			'user'=>$user,
			'url'=>$user->getMailUrl('activate'),
		));
		return $this->add($to, $subject, $message);
	}

	public function sendResetPassword($user) {
		$to = $user->email;
		$subject = $this->makeTitle('密码重设邮件');
		$message = $this->render('resetPassword', array(
			'user'=>$user,
			'url'=>$user->getMailUrl('resetPassword'),
		));
		return $this->add($to, $subject, $message);
	}

	public function sendRegistrationNotice($registration) {
		$subject = $this->makeTitle('选手报名通知');
		$message = $this->render('registrationNotice', array(
			'registration'=>$registration,
			'url'=>Yii::app()->request->getBaseUrl(true) . Yii::app()->createUrl(
				'/board/registration/index',
				array(
					'Registration'=>array(
						'competition_id'=>$registration->competition_id,
					),
				)
			),
		));
		foreach ($registration->competition->organizer as $organizer) {
			$to = $organizer->user->email;
			$this->add($to, $subject, $message);
		}
		return true;
	}

	private function makeTitle($title) {
		return $this->titlePrefix . $title;
	}

	public function add($to, $subject, $message) {
		$mail = new Mail();
		$mail->to = $to;
		$mail->subject = $subject;
		$mail->message = $message;
		$mail->add_time = $mail->update_time = time();
		return $mail->save();
	}

	public function send($to, $subject, $message) {
		$mail = $this->mail;
		$mail->addAddress($to);
		$mail->Subject = $subject;
		$mail->Body = $message;
		$mail->AltBody = implode("\r\n", array_filter(array_map(function($value) {return trim($value, " \t\r\n");}, explode("\n", strip_tags($message)))));
		$result = $mail->send();
		if ($result == false) {
			Yii::log(implode('|', array($to, $subject, $message, $mail->ErrorInfo)), 'error', 'sendmail');
		}
		$this->reset();
		return $result;
	}

	public function reset() {
		$this->_mail = null;
	}

	protected function render($_view_, $_data_) {
		$_viewFile_ = $this->viewPath . $_view_ . '.php';
		if(is_array($_data_)) {
			extract($_data_, EXTR_PREFIX_SAME, 'data');
		} else {
			$data = $_data_;
		}
		ob_start();
		ob_implicit_flush(false);
		require $_viewFile_;
		$content = ob_get_clean();
		ob_start();
		ob_implicit_flush(false);
		require $this->viewPath . 'layout.php';
		return ob_get_clean();
	}
}