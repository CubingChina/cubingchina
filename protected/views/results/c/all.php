<?php
$this->renderPartial('c/dropdownEvents', $_data_);
$this->widget('GroupGridView', array(
  'dataProvider'=>new CArrayDataProvider($all, array(
    'pagination'=>false,
    'sort'=>false,
  )),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'groupKey'=>'eventId',
  'groupHeader'=>'implode("&nbsp;&nbsp;&nbsp;&nbsp;", array(
    Events::getFullEventNameWithIcon($data->eventId),
    Yii::t("Rounds", $data->round->cellName),
    Yii::t("common", $data->format->name),
  ))',
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
<?php
Yii::app()->clientScript->registerScript('competition',
<<<EOT
  var results = {};
  var lastRound;
  var lastEvent;
  $('tr[data-round]').each(function() {
    var that = $(this);
    var event = that.data('event');
    var round = transRound(that.data('round'));
    var person = that.data('person');
    var best = that.data('best');
    var pos = that.data('pos');
    if (round == 'b') {
      $(makeExpression({
        event: event,
        round: '1',
        person: person
      })).addClass('warning');
      return;
    }
    if (round == 'f' && pos <= 3 && best > 0) {
      that.addClass('success');
    }
    if (lastEvent !== event) {
      results[event] = {};
      lastEvent = event;
      lastRound = '';
    }
    if (lastRound !== round) {
      results[event][round] = {}
      if (lastRound) {
        results[event][round].lastRound = lastRound;
      }
      lastRound = round;
    }
    results[event][round][person] = {};
    if (results[event][round].lastRound && results[event][results[event][round].lastRound][person]) {
      results[event][results[event][round].lastRound][person].promoted = true;
    }
  }).each(function() {
    var that = $(this);
    var event = that.data('event');
    var round = transRound(that.data('round'));
    var person = that.data('person');
    if (round !== 'b' && results[event][round][person].promoted) {
      that.addClass('success');
    }
  });
  function makeExpression(data) {
    var expression = ['tr'];
    for (var key in data) {
      expression.push('[data-' + key + '="' + data[key] + '"]');
    }
    return expression.join('');
  }
  function transRound(round) {
    switch (round) {
      case 'h':
        round = '0';
        break;
      case 'd':
        round = '1';
        break;
      case 'e':
        round = '2';
        break;
      case 'g':
        round = '3';
        break;
      case 'c':
        round = 'f';
        break;
    }
    return round;
  }
EOT
);