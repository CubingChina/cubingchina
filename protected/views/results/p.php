<div class="col-lg-12 results-person">
  <h2><?php echo Yii::t('Results', 'Current Personal Records'); ?></h2>
  <?php
  $this->widget('GridView', array(
    'dataProvider'=>new CArrayDataProvider($personRanks, array(
      'pagination'=>false,
      'sort'=>false,
    )),
    'front'=>true,
    'template'=>'{items}',
    'columns'=>array(
      array(
        'name'=>Yii::t('common', 'Event'),
        'type'=>'raw',
        'value'=>'CHtml::link(CHtml::tag("span", array(
          "class"=>"event-icon event-icon event-icon-" . $data->eventId,
          "title"=>Yii::t("event", $data->event->cellName),
        ), Yii::t("event", $data->event->cellName)), "#" . $data->event->id)',
      ),
      array(
        'name'=>'NR',
        'type'=>'raw',
        'value'=>'$data->getRank("countryRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>'CR',
        'type'=>'raw',
        'value'=>'$data->getRank("continentRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>'WR',
        'type'=>'raw',
        'value'=>'$data->getRank("worldRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('common', 'Single'),
        'type'=>'raw',
        'value'=>'CHtml::link(Results::formatTime($data->best, $data->eventId), array("/results/rankings", "event"=>$data->eventId))',
        // 'headerHtmlOptions'=>array('class'=>'best'),
      ),
      array(
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->average("best")',
        // 'headerHtmlOptions'=>array('class'=>'best'),
      ),
      array(
        'name'=>'WR',
        'type'=>'raw',
        'value'=>'$data->average("worldRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>'CR',
        'type'=>'raw',
        'value'=>'$data->average("continentRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>'NR',
        'type'=>'raw',
        'value'=>'$data->average("countryRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'header'=>Yii::t('statistics', 'Gold'),
        'value'=>'$data->medals["gold"]',
      ),
      array(
        'header'=>Yii::t('statistics', 'Silver'),
        'value'=>'$data->medals["silver"]',
      ),
      array(
        'header'=>Yii::t('statistics', 'Bronze'),
        'value'=>'$data->medals["bronze"]',
      ),
    ),
  )); ?>
  <?php if (!empty($wcPodiums)): ?>
  <h2><?php echo Yii::t('Results', 'World Championship Podiums'); ?></h2>
  <?php
  $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider($wcPodiums, array(
      'pagination'=>false,
      'sort'=>false,
    )),
    'itemsCssClass'=>'table table-condensed table-hover table-boxed',
    'groupKey'=>'competition.year',
    'groupHeader'=>'$group',
    'columns'=>array(
      array(
        'name'=>Yii::t('common', 'Event'),
        'type'=>'raw',
        'value'=>'CHtml::link(CHtml::tag("span", array(
          "class"=>"event-icon event-icon event-icon-" . $data->eventId,
          "title"=>Yii::t("event", $data->event->cellName),
        ), Yii::t("event", $data->event->cellName)), "#" . $data->event->id)',
      ),
      array(
        'name'=>Yii::t('Results', 'Place'),
        'type'=>'raw',
        'value'=>'$data->pos',
        'headerHtmlOptions'=>array('class'=>'place'),
      ),
      array(
        'name'=>Yii::t('common', 'Single'),
        'type'=>'raw',
        'value'=>'$data->getTime("best")',
        'headerHtmlOptions'=>array('class'=>'result'),
        'htmlOptions'=>array('class'=>'result'),
      ),
      array(
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->getTime("average")',
        'headerHtmlOptions'=>array('class'=>'result'),
        'htmlOptions'=>array('class'=>'result'),
      ),
      array(
        'name'=>Yii::t('common', 'Detail'),
        'type'=>'raw',
        'value'=>'$data->getTime("value1") . "&nbsp;" . $data->getTime("value2") . "&nbsp;" . $data->getTime("value3") . "&nbsp;" . $data->getTime("value4") . "&nbsp;" . $data->getTime("value5")',
      ),
    ),
  )); ?>
  <?php endif; ?>
  <h2><?php echo Yii::t('Persons', 'History'); ?></h2>
  <?php
  $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider($personResults, array(
      'pagination'=>false,
      'sort'=>false,
    )),
    'itemsCssClass'=>'table table-condensed table-hover table-boxed',
    'groupKey'=>'eventId',
    'groupHeader'=>'CHtml::openTag("a", array(
        "name"=>$data->eventId,
      )) . "</a>" . CHtml::tag("span", array(
        "class"=>"event-icon event-icon event-icon-" . $data->eventId,
        "title"=>Yii::t("event", $data->event->cellName),
      ), Yii::t("event", $data->event->cellName))',
    'repeatHeader'=>true,
    'columns'=>array(
      array(
        'name'=>Yii::t('Results', 'Competition'),
        'type'=>'raw',
        'value'=>'$data->competitionLink',
        'headerHtmlOptions'=>array('class'=>'competition_name'),
      ),
      array(
        'name'=>Yii::t('common', 'Round'),
        'type'=>'raw',
        'value'=>'Yii::t("Rounds", $data->round->cellName)',
        'headerHtmlOptions'=>array('class'=>'round'),
      ),
      array(
        'name'=>Yii::t('Results', 'Place'),
        'type'=>'raw',
        'value'=>'$data->pos',
        'headerHtmlOptions'=>array('class'=>'place'),
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
        'name'=>Yii::t('common', 'Detail'),
        'type'=>'raw',
        'value'=>'$data->getTime("value1") . "&nbsp;" . $data->getTime("value2") . "&nbsp;" . $data->getTime("value3") . "&nbsp;" . $data->getTime("value4") . "&nbsp;" . $data->getTime("value5")',
      ),
    ),
  )); ?>
</div>