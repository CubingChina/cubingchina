<?php

class Breadcrumbs extends Widget {
	public $breadcrumbs = array();

	public function run() {
		$this->breadcrumbs = Yii::app()->controller->breadcrumbs;
		if ($this->breadcrumbs !== array()) {
			$this->render('breadcrumbs');
		}
	}
}
