<?php
$this->widget('GroupRankGridView', array(
  'dataProvider'=>new CArrayDataProvider($byPerson, array(
    'pagination'=>false,
    'sort'=>false,
  )),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'groupKey'=>'personId',
  'groupHeader'=>'implode("&nbsp;&nbsp;&nbsp;&nbsp;", array(
    Persons::getLinkByNameNId($data->personName, $data->personId),
    Region::getIconName($data->person->country->name, $data->person->country->iso2),
  ))',
  'rankKey'=>'eventId',
  'repeatHeader'=>true,
  'rowHtmlOptionsExpression'=>'array(
    "data-event"=>$data->eventId,
    "data-round"=>$data->roundId,
    "data-person"=>$data->personId,
    "data-best"=>$data->best,
    "data-pos"=>$data->pos,
  )',
  'columns'=>array(
    array(
      'class'=>'RankColumn',
      'name'=>Yii::t('common', 'Event'),
      'type'=>'raw',
      'value'=>'$displayRank ? CHtml::link(CHtml::tag("span", array(
        "class"=>"event-icon event-icon event-icon-" . $data->eventId,
        "title"=>Yii::t("event", $data->event->cellName),
      ), Yii::t("event", $data->event->cellName)), array(
        "/results/c",
        "id"=>$data->competitionId,
        "type"=>"all",
        "#"=>$data->eventId,
      )) : ""',
    ),
    array(
      'name'=>Yii::t('Results', 'Round'),
      'type'=>'raw',
      'value'=>'Yii::t("Rounds", $data->round->cellName)',
      'headerHtmlOptions'=>array('class'=>'place'),
    ),
    array(
      'name'=>Yii::t('Results', 'Place'),
      'type'=>'raw',
      'value'=>'$data->pos',
      'headerHtmlOptions'=>array('class'=>'place'),
    ),
    // array(
    //   'name'=>Yii::t('Results', 'Person'),
    //   'type'=>'raw',
    //   'value'=>'Persons::getLinkByNameNId($data->personName, $data->personId)',
    // ),
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
    // array(
    //   'name'=>Yii::t('common', 'Region'),
    //   'value'=>'Region::getIconName($data->person->country->name, $data->person->country->iso2)',
    //   'type'=>'raw',
    //   'htmlOptions'=>array('class'=>'region'),
    // ),
    array(
      'name'=>Yii::t('common', 'Detail'),
      'type'=>'raw',
      'value'=>'$data->detail',
    ),
  ),
)); ?>
