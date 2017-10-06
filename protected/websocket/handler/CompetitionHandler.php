<?php

class CompetitionHandler extends MsgHandler {
	public $users = [];
	public $currentRecords = [];

	public function process() {
		if (isset($this->msg->competitionId)) {
			$this->client->competitionId = $this->msg->competitionId;
			$this->client->server->increaseOnlineNumber($this->client->competitionId);
			$competition = $this->getCompetition();
			if ($competition) {
				if (!isset($this->users[$competition->id])) {
					$this->users[$competition->id] = [];
					$registrations = Registration::getRegistrations($competition);
					foreach ($registrations as $registration) {
						$this->users[$competition->id][$registration->number] = [
							'number'=>$registration->number,
							'name'=>$registration->user->getCompetitionName(),
							'wcaid'=>$registration->user->wcaid,
							'region'=>$registration->user->country->name,
						];
					}
				}
				$this->success('users', $this->users[$competition->id]);
				if ($this->checkAccess()) {
					if (!isset($this->currentRecords[$competition->id])) {
						$this->currentRecords[$competition->id] = [];
						$registrations = Registration::getRegistrations($competition);
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
										$currentRecords[$region][$type{0}] = $NR[$type];
									}
								}
							}
						}
						$this->currentRecords[$competition->id] = $currentRecords;
					}
					$this->success('record.current', $this->currentRecords[$competition->id]);
				}
			}
		}
	}
}
