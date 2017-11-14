<?php

class Html extends CHtml {

	public static function beginForm($action = '', $method = 'post', $htmlOptions = array()) {
		$htmlOptions['action'] = $url = self::normalizeUrl($action);
		$htmlOptions['method'] = $method;
		$form = self::tag('form', $htmlOptions, false, false);
		$hiddens = array();
		if (!strcasecmp($method, 'get') && ($pos = strpos($url, '?')) !== false) {
			foreach (explode('&', substr($url , $pos + 1)) as $pair) {
				$pair = explode('=', $pair);
				$key = urldecode($pair[0]);
				$value = isset($pair[1]) ? urldecode($pair[1]) : '';
				$hiddens[] = self::hiddenField($key, $value , array('id'=>false));
			}
		}
		$request = Yii::app()->request;
		if ($request->enableCsrfValidation && !strcasecmp($method, 'post')) {
			$csrfName = self::shuffleScriptVar($request->csrfTokenName);
			$csrfValue = self::shuffleScriptVar($request->getCsrfToken());
			Yii::app()->clientScript->registerScript('form',
<<<EOT
  $('<input type="hidden">').attr({
	name: $csrfName,
	value: $csrfValue
  }).appendTo($('form'));
EOT
);
		}
		if ($hiddens !== array()) {
			$form .= "\n" . self::tag('div', array('style'=>'display:none'), implode("\n", $hiddens));
		}
		return $form;
	}

	public static function shuffleScriptVar($value) {
		$length = strlen($value);
		$max = 3;
		$offset = 0;
		$result = array();
		$cut = array();
		while ($length > 0) {
			$len = rand(0, min($max, $length));
			$rand = "'" . self::randString(rand(1, $max)) . "'";
			if ($len > 0) {
				$val = "'" . substr($value, $offset, $len) . "'";
				$result[] = rand(0, 1) ? "//{$rand}\n{$val}" : "{$val}//{$rand}\n";
			} else {
				if (rand(0, 1)) {
					$result[] = rand(0, 1) ? "''///*{$rand}*/{$rand}\n" : "/* {$rand}//{$rand} */''";
				} else {
					$result[] = rand(0, 1) ? "//{$rand}\n{$rand}" : "{$rand}//{$rand}\n";
					$cut[] = array($offset, strlen($rand) - 2 + $offset);
				}
			}
			$offset += $len;
			$length -= $len;
		}
		$name = '_' . self::randString(rand(3, 7));
		$cutName = '_' . self::randString(rand(3, 7));
		$var = implode('+', $result);
		$cutVar = json_encode($cut);
		return "(function () {
			var {$name} = {$var}, {$cutName} = {$cutVar};

			for (var i = 0; i < {$cutName}.length; i ++) {
				{$name} = {$name}.substring(0, {$cutName}[i][0]) + {$name}.substring({$cutName}[i][1]);
			}
			return {$name};
		})()";
	}

	public static function randString($length, $specialChars = false) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		if ($specialChars) {
			$chars .= '!@#$%^&*()';
		}
		$result = '';
		$max = strlen($chars) - 1;
		for ($i = 0; $i < $length; $i++) {
			$result .= $chars[rand(0, $max)];
		}
		return $result;
	}

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

	public static function activeSwitch($model, $name, $htmlOptions = []) {
		$htmlOptions['data-switch'] = '';
		$clientScript = Yii::app()->clientScript;
		$clientScript->registerPackage('switch');
		$options = json_encode([
			'onText'=>Yii::t('common', 'Yes'),
			'offText'=>Yii::t('common', 'No'),
		]);
		$clientScript->registerScript('switch', '$("[data-switch]").bootstrapSwitch(' . $options . ')');
		return self::tag('div', ['class'=>''], self::activeCheckBox($model, $name, $htmlOptions));
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

	public static function countdown($time, $options = []) {
		if ($time <= time()) {
			return '';
		}
		if (!isset($options['class'])) {
			$options['class'] = 'countdown-timer';
		} else {
			$options['class'] .= ' countdown-timer';
		}
		$options['data-time'] = $time * 1000;
		$options['data-remaining'] = ($time - time()) * 1000;
		foreach (['days', 'hours', 'minutes', 'seconds'] as $unit) {
			$containers[] = self::tag('div', [
				'class'=>'square-content',
			], self::tag('div', [
				'class'=>'square-inner',
			], implode('', [
				self::tag('div', ['class'=>"text $unit"], ''),
				self::tag('div', ['class'=>'unit'], Yii::t('common', ucfirst($unit))),
				self::tag('div', ['class'=>'progress-container'], ''),
			])));
		}
		array_splice($containers, 2, 0, self::tag('div', ['class'=>'clearfix visible-sm visible-xs'], ''));
		return self::tag('div', $options, implode('', $containers));
	}
}
