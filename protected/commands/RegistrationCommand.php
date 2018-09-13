<?php

class RegistrationCommand extends CConsoleCommand {

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
	}
}
