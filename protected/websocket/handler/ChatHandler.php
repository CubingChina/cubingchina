<?php

class ChatHandler extends MsgHandler {
	public function process() {
		if (!empty($this->msg->content) && $this->competition != null && $this->user != null) {
			$this->broadcastSuccess('newmessage', array(
				'user'=>array(
					'name'=>$this->user->getCompetitionName(),
				),
				'time'=>time(),
				'content'=>$this->msg->content,
			), $this->competition, $this->client);
		}
	}
}
