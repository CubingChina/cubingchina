<?php

class ActiveRecord extends CActiveRecord {
	private static $_qqwry;
	private static $_qqwryFile;

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

	public static function getQQWRY() {
		if (self::$_qqwry === null) {
			self::$_qqwry = new qqwry(self::getQQWRYFile());
		}
		return self::$_qqwry;
	}

	public static function getQQWRYFile() {
		if (self::$_qqwryFile === null) {
			self::$_qqwryFile = Yii::getPathOfAlias('application.data.qqwry').'.dat';
		}
		return self::$_qqwryFile;
	}

	public function getRegIpDisplay($attribute = 'ip') {
		if (!extension_loaded('qqwry') || !class_exists('qqwry', false) || empty($this->$attribute)) {
			return $this->$attribute;
		}
		$result = self::getQQWRY()->q($this->$attribute);
		return CHtml::tag('button', array(
			'class'=>'btn btn-xs btn-orange tips',
			'data-toggle'=>'tooltip',
			'data-placement'=>'left',
			'title'=>implode('|', array_map(function($a) {
				return iconv('gbk', 'utf-8', $a);
			}, $result)),
		), $this->$attribute);
	}

	protected function afterSave() {
		// Yii::app()->cache->flush();
		parent::afterSave();
	}
}