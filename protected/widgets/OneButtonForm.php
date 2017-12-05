<?php

class OneButtonForm extends Widget {
	public $action = null;
	public $method = 'post';
	public $text;
	public $btnClass = '';
	public $data = [];

	public function run() {
		$this->beginWidget('ActiveForm', [
			'action'=>$this->action,
			'method'=>$this->method,
		]);
		foreach ($this->data as $key=>$value) {
			echo CHtml::hiddenField($key, $value);
		}
		echo CHtml::tag('button', [
			'class'=>'btn btn-sm btn-theme',
		], $this->text ?: Yii::t('common', 'Submit'));
		$this->endWidget();
	}
}
