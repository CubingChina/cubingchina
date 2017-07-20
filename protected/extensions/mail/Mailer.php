<?php

class Mailer extends CApplicationComponent {
	const SEPARATOR = ';';

	//mailgun
	public $from;
	public $fromname;
	public $api;
	public $baseUrl;

	protected $titlePrefix = 'Cubing China (粗饼) - ';
	protected $viewPath;

	public function init() {
		parent::init();
		$this->baseUrl = Yii::app()->params->baseUrl;
		$this->viewPath = dirname(__FILE__) . '/views/';
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

	public function sendCompetitionConfirmNotice($competition) {
		$to = [Yii::app()->params->adminEmail];
		$subject = $this->makeTitle("【{$competition->name_zh}】已确认");
		$message = $this->render('competitionConfirmNotice', array(
			'user'=>Yii::app()->controller->user,
			'competition'=>$competition,
			'url'=>$this->getUrl(Yii::app()->createUrl(
				'/board/competition/view',
				array(
					'id'=>$competition->id,
				)
			)),
		));
		if ($competition->type == Competition::TYPE_WCA) {
			foreach ($competition->delegate as $delegate) {
				$to[] = $delegate->user->email;
			}
		}
		$cc = [];
		foreach ($competition->organizer as $organizer) {
			$cc[] = $organizer->user->email;
		}
		return $this->add($to, $subject, $message, $cc[0], $cc);
	}

	public function sendCompetitionRejectNotice($competition) {
		$to = [Yii::app()->params->adminEmail];
		$title = $competition->isRejected() ? '拒绝' : '驳回';
		$subject = $this->makeTitle("【{$competition->name_zh}】已被{$title}");
		$message = $this->render('competitionRejectNotice', array(
			'user'=>$competition->organizer[0]->user,
			'competition'=>$competition,
			'title'=>$title,
			'url'=>$this->getUrl(Yii::app()->createUrl(
				'/board/competition/view',
				array(
					'id'=>$competition->id,
				)
			)),
		));
		if ($competition->type == Competition::TYPE_WCA) {
			foreach ($competition->delegate as $delegate) {
				$to[] = $delegate->user->email;
			}
		}
		$cc = [];
		foreach ($competition->organizer as $organizer) {
			$cc[] = $organizer->user->email;
		}
		return $this->add($to, $subject, $message, $cc[0], $cc);
	}

	public function sendCompetitionAcceptNotice($competition) {
		$to = [Yii::app()->params->adminEmail];
		$subject = $this->makeTitle("【{$competition->name_zh}】审核通过");
		$message = $this->render('competitionAcceptNotice', array(
			'user'=>$competition->organizer[0]->user,
			'competition'=>$competition,
			'url'=>$this->getUrl(Yii::app()->createUrl(
				'/board/competition/edit',
				array(
					'id'=>$competition->id,
				)
			)),
		));
		if ($competition->type == Competition::TYPE_WCA) {
			foreach ($competition->delegate as $delegate) {
				$to[] = $delegate->user->email;
			}
		}
		$cc = [];
		foreach ($competition->organizer as $organizer) {
			$cc[] = $organizer->user->email;
		}
		return $this->add($to, $subject, $message, $cc[0], $cc);
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

	public function sendRegistrationAcception($registration) {
		$subject = $this->makeTitle('报名成功通知 Registration Confirmed');
		$qrCodeUrl = $this->getUrl($registration->qrCodeUrl);
		$registration->formatEvents();
		$events = array();
		$translation = include APP_PATH . '/protected/messages/zh_cn/event.php';
		foreach ($registration->events as $event) {
			$enName = Events::getEventName($event);
			$cnName = isset($translation[$enName]) ? $translation[$enName] : $enName;
			$events['en'][] = $enName;
			$events['cn'][] = $cnName;
		}
		$events['en'] = implode(', ', $events['en']);
		$events['cn'] = implode('、', $events['cn']);
		$message = $this->render('registrationAcception', array(
			'registration'=>$registration,
			'competition'=>$registration->competition,
			'user'=>$registration->user,
			'events'=>$events,
			'url'=>$this->getUrl(CHtml::normalizeUrl($registration->competition->getUrl())),
			'qrCodeUrl'=>$qrCodeUrl,
		));
		return $this->add($registration->user->email, $subject, $message);
	}

	public function sendRegistrationCancellation($registration) {
		$subject = $this->makeTitle('退赛成功通知 Registration Cancelled');
		$message = $this->render('registrationCancellation', array(
			'registration'=>$registration,
			'competition'=>$registration->competition,
			'user'=>$registration->user,
		));
		$cc = array();
		foreach ($registration->competition->organizer as $organizer) {
			$cc[] = $organizer->user->email;
		}
		return $this->add($registration->user->email, $subject, $message, $cc[0], $cc);
	}

	public function sendRegistrationDisqualified($registration) {
		$subject = "【{$registration->competition->name_zh}】报名取消通知 Registration Disqualified";
		$message = $this->render('registrationDisqualified', array(
			'registration'=>$registration,
			'competition'=>$registration->competition,
			'user'=>$registration->user,
		));
		$cc = array();
		foreach ($registration->competition->organizer as $organizer) {
			$cc[] = $organizer->user->email;
		}
		return $this->add($registration->user->email, $subject, $message, $cc[0], $cc);
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
			$this->add($user, $subject, $message, Yii::app()->controller->user->email);
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
		if (strpos($url, 'http') !== 0) {
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
			'apiUser'=>$this->api['user'],
			'apiKey'=>$this->api['key'],
			'from'=>$this->from,
			'fromName'=>$this->fromname,
			'to'=>$mail->to,
			'subject'=>$mail->subject,
			'html'=>$mail->message,
		);
		if ($mail->reply_to) {
			$fields['replyTo'] = $mail->reply_to;
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
		curl_setopt($ch, CURLOPT_URL, 'http://api.sendcloud.net/apiv2/mail/send');
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
		if (isset($result->statusCode) && $result->statusCode == 200) {
			return true;
		} else {
			if (isset($result->message)) {
				Yii::log(implode('|', array($mail->to, $mail->subject, $mail->message, json_encode($result->message))), 'error', 'sendmail');
			}
			return false;
		}
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
