<?php

class ActiveRecord extends CActiveRecord {
	private static $_qqwry;
	private static $_qqwryFile;

	public function getAttributeValue($name, $forceValue = false) {
		return self::getModelAttributeValue($this, $name, $forceValue);
	}

	public static function applyRegionCondition($command, $region, $countryField = 'rs.personCountryId', $continentField = 'country.continentId') {
		switch ($region) {
			case 'World':
				break;
			case 'Africa':
			case 'Asia':
			case 'Oceania':
			case 'Europe':
			case 'North America':
			case 'South America':
				$command->andWhere($continentField . '=:region', array(
					':region'=>'_' . $region,
				));
				break;
			default:
				$command->andWhere($countryField . '=:region', array(
					':region'=>$region,
				));
				break;
		}
	}

	public static function getModelAttributeValue($model, $name, $forceValue = false) {
		$value = $model[Yii::app()->controller->getAttributeName($name)];
		if ($forceValue) {
			$value = $value ?: $model[$name];
		}
		return Yii::app()->controller->translateTWInNeed($value);
	}

	public static function getYesOrNo() {
		return array(
			1=>'是',
			0=>'否',
		);
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
		if (!class_exists('\Zhuzhichao\IpLocationZh\Ip', false) || empty($this->$attribute)) {
			return $this->$attribute;
		}
		$result = \Zhuzhichao\IpLocationZh\Ip::find($this->$attribute);
		return CHtml::tag('button', array(
			'class'=>'btn btn-xs btn-orange tips',
			'data-toggle'=>'tooltip',
			'data-placement'=>'left',
			'title'=>implode('', $result),
		), $this->$attribute);
	}

	protected function afterSave() {
		// Yii::app()->cache->flush();
		parent::afterSave();
	}
}