<?php

Yii::import('zii.widgets.grid.CDataColumn');

class GroupDataColumn extends CDataColumn
{

	/**
	 * Renders a data cell.
	 * @param integer $row the row number (zero-based)
	 */
	public function renderDataCell($row, $data = null)
	{
		$options=$this->htmlOptions;
		if($this->cssClassExpression!==null)
		{
			$class=$this->evaluateExpression($this->cssClassExpression,array('row'=>$row,'data'=>$data));
			if(isset($options['class']))
				$options['class'].=' '.$class;
			else
				$options['class']=$class;
		}
		echo CHtml::openTag('td',$options);
		$this->renderDataCellContent($row,$data);
		echo '</td>';
	}

	/**
	 * Renders the header cell content.
	 * This method will render a link that can trigger the sorting if the column is sortable.
	 */
	protected function renderHeaderCellContent()
	{
		if($this->name!==null && $this->header===null)
		{
			echo CHtml::encode($this->name);
		}
		else
			parent::renderHeaderCellContent();
	}
}
