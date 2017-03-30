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
		$value = $model[Yii::app()->controller->getAttributeName($name)];
		if ($forceValue) {
			$value = $value ?: $model[$name];
		}
		return Yii::app()->controller->translateTWInNeed($value);
	}

	public static function getYesOrNo() {
		return array(
			self::YES=>'是',
			self::NO=>'否',
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

	protected function beforeSave() {
		if ($this->isNewRecord && $this->hasAttribute('create_time')) {
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
