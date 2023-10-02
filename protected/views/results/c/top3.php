<?php
$this->widget('GroupGridView', [
  'dataProvider'=>new CArrayDataProvider($top3, [
    'pagination'=>false,
    'sort'=>false,
  ]),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'groupKey'=>'eventId',
  'groupHeader'=>'CHtml::link(Events::getFullEventNameWithIcon($data->eventId), [
    "/results/c",
    "id"=>$data->competitionId,
    "type"=>"all",
    "#"=>$data->eventId,
  ])',
  'columns'=>[
    [
      'name'=>Yii::t('Results', 'Place'),
      'type'=>'raw',
      'value'=>'$data->pos',
      'headerHtmlOptions'=>['class'=>'place'],
    ],
    [
      'name'=>Yii::t('Results', 'Person'),
      'type'=>'raw',
      'value'=>'Persons::getLinkByNameNId($data->personName, $data->personId)',
    ],
    [
      'name'=>Yii::t('common', 'Best'),
      'type'=>'raw',
      'value'=>'$data->getTime("best", false, true)',
      'headerHtmlOptions'=>['class'=>'result'],
      'htmlOptions'=>['class'=>'result'],
    ],
    [
      'name'=>Yii::t('common', 'Average'),
      'type'=>'raw',
      'value'=>'$data->getTime("average", false, true)',
      'headerHtmlOptions'=>['class'=>'result'],
      'htmlOptions'=>['class'=>'result'],
    ],
    [
      'name'=>Yii::t('common', 'Region'),
      'value'=>'Region::getIconName($data->personCountry->name, $data->personCountry->iso2)',
      'type'=>'raw',
      'htmlOptions'=>['class'=>'region'],
    ],
    [
      'name'=>Yii::t('common', 'Detail'),
      'type'=>'raw',
      'value'=>'$data->detail',
    ],
  ],
]); ?>
