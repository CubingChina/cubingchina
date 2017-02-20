<?php

class GroupRankGridView extends RankGridView {
	public $groupKey;
	public $groupHeader;
	public $lastGroup;
	public $repeatHeader = false;
	public $front = true;
	private $_currentRow = -1;

	public function renderTableRow($row) {
		$this->_currentRow = $row;
		if ($this->groupKey !== null) {
			$data = $this->dataProvider->data[$row];
			$group = CHtml::value($data, $this->groupKey);
			if ($this->lastGroup != $group) {
				$this->lastGroup = $group;
				$this->renderGroupHeader($row);
				$this->lastRankValue = '';
			}
		}
		parent::renderTableRow($row);
	}

	public function renderGroupHeader($row) {
		if ($this->repeatHeader && $row > 0) {
			$this->renderEmptyRow();
		}
		$data = $this->dataProvider->data[$row];
		echo "<tr>\n";
		echo CHtml::tag('td', array(
			'colspan'=>count($this->columns),
		), $this->evaluateExpression($this->groupHeader, array(
			'data'=>$data,
			'row'=>$row,
			'group'=>$this->lastGroup,
		)));
		echo "</tr>\n";
		if ($this->repeatHeader) {
			echo "<tr>\n";
			foreach ($this->columns as $column) {
				$column->renderHeaderCell();
			}
			echo "</tr>\n";
		}
	}

	public function renderEmptyRow() {
		echo "<tr>\n";
		echo CHtml::tag('td', array(
			'colspan'=>count($this->columns),
		), '&nbsp;');
		echo "</tr>\n";
	}

	public function renderTableHeader() {
		if (!$this->repeatHeader || $this->_currentRow > -1) {
			parent::renderTableHeader();
		}
	}
}
