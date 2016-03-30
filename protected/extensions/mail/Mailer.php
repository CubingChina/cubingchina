<?php

class Mailer extends CApplicationComponent {
	const SEPARATOR = ';';

	//mailgun
	public $from;
	public $fromname;
	public $api;
	public $baseUrl;

	protected $titlePrefix = 'Cubing China (粗饼·中国魔方赛事网) - ';
	protected $viewPath;
	private $_mailer;

	public function init() {
		parent::init();
		$this->baseUrl = 'http://cubingchina.com';
		$this->viewPath = dirname(__FILE__) . '/views/';
	}

	public function getMailer() {
		if ($this->_mailer === null) {
			$this->_mailer = new \Mailgun\Mailgun($this->api);
		}
		return $this->_mailer;
	}

	public function sendActivate($user) {
		$to = $user->email;
		$subject = $this->makeTitle('注册激活邮件');
		$message = $this->render('activate', array(
			'user'=>$user,
			'url'=>$this->getUrl($user->getMailUrl('activate')),
		));
		return $this->add($to, $subject, $message);
	}

	public function sendResetPassword($user) {
		$to = $user->email;
		$subject = $this->makeTitle('密码重设邮件');
		$message = $this->render('resetPassword', array(
			'user'=>$user,
			'url'=>$this->getUrl($user->getMailUrl('resetPassword')),
		));
		return $this->add($to, $subject, $message);
	}

	public function sendAddCompetitionNotice($competition) {
		$to = Yii::app()->params->adminEmail;
		$subject = $this->makeTitle('新增比赛通知');
		$message = $this->render('addCompetitionNotice', array(
			'user'=>Yii::app()->controller->user,
			'competition'=>$competition,
			'url'=>$this->getUrl(Yii::app()->createUrl(
				'/board/competition/edit',
				array(
					'id'=>$competition->id,
				)
			)),
		));
		return $this->add($to, $subject, $message);
	}

	public function sendRegistrationNotice($registration) {
		$subject = $this->makeTitle('选手报名通知');
		$message = $this->render('registrationNotice', array(
			'registration'=>$registration,
			'url'=>$this->getUrl(Yii::app()->createUrl(
				'/board/registration/index',
				array(
					'Registration'=>array(
						'competition_id'=>$registration->competition_id,
					),
				)
			)),
		));
		$to = array();
		foreach ($registration->competition->organizer as $organizer) {
			$to[] = $organizer->user->email;
		}
		return $this->add($to, $subject, $message);
	}

	public function sendCompetitionNotice($competition, $users, $title, $content, $englishContent = '') {
		$subject = "【{$competition->name_zh}】$title";
		$organizers = array();
		foreach ($competition->organizer as $organizer) {
			$organizers[] = $organizer->user->email;
		}
		$message = $this->render('competitionNotice', array(
			'title'=>$title,
			'competition'=>$competition,
			'content'=>$content,
			'englishContent'=>$englishContent,
			'organizers'=>$organizers,
		));
		//用bcc方式发送会被ban掉。。
		foreach ($users as $user) {
			$this->add($user, $subject, $message, Yii::app()->user->name);
		}
		return true;
	}

	public function getCompetitionNoticePreview($competition, $users, $title, $content, $englishContent = '') {
		$subject = "【{$competition->name_zh}】$title";
		$organizers = array();
		foreach ($competition->organizer as $organizer) {
			$organizers[] = $organizer->user->email;
		}
		$message = $this->render('competitionNotice', array(
			'title'=>$title,
			'competition'=>$competition,
			'content'=>$content,
			'englishContent'=>$englishContent,
			'organizers'=>$organizers,
		));
		return compact('subject', 'message');
	}

	public function getUrl($url) {
		if (strpos($url, $this->baseUrl) === false) {
			$url = $this->baseUrl . $url;
		}
		return $url;
	}

	private function makeTitle($title) {
		return $this->titlePrefix . $title;
	}

	public function add($to, $subject, $message, $replyTo = '', $cc = '', $bcc = '') {
		foreach (array('to', 'replyTo', 'cc', 'bcc') as $var) {
			if (is_array($$var)) {
				$$var = implode(self::SEPARATOR, $$var);
			}
		}
		$mail = new Mail();
		$mail->to = $to;
		$mail->reply_to = $replyTo;
		$mail->cc = $cc;
		$mail->bcc = $bcc;
		$mail->subject = $subject;
		$mail->message = $message;
		$mail->add_time = $mail->update_time = time();
		return $mail->save();
	}

	public function send($mail) {
		$fields = array(
			'api_user'=>$this->api['user'],
			'api_key'=>$this->api['key'],
			'from'=>$this->from,
			'fromname'=>$this->fromname,
			'to'=>$mail->to,
			'subject'=>$mail->subject,
			'html'=>$mail->message,
		);
		if ($mail->reply_to) {
			$fields['replyto'] = $mail->reply_to;
		}
		if ($mail->cc) {
			$fields['cc'] = $mail->cc;
		}
		if ($mail->bcc) {
			$fields['bcc'] = $mail->bcc;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_URL, 'https://sendcloud.sohu.com/webapi/mail.send.json');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

		$result = curl_exec($ch);

		if($result === false) {
			$error = curl_error($ch);
			Yii::log(implode('|', array($mail->to, $mail->subject, $mail->message, $error)), 'error', 'sendmail');
			return false;
		}
		curl_close($ch);
		$result = json_decode($result);
		if ($result === false) {
			return false;
		}
		if (isset($result->message) && $result->message == 'success') {
			return true;
		} else {
			if (isset($result->errors)) {
				Yii::log(implode('|', array($mail->to, $mail->subject, $mail->message, json_encode($result->errors))), 'error', 'sendmail');
			}
			return false;
		}
	}

	public function reset() {
		$this->_mailer = null;
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