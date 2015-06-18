<?php

class GroupGridView extends GridView {
	public $groupKey;
	public $groupHeader;
	public $lastGroup;
	public $repeatHeader = false;

	public function renderTableRow($row) {
		if ($this->groupKey !== null) {
			$data = $this->dataProvider->data[$row];
			$group = CHtml::value($data, $this->groupKey);
			if ($this->lastGroup != $group) {
				$this->lastGroup = $group;
				$this->renderGroupHeader($row);
			}
		}
		parent::renderTableRow($row);
	}

	public function renderGroupHeader($row) {
		if ($this->repeatHeader && $row > 0) {
			$this->renderTableHeader();
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
	}
}