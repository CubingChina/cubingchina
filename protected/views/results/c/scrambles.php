<?php
$this->renderPartial('c/dropdownEvents', $_data_);
$this->widget('GroupGridView', array(
  'dataProvider'=>new CArrayDataProvider($scrambles, array(
    'pagination'=>false,
    'sort'=>false,
  )),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'groupKey'=>'event_id',
  'groupHeader'=>'implode("&nbsp;&nbsp;&nbsp;&nbsp;", array(
    Events::getFullEventNameWithIcon($data->event_id),
    Yii::t("RoundTypes", $data->round->cell_name),
    CHtml::tag("a", ["id"=>$data->event_id], ""),
  ))',
  'repeatHeader'=>true,
  'rowHtmlOptionsExpression'=>'array(
    "data-event"=>$data->event_id,
    "data-round"=>$data->round_type_id,
  )',
  'columns'=>array(
    array(
      'name'=>Yii::t('Scrambles', 'Group'),
      'value'=>'$data->group_id',
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
