<?php

class ArchiveMode {
	const NEW_SITE_URL = 'https://cubing.com';

	public static function newSiteUrl() {
		return self::NEW_SITE_URL;
	}

	public static function newSiteLink($text = null, $htmlOptions = []) {
		$htmlOptions = array_merge([
			'target'=>'_blank',
			'rel'=>'noopener',
		], $htmlOptions);
		return CHtml::link($text === null ? self::NEW_SITE_URL : $text, self::NEW_SITE_URL, $htmlOptions);
	}

	public static function isLoginRequest() {
		$request = Yii::app()->request;
		$path = strtolower(trim($request->getPathInfo(), '/'));
		if (in_array($path, ['login', 'site/login'], true)) {
			return true;
		}
		$route = strtolower((string)$request->getParam('r', ''));
		return in_array($route, ['login', 'site/login'], true);
	}

	public static function rejectWriteRequests($controller = null) {
		$request = Yii::app()->request;
		if (!$request->getIsPostRequest() || self::isLoginRequest()) {
			return;
		}

		$message = Yii::t('common', 'This legacy site is read-only and no longer accepts submissions. Please use the new site at {url}.', [
			'{url}'=>self::NEW_SITE_URL,
		]);
		$isJson = $request->getIsAjaxRequest()
			|| ($controller && $controller->module && $controller->module->id === 'api');

		header('HTTP/1.1 403 Forbidden');
		if ($isJson) {
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode([
				'status'=>Constant::STATUS_FORBIDDEN,
				'data'=>null,
				'message'=>$message,
			]);
		} else {
			header('Content-Type: text/plain; charset=utf-8');
			echo $message;
		}
		Yii::app()->end();
	}
}
