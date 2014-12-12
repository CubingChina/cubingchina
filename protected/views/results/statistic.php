<?php $this->widget('GridView', array(
  'dataProvider'=>new CArrayDataProvider($statistic['rows'], array(
    'pagination'=>false,
  )),
  'id'=>'statistic_' . $id,
  'enableSorting'=>false,
  'front'=>true,
  'columns'=>array_map(function($column) {
    $column['header'] = Yii::app()->evaluateExpression($column['header']);
    return $column;
  }, $statistic['columns']),
  'htmlOptions'=>array(
    'class'=>'table-responsive' . $class,
  ),
));
?>