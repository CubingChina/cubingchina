<?php
$this->widget('GroupGridView', [
  'dataProvider'=>new CArrayDataProvider($top3, [
    'pagination'=>false,
    'sort'=>false,
  ]),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'groupKey'=>'event_id',
  'groupHeader'=>'CHtml::link(Events::getFullEventNameWithIcon($data->event_id), [
    "/results/c",
    "id"=>$data->competition_id,
    "type"=>"all",
    "#"=>$data->event_id,
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
      'value'=>'Persons::getLinkByNameNId($data->person_name, $data->person_id)',
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
