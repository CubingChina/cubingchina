<?php $this->widget('GridView', array(
  'dataProvider'=>$model->searchUser(),
  // 'filter'=>false,
  'enableSorting'=>false,
  'front'=>true,
  'columns'=>array(
    array(
      'header'=>'操作',
      'headerHtmlOptions'=>array(
        'class'=>'header-operation',
      ),
      'type'=>'raw',
      'value'=>'$data->operationButton',
    ),
    array(
      'name'=>'competition.name',
      'type'=>'raw',
      'value'=>'CHtml::link($data->competition->name_zh, array("/board/registration/index", "Registration"=>array("competition_id"=>$data->competition_id)))',
    ),
    array(
      'name'=>'competition.date',
      'type'=>'raw',
      'value'=>'$data->competition->getDisplayDate()',
    ),
    array(
      'name'=>'events',
      'type'=>'raw',
      'value'=>'$data->getRegistrationEvents()',
    ),
    array(
      'name'=>'fee',
      'type'=>'raw',
      'value'=>'$data->getRegistrationFee()',
    ),
    array(
      'name'=>'date',
      'type'=>'raw',
      'value'=>'date("Y-m-d H:i", $data->date)',
    ),
    'comments',
    array(
      'name'=>'status',
      'type'=>'raw',
      'value'=>'$data->getStatusText()',
    ),
  ),
)); ?>