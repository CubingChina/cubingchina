<?php

class Html extends CHtml {

	public static function formGroup($model, $attribute, $htmlOptions = array()) {
		$tag = 'div';
		if (isset($htmlOptions['tag'])) {
			$tag = $htmlOptions['tag'];
			unset($htmlOptions['tag']);
		}
		if (!isset($htmlOptions['class'])) {
			$htmlOptions['class'] = 'form-group';
		} else {
			$htmlOptions['class'] .= ' form-group';
		}
		if ($model->hasErrors($attribute)) {
			$htmlOptions['class'] .= ' has-error';
		}
		$args = func_get_args();
		$content = implode("\n", array_slice($args, 3));
		$content = self::tag($tag, $htmlOptions, $content);
		return $content;
	}

	public static function activeTextField($model , $attribute , $htmlOptions = array()) {
		$type = isset($htmlOptions['type']) ? $htmlOptions['type'] : 'text';
		if (!isset($htmlOptions['placeholder'])) {
			$htmlOptions['placeholder'] = $model->getAttributeLabel(preg_replace('{\[\]$}', '', $attribute));
		}
		if (!isset($htmlOptions['class'])) {
			$htmlOptions['class'] = 'form-control';
		} else {
			$htmlOptions['class'] .= ' form-control';
		}
		self::resolveNameID($model , $attribute , $htmlOptions);
		self::clientChange('change', $htmlOptions);
		return self::activeInputField($type, $model, $attribute, $htmlOptions);
	}

	public static function fontAwesome($name, $position = '') {
		$icon = '<i class="fa fa-' . $name . '"></i>';
		switch ($position) {
			case 'a':
				$icon .= ' ';
				break;
			case 'b':
				$icon = ' ' . $icon;
				break;
		}
		return $icon;
	}
}
