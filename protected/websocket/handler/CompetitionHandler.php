<?php

class CompetitionHandler extends MsgHandler {
	public static $users = [];
	public static $currentRecords = [];

	public function process() {
		if (isset($this->msg->competitionId)) {
			$this->client->competitionId = $this->msg->competitionId;
			$this->client->server->increaseOnlineNumber($this->client->competitionId);
			$competition = $this->getCompetition();
			if ($competition) {
				if (!isset(self::$users[$competition->id])) {
					self::$users[$competition->id] = [];
					$registrations = Registration::getRegistrations($competition);
					foreach ($registrations as $registration) {
						self::$users[$competition->id][$registration->number] = [
							'number'=>$registration->number,
							'name'=>$registration->user->getCompetitionName(),
							'wcaid'=>$registration->user->wcaid,
							'region'=>$registration->user->country->name,
						];
					}
				}
				$this->success('users', self::$users[$competition->id]);
				if ($this->checkAccess()) {
					if (!isset(self::$currentRecords[$competition->id])) {
						self::$currentRecords[$competition->id] = [];
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
						self::$currentRecords[$competition->id] = $currentRecords;
					}
					$this->success('record.current', self::$currentRecords[$competition->id]);
				}
			}
		}
	}
}
