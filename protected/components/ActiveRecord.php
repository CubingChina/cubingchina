<?php

class ActiveRecord extends CActiveRecord {
	public function getAttributeValue($name, $forceValue = false) {
		return self::getModelAttributeValue($this, $name, $forceValue);
	}

	public static function getModelAttributeValue($model, $name, $forceValue = false) {
		$value = $model[Yii::app()->controller->getAttributeName($name)];
		if ($forceValue) {
			$value = $value ?: $model[$name];
		}
		return Yii::app()->controller->translateTWInNeed($value);
	}

	protected function afterSave() {
		// Yii::app()->cache->flush();
		parent::afterSave();
	}
}