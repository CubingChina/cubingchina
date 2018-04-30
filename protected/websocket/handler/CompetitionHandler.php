<?php

class CompetitionHandler extends MsgHandler {

	public function process() {
		if (isset($this->msg->competitionId)) {
			$this->client->competitionId = $this->msg->competitionId;
			$this->client->server->increaseOnlineNumber($this->client->competitionId);
			$competition = $this->getCompetition();
			$cache = Yii::app()->cache;
			if ($competition) {
				$usersKey = 'live_users_' . $competition->id;
				if (($users = $cache->get($usersKey)) === false) {
					$users = [];
					$registrations = Registration::getRegistrations($competition);
					foreach ($registrations as $registration) {
						$users[$registration->number] = [
							'number'=>$registration->number,
							'name'=>$registration->user->getCompetitionName(),
							'wcaid'=>$registration->user->wcaid,
							'region'=>$registration->user->country->name,
						];
					}
					$cache->set($usersKey, $users);
				}
				$this->success('users', $users);
				if ($this->checkAccess()) {
					$recordsKey = 'live_current_record_' . $competition->id;
					if (($currentRecords = $cache->get($recordsKey)) === false) {
						if (!isset($registrations)) {
							$registrations = Registration::getRegistrations($competition);
						}
						$events = $competition->getAssociatedEvents();
						$currentRecords = [];
						foreach ($registrations as $registration) {
							$region = $registration->user->country->name;
							if (isset($currentRecords[$region])) {
								continue;
							}
							foreach ($events as $event=>$value) {
								foreach (['best', 'average'] as $type) {
									$NR = Results::getRecord($region, $event, $type, $competition->date);
									if ($NR !== null) {
										$currentRecords[$region][$event][$type{0}] = intval($NR[$type]);
									}
								}
							}
						}
						$cache->set($recordsKey, $currentRecords);
					}
					$this->success('record.current', $currentRecords);
				}
			}
		}
	}
}
