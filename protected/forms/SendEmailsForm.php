<?php

class SendEmailsForm extends CFormModel {
	public $user_type;
	public $title;
	public $content;
	public $content_zh;

	public static function getUserTypes() {
		$types = User::getRoles();
		unset($types[User::ROLE_UNCHECKED], $types[User::ROLE_CHECKED]);
		return $types;
	}

	public function send($users = null) {
		if ($users === null) {
			$users = User::model()->findAllByAttributes([
				'role'=>$this->user_type,
			]);
		}
		// var_dump(count($users));exit;
		return Yii::app()->mailer->sendToUsers($users, $this->title, $this->content_zh, $this->content);
	}

	public function getPreview() {
		return Yii::app()->mailer->getSendToUsersPreview($this->title, $this->content_zh, $this->content);
	}

	public function checkContent() {
		if (trim($this->content) == '' && trim($this->content_zh) == '') {
			$this->addError('content', '中文正文和英文正文至少一项不为空');
			$this->addError('content_zh', '中文正文和英文正文至少一项不为空');
		}
	}

	public function checkUserType() {
		if (!array_key_exists($this->user_type, self::getUserTypes())) {
			$this->addError('user_type', '请选择用户类型！');
		}
	}

	public function rules() {
		return array(
			array('title, user_type', 'required'),
			array('content, content_zh', 'safe'),
			array('content', 'checkContent'),
			array('user_type', 'checkUserType'),
		);
	}

	public function attributeLabels() {
		return array(
			'user_type'=>'用户类型',
			'title'=>'标题',
			'content_zh'=>'中文正文',
			'content'=>'英文正文',
		);
	}
}
