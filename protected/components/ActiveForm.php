<?php

class ActiveForm extends CActiveForm {
	public function init() {
		if (!isset($this->htmlOptions['id'])) {
			$this->htmlOptions['id'] = $this->id;
		} else {
			$this->id = $this->htmlOptions['id'];
		}

		if ($this->stateful) {
			echo CHtml::statefulForm($this->action, $this->method, $this->htmlOptions);
		} else {
			echo Html::beginForm($this->action, $this->method, $this->htmlOptions);
		}
		if ($this->errorMessageCssClass === null) {
			$this->errorMessageCssClass = CHtml::$errorMessageCss;
		}
	}
}