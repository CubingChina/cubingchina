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
				$remainedEvents = array_diff($registration->events, $unmetEvents);
				if (empty($remainedEvents)) {
					Yii::log('Disqualified: ' . $registration->user->getCompetitionName(), 'info', 'disqualify');
					$registration->disqualify();
				} elseif ($registration->events != $remainedEvents) {
					Yii::log('Disqualified: ' . $registration->user->getCompetitionName() . "\nEvents: " . implode(',', array_intersect($registration->events, $unmetEvents)), 'info', 'disqualify');
					$registration->events = $remainedEvents;
					$registration->save();
				}
			}
		}
	}
}
