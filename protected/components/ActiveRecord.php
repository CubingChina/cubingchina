<?php

class ActiveRecord extends CActiveRecord {
	public function getAttributeValue($name, $forceValue = false) {
		$value = $this->getAttribute(Yii::app()->controller->getAttributeName($name));
		if ($forceValue) {
			$value = $value ?: $this->getAttribute($name);
		}
		return Yii::app()->controller->translateTWInNeed($value);
	}

	protected function afterSave() {
		// Yii::app()->cache->flush();
		parent::afterSave();
	}
}