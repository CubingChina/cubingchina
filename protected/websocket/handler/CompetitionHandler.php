<?php

class CompetitionHandler extends MsgHandler {
	public function process() {
		if (isset($this->msg->competitionId)) {
			$this->client->competitionId = $this->msg->competitionId;
			$this->client->server->increaseOnlineNumber($this->client->competitionId);
		}
	}
}