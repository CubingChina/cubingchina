<?php

Yii::import('zii.widgets.grid.CDataColumn');
class RankColumn extends CDataColumn {
	protected function renderDataCellContent($row, $data) {
		if ($this->value !== null) {
			$value = $this->evaluateExpression($this->value, array(
				'data'=>$data,
				'row'=>$row,
				'rank'=>$this->grid->rank,
			));
		} elseif ($this->name !== null) {
			$value = CHtml::value($data, $this->name);
		}
		echo $value===null ? $this->grid->nullDisplay : $this->grid->getFormatter()->format($value, $this->type);
	}
}
