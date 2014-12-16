<?php

class RankGridView extends GridView {
	public $rank = 0;
	public $rankKey;
	public $lastRankValue;

	public function init() {
		parent::init();
		if ($this->rankKey === null) {
			if ($this->dataProvider->getItemCount() > 0) {
				$data = $this->dataProvider->data[0];
				$keys = array_keys($data instanceof CActiveRecord ? $data->attributes : $data);
				$this->rankKey = isset($data['rank']) ? 'rank' : $keys[0];
			}
		}
		$hasRankColumn = false;
		foreach ($this->columns as $column) {
			if ($column instanceof RankColumn) {
				$hasRankColumn = true;
				break;
			}
		}
		if ($hasRankColumn === false) {
			array_unshift($this->columns, Yii::createComponent(array(
				'class'=>'RankColumn',
				'value'=>'$rank',
			), $this));
		}
	}

	public function renderTableRow($row) {
		$data = $this->dataProvider->data[$row];
		$value = CHtml::value($data, $this->rankKey);
		if ($this->lastRankValue != $value) {
			$this->lastRankValue = $value;
			$this->rank++;
		}
		$htmlOptions = array();
		if ($this->rowHtmlOptionsExpression !== null) {
			$options = $this->evaluateExpression($this->rowHtmlOptionsExpression, array(
				'row'=>$row,
				'data'=>$data,
				'rank'=>$this->rank,
			));
			if (is_array($options)) {
				$htmlOptions = $options;
			}
		}

		if ($this->rowCssClassExpression !== null) {
			$class = $this->evaluateExpression($this->rowCssClassExpression, array(
				'row'=>$row,
				'data'=>$data,
				'rank'=>$this->rank,
			));
		} elseif (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0) {
			$class = $this->rowCssClass[$row % $n];
		}

		if (!empty($class)) {
			if(isset($htmlOptions['class'])) {
				$htmlOptions['class'] .= ' '.$class;
			} else {
				$htmlOptions['class'] = $class;
			}
		}

		echo CHtml::openTag('tr', $htmlOptions) . "\n";
		foreach ($this->columns as $column) {
			$column->renderDataCell($row);
		}
		echo "</tr>\n";
	}
}