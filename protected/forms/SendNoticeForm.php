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

	public function rules() {
		return array(
			array('title, content_zh, competitors', 'required'),
			array('content', 'safe'),
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