<?php
$this->widget('GroupRankGridView', [
  'dataProvider'=>new CArrayDataProvider($byPerson, [
    'pagination'=>false,
    'sort'=>false,
  ]),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'groupKey'=>'person_id',
  'groupHeader'=>'implode("&nbsp;&nbsp;&nbsp;&nbsp;", [
    Persons::getLinkByNameNId($data->person_name, $data->person_id),
    Region::getIconName($data->personCountry->name, $data->personCountry->iso2),
  ])',
  'rankKey'=>'event_id',
  'repeatHeader'=>true,
  'rowHtmlOptionsExpression'=>'[
    "data-event"=>$data->event_id,
    "data-round"=>$data->round_type_id,
    "data-person"=>$data->person_id,
    "data-best"=>$data->best,
    "data-pos"=>$data->pos,
  ]',
  'columns'=>[
    [
      'class'=>'RankColumn',
      'name'=>Yii::t('common', 'Event'),
      'type'=>'raw',
      'value'=>'$displayRank ? CHtml::link(Events::getFullEventNameWithIcon($data->event_id), [
        "/results/c",
        "id"=>$data->competition_id,
        "type"=>"all",
        "#"=>$data->event_id,
      ]) : ""',
    ],
    [
      'name'=>Yii::t('Results', 'Round'),
      'type'=>'raw',
      'value'=>'Yii::t("RoundTypes", $data->round->cell_name)',
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
      'value'=>'$data->getTime("best", true, true)',
      'headerHtmlOptions'=>['class'=>'result'],
      'htmlOptions'=>['class'=>'result'],
    ],
    [
      'name'=>Yii::t('common', 'Average'),
      'type'=>'raw',
      'value'=>'$data->getTime("average", true, true)',
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
