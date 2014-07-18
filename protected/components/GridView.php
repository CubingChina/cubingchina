<?php

Yii::import('zii.widgets.grid.CGridView');

class GridView extends CGridView {
	public $cssFile = false;
	public $template = '{items}{pager}';
	public $itemsCssClass = 'table table-bordered table-condensed table-hover table-boxed';
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
	public $htmlOptions = array(
		'class'=>'table-responsive',
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