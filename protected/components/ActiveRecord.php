<?php

class ActiveRecord extends CActiveRecord {
	const YES = 1;
	const NO = 0;

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
		$value = $model[self::getAttributeName($name)];
		if ($forceValue) {
			$value = $value ?: $model[$name];
		}
		if (Yii::app() instanceof CConsoleApplication) {
			return $value;
		}
		return Yii::app()->controller->translateTWInNeed($value);
	}

	public static function getAttributeName($name = 'name') {
		if (Yii::app()->language[0] == 'z' && Yii::app()->language[1] == 'h') {
			$name .= '_zh';
		}
		return $name;
	}

	public static function getYesOrNo() {
		return array(
			self::YES=>Yii::t('common', 'Yes'),
			self::NO=>Yii::t('common', 'No'),
		);
	}

	public function getRegIpDisplay($attribute = 'ip') {
		$result = \Zhuzhichao\IpLocationZh\Ip::find($this->$attribute);
		return CHtml::tag('button', array(
			'class'=>'btn btn-xs btn-orange tips',
			'data-toggle'=>'tooltip',
			'data-placement'=>'left',
			'title'=>implode('', $result),
		), $this->$attribute);
	}

	protected function getTimeInNumber($attribute) {
		$time = $this->$attribute;
		if (!ctype_digit($time)) {
			$time = strtotime($time);
		}
		return $time;
	}

	protected function beforeSave() {
		if ($this->isNewRecord && $this->hasAttribute('create_time') && $this->create_time == 0) {
			$this->create_time = time();
		}
		if ($this->hasAttribute('update_time')) {
			$this->update_time = time();
		}
		return parent::beforeSave();
	}

	protected function afterSave() {
		// Yii::app()->cache->flush();
		parent::afterSave();
	}
}
