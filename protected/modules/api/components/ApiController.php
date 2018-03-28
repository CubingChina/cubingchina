<?php

class ApiController extends Controller {

	public function accessRules() {
		return [
			[
				'allow',
				'users'=>['*'],
			],
		];
	}

	protected function beforeAction($action) {
		return true;
	}
}
