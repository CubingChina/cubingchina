<?php

class RepeatHeaderGridView extends GridView {
	public $repeatNum = 20;
	public function renderTableBody() {
		$data = $this->dataProvider->getData();
		$n = count($data);
		echo "<tbody>\n";
		if ($n > 0) {
			if ($this->footerOnTop) {
				$this->renderTableFooterColumns();
			}
			for ($row = 0; $row < $n; ++$row) {
				if ($row > 0 && $row % $this->repeatNum === 0) {
					$this->renderTableHeaderColumns();
				}
				$this->renderTableRow($row);
			}
		} else {
			echo '<tr><td colspan="' . count($this->columns) . '" class="empty">';
			$this->renderEmptyText();
			echo "</td></tr>\n";
		}
		echo "</tbody>\n";
	}

	public function renderTableHeaderColumns() {
		echo "<tr>\n";
		foreach($this->columns as $column) {
			$column->renderHeaderCell();
		}
		echo "</tr>\n";
	}
}
