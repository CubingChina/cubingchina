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
	public $footerOnTop = false;

	public function renderTableBody() {
		$data = $this->dataProvider->getData();
		$n = count($data);
		echo "<tbody>\n";
		if ($n > 0) {
			if ($this->footerOnTop) {
				$this->renderTableFooterColumns();
			}
			for ($row = 0; $row < $n; ++$row) {
				$this->renderTableRow($row);
			}
		} else {
			echo '<tr><td colspan="' . count($this->columns) . '" class="empty">';
			$this->renderEmptyText();
			echo "</td></tr>\n";
		}
		echo "</tbody>\n";
	}

	public function renderTableFooterColumns() {
		$hasFooter = $this->getHasFooter();
		if ($hasFooter) {
			echo "<tr>\n";
			foreach ($this->columns as $column) {
				$column->renderFooterCell();
			}
			echo "</tr>\n";
		}
	}

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
