<?php

class ChatHandler extends MsgHandler {
	public function process() {
		if (!empty($this->msg->content) && $this->competition != null && $this->user != null) {
			$this->broadcastSuccess('message.new', array(
				'user'=>array(
					'name'=>$this->user->getCompetitionName(),
				),
				'time'=>time(),
				'content'=>htmlspecialchars($this->msg->content),
			), $this->competition, $this->client);
		}
	}
}
