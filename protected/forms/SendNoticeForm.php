<?php

class SendNoticeForm extends CFormModel {
	public $competitors = array();
	public $title;
	public $content;
	public $content_zh;

	public function send($competition) {
		return Yii::app()->mailer->sendCompetitionNotice($competition, $this->competitors, $this->title, $this->content_zh, $this->content);
	}

	public function getPreview($competition) {
		return Yii::app()->mailer->getCompetitionNoticePreview($competition, $this->competitors, $this->title, $this->content_zh, $this->content);
	}

	public function checkContent() {
		if (trim($this->content) == '' && trim($this->content_zh) == '') {
			$this->addError('content', '中文正文和英文正文至少一项不为空');
			$this->addError('content_zh', '中文正文和英文正文至少一项不为空');
		}
	}

	public function rules() {
		return array(
			array('title, competitors', 'required'),
			array('content, content_zh', 'safe'),
			array('content', 'checkContent'),
		);
	}

	public function attributeLabels() {
		return array(
			'competitors'=>'参赛选手',
			'title'=>'标题',
			'content_zh'=>'中文正文',
			'content'=>'英文正文',
		);
	}
}
