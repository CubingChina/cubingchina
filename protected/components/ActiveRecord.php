<?php

class ActiveRecord extends CActiveRecord {
	public function getAttributeValue($name) {
		$name = Yii::app()->controller->getAttributeName($name);
		$value = $this->getAttribute($name);
		return Yii::app()->controller->translateTWInNeed($value);
	}

	protected function afterSave() {
		Yii::app()->cache->flush();
		parent::afterSave();
	}
}