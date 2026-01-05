<?php
use GuzzleHttp\Client;

class Mailer extends CApplicationComponent {
	const SEPARATOR = ';';

	//mailgun
	public $from;
	public $fromname;
	public $api;
	public $baseUrl;

	protected $titlePrefix = 'Cubing China (粗饼)';
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
		$to = [Yii::app()->params->caqaEmail];
		$subject = $this->makeCaqaTitle("已确认【{$competition->name_zh}】");
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
		$to = [Yii::app()->params->caqaEmail];
		$title = $competition->isRejected() ? '拒绝' : '驳回';
		$subject = $this->makeCaqaTitle("已{$title}【{$competition->name_zh}】");
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
		$to = [Yii::app()->params->caqaEmail];
		$subject = $this->makeCaqaTitle("已通过【{$competition->name_zh}】");
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

	public function sendCompetitionLockNotice($user, $competition) {
		$to = [Yii::app()->params->caqaEmail];
		$subject = $this->makeCaqaTitle("已锁定【{$competition->name_zh}】");
		$message = $this->render('competitionLockNotice', array(
			'user'=>$user,
			'competition'=>$competition,
			'url'=>$this->getUrl(Yii::app()->createUrl(
				'/board/competition/view',
				array('id'=>$competition->id,)
			)),
		));
		return $this->add($to, $subject, $message);
	}

	public function sendCompetitionPreNotice($competition) {
		$to = [Yii::app()->params->caqaEmail];
		$subject = $this->makeCaqaTitle("预公示【{$competition->name_zh}】");
		$message = $this->render('competitionPreNotice', array(
			'user'=>$competition->organizer[0]->user,
			'competition'=>$competition,
			'url'=>$this->getUrl(Yii::app()->createUrl(
				'/board/competition/view',
				array(
					'id'=>$competition->id,
				)
			)),
		));
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
		$events = array();
		$translation = include APP_PATH . '/protected/messages/zh_cn/event.php';
		foreach ($registration->getAcceptedEvents() as $registrationEvent) {
			$event = $registrationEvent->event;
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
			'regulationUrl'=>$this->getUrl($registration->competition->getUrl('regulations')),
		));
		$cc = array();
		foreach ($registration->competition->organizer as $organizer) {
			$cc[] = $organizer->user->email;
		}
		return $this->add($registration->user->email, $subject, $message, $cc[0], $cc);
	}

	public function sendRegistrationEventsDisqualified($registration) {
		$events = array();
		$translation = include APP_PATH . '/protected/messages/zh_cn/event.php';
		foreach ($registration->getDisqualifiedEvents() as $registrationEvent) {
			$event = $registrationEvent->event;
			$enName = Events::getEventName($event);
			$cnName = isset($translation[$enName]) ? $translation[$enName] : $enName;
			$events['en'][] = $enName;
			$events['cn'][] = $cnName;
		}
		$events['en'] = implode(', ', $events['en']);
		$events['cn'] = implode('、', $events['cn']);
		$subject = "【{$registration->competition->name_zh}】报名项目取消通知 Registration Events Disqualified";
		$message = $this->render('registrationEventsDisqualified', array(
			'registration'=>$registration,
			'competition'=>$registration->competition,
			'user'=>$registration->user,
			'events'=>$events,
			'regulationUrl'=>$this->getUrl($registration->competition->getUrl('regulations')),
		));
		$cc = array();
		foreach ($registration->competition->organizer as $organizer) {
			$cc[] = $organizer->user->email;
		}
		return $this->add($registration->user->email, $subject, $message, $cc[0], $cc);
	}

	public function sendCompetitionNotice($competition, $users, $title, $content, $englishContent = '') {
		$subject = $content == '' ? "【{$competition->name}】$title" : "【{$competition->name_zh}】$title";
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
		$subject = $content == '' ? "【{$competition->name}】$title" : "【{$competition->name_zh}】$title";
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

	public function sendToUsers($users, $title, $content, $englishContent = '') {
		$subject = $this->makeTitle($title);
		foreach ($users as $user) {
			$message = $this->render('toUsers', array(
				'title'=>$title,
				'content'=>$content,
				'englishContent'=>$englishContent,
				'user'=>$user,
				'sender'=>Yii::app()->controller->user,
			));
			$this->add($user->email, $subject, $message, Yii::app()->controller->user->email);
		}
		return true;
	}

	public function getSendToUsersPreview($title, $content, $englishContent = '') {
		$subject = $this->makeTitle($title);
		$message = $this->render('toUsers', array(
			'title'=>$title,
			'content'=>$content,
			'englishContent'=>$englishContent,
			'user'=>Yii::app()->controller->user,
			'sender'=>Yii::app()->controller->user,
		));
		return compact('subject', 'message');
	}

	public function getUrl($url) {
		if (is_array($url)) {
			$url = CHtml::normalizeUrl($url);
		}
		$url = ltrim($url, '.');
		if (strpos($url, 'http') !== 0) {
			$url = $this->baseUrl . $url;
		}
		return $url;
	}

	private function makeTitle($title) {
		return $this->titlePrefix . ' - ' . $title;
	}

	private function makeCaqaTitle($title) {
		return $title . ' - ' . $this->titlePrefix;
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
		$params = [
			'from'=>$this->from,
			'fromName'=>$this->fromname,
			'to'=>$mail->to,
			'subject'=>$mail->subject,
			'html'=>$mail->message,
		];
		if ($mail->reply_to) {
			$params['replyTo'] = $mail->reply_to;
		}
		if ($mail->cc) {
			$params['cc'] = $mail->cc;
		}
		if ($mail->bcc) {
			$params['bcc'] = $mail->bcc;
		}
		$result = $this->request($this->getApiUrl('mail/send'), $params);
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

	public function removeBounceList() {
		$list = $this->request($this->getApiUrl('bounce/delete'), [
			'days'=>3,
		]);
	}

	protected function getApiUrl($path) {
		return $this->api['baseUrl'] . $path;
	}

	protected function request($url, $params = []) {
		$params['apiUser'] = $this->api['user'];
		$params['apiKey'] = $this->api['key'];
		$client = new Client();
		try {
			$response = $client->post($url, [
				'form_params'=>$params,
			]);
			if ($response->getStatusCode() != 200) {
				return false;
			}
			$body = $response->getBody(true);
			$body = json_decode($body);
			return $body;
		} catch (Exception $e) {
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
