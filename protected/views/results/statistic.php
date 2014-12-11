<?php $this->widget('GridView', array(
  'dataProvider'=>new CArrayDataProvider($statistic['rows'], array(
    'pagination'=>false,
  )),
  'id'=>$id,
  'enableSorting'=>false,
  'front'=>true,
  'columns'=>$statistic['columns'],
  'htmlOptions'=>array(
    'class'=>'table-responsive' . $class,
  ),
));
?>