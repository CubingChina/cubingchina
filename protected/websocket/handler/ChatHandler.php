<?php

class ChatHandler extends MsgHandler {
	public function process() {
		if (!empty($this->msg->text) && $this->competition != null && $this->user != null) {
			$this->broadcastSuccess('newmessage', array(
				'user'=>'',
				'text'=>$this->msg->text,
			), $this->competition, $this->client);
		}
	}
}