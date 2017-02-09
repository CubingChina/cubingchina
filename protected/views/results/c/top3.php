<?php
$this->widget('GroupGridView', array(
  'dataProvider'=>new CArrayDataProvider($top3, array(
    'pagination'=>false,
    'sort'=>false,
  )),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'groupKey'=>'eventId',
  'groupHeader'=>'CHtml::link(Events::getFullEventNameWithIcon($data->eventId), array(
    "/results/c",
    "id"=>$data->competitionId,
    "type"=>"all",
    "#"=>$data->eventId,
  ))',
  'columns'=>array(
    array(
      'name'=>Yii::t('Results', 'Place'),
      'type'=>'raw',
      'value'=>'$data->pos',
      'headerHtmlOptions'=>array('class'=>'place'),
    ),
    array(
      'name'=>Yii::t('Results', 'Person'),
      'type'=>'raw',
      'value'=>'Persons::getLinkByNameNId($data->personName, $data->personId)',
    ),
    array(
      'name'=>Yii::t('common', 'Best'),
      'type'=>'raw',
      'value'=>'$data->getTime("best")',
      'headerHtmlOptions'=>array('class'=>'result'),
      'htmlOptions'=>array('class'=>'result'),
    ),
    array(
      'name'=>'',
      'type'=>'raw',
      'value'=>'$data->regionalSingleRecord',
      'headerHtmlOptions'=>array('class'=>'record'),
    ),
    array(
      'name'=>Yii::t('common', 'Average'),
      'type'=>'raw',
      'value'=>'$data->getTime("average")',
      'headerHtmlOptions'=>array('class'=>'result'),
      'htmlOptions'=>array('class'=>'result'),
    ),
    array(
      'name'=>'',
      'type'=>'raw',
      'value'=>'$data->regionalAverageRecord',
      'headerHtmlOptions'=>array('class'=>'record'),
    ),
    array(
      'name'=>Yii::t('common', 'Region'),
      'value'=>'Region::getIconName($data->person->country->name, $data->person->country->iso2)',
      'type'=>'raw',
      'htmlOptions'=>array('class'=>'region'),
    ),
    array(
      'name'=>Yii::t('common', 'Detail'),
      'type'=>'raw',
      'value'=>'$data->detail',
    ),
  ),
)); ?>