<?php
use \Yunpian\Sdk\YunpianClient;

class RegistrationCommand extends CConsoleCommand {

	private $_smsClient;

	public function actionClearWaitingList() {
		$competitions = Competition::model()->findAllByAttributes([
			'status'=>Competition::STATUS_SHOW,
		], [
			'condition'=>'reg_end<' . time() . ' AND reg_end>' . (time() - 7 * 86400),
		]);
		$_SERVER['HTTPS'] = 1;
		$_SERVER['HTTP_HOST'] = 'cubingchina.com';
		foreach ($competitions as $competition) {
			$registrations = Registration::model()->findAllByAttributes([
				'competition_id'=>$competition->id,
				'status'=>Registration::STATUS_WAITING,
			]);
			foreach ($registrations as $registration) {
				if (!$registration->cancel(Registration::STATUS_CANCELLED_TIME_END)) {
					echo 'Failed: ', $registration->user->getCompetitionName() . PHP_EOL;
				}
			}
		}
	}

	public function actionClearDisqualified($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $competition->has_qualifying_time && $this->confirm($competition->name_zh)) {
			$_SERVER['HTTPS'] = 1;
			$_SERVER['HTTP_HOST'] = 'cubingchina.com';
			$registrations = Registration::getRegistrations($competition);
			foreach ($registrations as $registration) {
				$unmetEvents = $registration->getUnmetEvents();
				$acceptedEvents = $registration->getAcceptedEvents();
				$disqualifiedEvents = [];
				foreach ($acceptedEvents as $registrationEvent) {
					if (in_array($registrationEvent->event, $unmetEvents)) {
						$registrationEvent->disqualify();
						$disqualifiedEvents[] = $registrationEvent;
					}
				}
				if (count($disqualifiedEvents) === count($acceptedEvents)) {
					$this->logDisqualified($registration, $disqualifiedEvents, true);
					$registration->disqualify();
				} elseif (count($disqualifiedEvents) > 0) {
					$this->logDisqualified($registration, $disqualifiedEvents, false);
					Yii::app()->mailer->sendRegistrationEventsDisqualified($registration);
					$registration->save();
				}
			}
		}
	}

	private function logDisqualified($registration, $disqualifiedEvents, $disqualified) {
		echo implode("\t", [$registration->user->getCompetitionName(), $registration->user->wcaid, $disqualified ? 'disqualified' : '', implode("\t", $disqualifiedEvents)]), "\n";
		if ($disqualified) {
			$this->sendSms($registration->user->mobile, "【粗饼网】尊敬的{$registration->user->name_zh}，2018中国魔方锦标赛参赛资格筛查已完成，很抱歉的通知您，由于您所有项目均未达到资格线要求，您的报名已被取消，无法参加本次比赛，详见规则：http://t.cn/Rssv1Ou");
		} else {
			$this->sendSms($registration->user->mobile, sprintf('【粗饼网】尊敬的%s，2018中国魔方锦标赛参赛资格筛查已完成，很抱歉的通知您，由于您报名的项目%s未达到资格线要求，无法参加本次比赛的上述项目，详见规则：http://t.cn/Rssv1Ou',
				$registration->user->name_zh,
				implode('、', array_map(function($event) {
					if (is_object($event) && isset($event->event)) {
						$event = $event->event;
					}
					return Events::getFullEventName($event);
				}, $disqualifiedEvents))
			));
		}
	}

	private function sendSms($mobile, $message) {
		if (!preg_match('|^1\d{10}$|', $mobile)) {
			return;
		}
		$mobile = '18601371730';
		$client = $this->getSmsClient();
		$result = $client->single_send([
			YunpianClient::MOBILE=>$mobile,
			YunpianClient::TEXT=>$message,
		]);
		if (!$result->isSucc()){
			Yii::log("Send to {$mobile} failed. Message: {$message}", 'error', 'sms');
		}
		return $result->isSucc();
	}

	private function getSmsClient() {
		if ($this->_smsClient === null) {
			$this->_smsClient = YunpianClient::create(Env::get('YUNPIAN_API_KEY'));
		}
		return $this->_smsClient->sms();
	}
}
