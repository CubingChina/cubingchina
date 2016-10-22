<?php

class CompetitionHandler extends MsgHandler {
	public $users = [];

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
			}
		}
	}
}