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

	public static function cssEscape($string) {
		return str_replace(array('%u', '%'), '\\', self::jsEscape($string, true));
	}

	public static function jsEscape($string, $fullEscape = false) {
		$n = $bn = $tn = 0;
		$output = '';
		$special = "-_.+@/*0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		while($n < strlen($string)) {
			$ascii = ord($string[$n]);
			if ($ascii == 9 || $ascii == 10 || (32 <= $ascii && $ascii <= 126)) {
				$tn = 1;
				$n++;
			} elseif (194 <= $ascii && $ascii <= 223) {
				$tn = 2;
				$n += 2;
			} elseif (224 <= $ascii && $ascii <= 239) {
				$tn = 3;
				$n += 3;
			} elseif (240 <= $ascii && $ascii <= 247) {
				$tn = 4;
				$n += 4;
			} elseif (248 <= $ascii && $ascii <= 251) {
				$tn = 5;
				$n += 5;
			} elseif ($ascii == 252 || $ascii == 253) {
				$tn = 6;
				$n += 6;
			} else {
				$n++;
			}
			$singleStr = substr($string, $bn, $tn);
			$charVal = bin2hex(iconv('utf-8', 'ucs-2be', $singleStr));
			if (base_convert($charVal, 16, 10) > 0xff) {
				$output .= '%u' . $charVal;
			} else {
				if (!$fullEscape && false !== strpos($special, $singleStr)) {
					$output .= $singleStr;
				} else {
					$output .= '%' . dechex(ord($string[$bn]));
				}
			}
			$bn = $n;
		}
		return $output;
	}
}