<?php $this->widget('GridView', array(
  'dataProvider'=>$model->search(),
  // 'filter'=>false,
  'enableSorting'=>false,
  'front'=>true,
  'columns'=>array(
    array(
      'name'=>'ip',
      'type'=>'raw',
      'value'=>'$data->getRegIpDisplay()',
    ),
    array(
      'name'=>'date',
      'type'=>'raw',
      'value'=>'date("Y-m-d H:i", $data->date)',
    ),
    'from_cookie',
  ),
)); ?>