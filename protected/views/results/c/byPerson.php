<?php
$this->widget('GroupRankGridView', [
  'dataProvider'=>new CArrayDataProvider($byPerson, [
    'pagination'=>false,
    'sort'=>false,
  ]),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'groupKey'=>'personId',
  'groupHeader'=>'implode("&nbsp;&nbsp;&nbsp;&nbsp;", [
    Persons::getLinkByNameNId($data->personName, $data->personId),
    Region::getIconName($data->person->country->name, $data->person->country->iso2),
  ])',
  'rankKey'=>'eventId',
  'repeatHeader'=>true,
  'rowHtmlOptionsExpression'=>'[
    "data-event"=>$data->eventId,
    "data-round"=>$data->roundTypeId,
    "data-person"=>$data->personId,
    "data-best"=>$data->best,
    "data-pos"=>$data->pos,
  ]',
  'columns'=>[
    [
      'class'=>'RankColumn',
      'name'=>Yii::t('common', 'Event'),
      'type'=>'raw',
      'value'=>'$displayRank ? CHtml::link(Events::getFullEventNameWithIcon($data->eventId), [
        "/results/c",
        "id"=>$data->competitionId,
        "type"=>"all",
        "#"=>$data->eventId,
      ]) : ""',
    ],
    [
      'name'=>Yii::t('Results', 'Round'),
      'type'=>'raw',
      'value'=>'Yii::t("RoundTypes", $data->round->cellName)',
      'headerHtmlOptions'=>['class'=>'place'],
    ],
    [
      'name'=>Yii::t('Results', 'Place'),
      'type'=>'raw',
      'value'=>'$data->pos',
      'headerHtmlOptions'=>['class'=>'place'],
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
      'name'=>Yii::t('common', 'Detail'),
      'type'=>'raw',
      'value'=>'$data->detail',
    ],
  ],
]); ?>
