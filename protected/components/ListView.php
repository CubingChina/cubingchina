<?php

Yii::import('zii.widgets.CListView');

class ListView extends CListView {
	public $cssFile = false;
	public $template = '{items}{pager}';
	public $pagerCssClass = 'pager-container';
	public $enableHistory = true;
	public $pager = array(
		'selectedPageCssClass'=>'active',
		'hiddenPageCssClass'=>'disabled',
		'header'=>'',
		'htmlOptions'=>array(
			'class'=>'pagination',
		),
		'cssFile'=>false,
	);
	public $front = false;

	public function renderKeys() {
		if ($this->front === false) {
			parent::renderKeys();
		}
	}

	public function registerClientScript() {
		if ($this->front === false) {
			parent::registerClientScript();
		}
	}
}