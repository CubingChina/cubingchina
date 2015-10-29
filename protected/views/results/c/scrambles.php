<?php
$this->renderPartial('c/dropdownEvents', $_data_);
$this->widget('GroupGridView', array(
  'dataProvider'=>new CArrayDataProvider($results, array(
    'pagination'=>false,
    'sort'=>false,
  )),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'groupKey'=>'eventId',
  'groupHeader'=>'implode("&nbsp;&nbsp;&nbsp;&nbsp;", array(CHtml::tag("span", array(
      "id"=>$data->eventId,
      "class"=>"event-icon event-icon event-icon-" . $data->eventId,
      "title"=>Yii::t("event", $data->event->cellName),
    ), Yii::t("event", $data->event->cellName)),
    Yii::t("Rounds", $data->round->cellName),
  ))',
  'repeatHeader'=>true,
  'rowHtmlOptionsExpression'=>'array(
    "data-event"=>$data->eventId,
    "data-round"=>$data->roundId,
  )',
  'columns'=>array(
    array(
      'name'=>Yii::t('Scrambles', 'Group'),
      'value'=>'$data->groupId',
    ),
    array(
      'name'=>Yii::t('Scrambles', 'No.'),
      'type'=>'raw',
      'value'=>'$data->num',
    ),
    array(
      'name'=>Yii::t('Scrambles', 'Scramble'),
      'type'=>'raw',
      'value'=>'$data->formattedScramble',
      'htmlOptions'=>array(
        'class'=>'scramble',
      ),
    ),
  ),
)); ?>