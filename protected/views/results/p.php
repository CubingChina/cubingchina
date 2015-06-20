<div class="col-lg-12 results-person">
  <h1 class="text-center"><?php echo $user && $user->id === Yii::app()->user->id ? CHtml::link($person->name, array('/user/profile')) : $person->name; ?></h1>
  <?php if ($user && $user->avatar): ?>
  <div class="text-center"><?php echo $user->avatar->img; ?></div>
  <?php endif ?>
  <div class="panel panel-info person-detail">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('Results', 'Name'); ?>:</span>
          <span class="info-value"><?php echo $person->name; ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('common', 'Region'); ?>:</span>
          <span class="info-value"><?php echo Yii::t('Region', $person->country->name); ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('Results', 'Competitions'); ?>:</span>
          <span class="info-value"><?php echo $person->competitionNum; ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('common', 'WCA ID'); ?>:</span>
          <span class="info-value"><?php echo Persons::getWCALinkByNameNId(CHtml::image('/f/images/wca.png', $person->name, array('class'=>'wca-competition')), $person->id), $person->id; ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('common', 'Gender'); ?>:</span>
          <span class="info-value"><?php echo strtolower($person->gender) == 'f' ? Yii::t('common', 'Female') : Yii::t('common', 'Male'); ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('Results', 'Emulation'); ?>:</span>
          <span class="info-value"><?php echo sprintf('%d.%02d.%02d - %d.%02d.%02d', $firstCompetition->year, $firstCompetition->month, $firstCompetition->day, $lastCompetition->year, $lastCompetition->month, $lastCompetition->day); ?></span>
        </div>
      </div>
    </div>
  </div>
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
        'name'=>Yii::t('statistics', 'NR'),
        'type'=>'raw',
        'value'=>'$data->getRank("countryRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('statistics', 'CR'),
        'type'=>'raw',
        'value'=>'$data->getRank("continentRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('statistics', 'WR'),
        'type'=>'raw',
        'value'=>'$data->getRank("worldRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('common', 'Single'),
        'type'=>'raw',
        'value'=>'CHtml::link(Results::formatTime($data->best, $data->eventId), array(
          "/results/rankings",
          "event"=>$data->eventId,
          "region"=>$data->person->countryId,
        ))',
        // 'headerHtmlOptions'=>array('class'=>'best'),
      ),
      array(
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->average("best")',
        // 'headerHtmlOptions'=>array('class'=>'best'),
      ),
      array(
        'name'=>Yii::t('statistics', 'WR'),
        'type'=>'raw',
        'value'=>'$data->average("worldRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('statistics', 'CR'),
        'type'=>'raw',
        'value'=>'$data->average("continentRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('statistics', 'NR'),
        'type'=>'raw',
        'value'=>'$data->average("countryRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'header'=>Yii::t('statistics', 'Gold'),
        'value'=>'$data->medals["gold"] ?: ""',
      ),
      array(
        'header'=>Yii::t('statistics', 'Silver'),
        'value'=>'$data->medals["silver"] ?: ""',
      ),
      array(
        'header'=>Yii::t('statistics', 'Bronze'),
        'value'=>'$data->medals["bronze"] ?: ""',
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
    'groupHeader'=>'$data->competitionLink',
    'columns'=>array(
      array(
        'name'=>Yii::t('common', 'Event'),
        'type'=>'raw',
        'value'=>'CHtml::tag("span", array(
          "class"=>"event-icon event-icon event-icon-" . $data->eventId,
          "title"=>Yii::t("event", $data->event->cellName),
        ), Yii::t("event", $data->event->cellName))',
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
      ),
      array(
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->getTime("average")',
      ),
      array(
        'name'=>Yii::t('common', 'Detail'),
        'type'=>'raw',
        'value'=>'$data->detail',
      ),
    ),
  )); ?>
  <?php endif; ?>
  <?php if (array_sum($overAll) > 0): ?>
  <div class="row">
    <?php $overAllDataProvider = new CArrayDataProvider(array($overAll), array(
      'pagination'=>false,
      'sort'=>false,
    )); ?>
    <?php if ($overAll['gold'] + $overAll['silver'] + $overAll['bronze'] > 0): ?>
    <div class="col-sm-6 col-xs-12">
      <h2><?php echo Yii::t('Results', 'Overall Medal Collection'); ?></h2>
      <?php
      $this->widget('GridView', array(
        'dataProvider'=>$overAllDataProvider,
        'front'=>true,
        'template'=>'{items}',
        'columns'=>array(
          array(
            'header'=>Yii::t('statistics', 'Gold'),
            'value'=>'$data["gold"] ?: ""',
          ),
          array(
            'header'=>Yii::t('statistics', 'Silver'),
            'value'=>'$data["silver"] ?: ""',
          ),
          array(
            'header'=>Yii::t('statistics', 'Bronze'),
            'value'=>'$data["bronze"] ?: ""',
          ),
        ),
      )); ?>
    </div>
    <?php endif; ?>
    <?php if ($overAll['WR'] + $overAll['CR'] + $overAll['NR'] > 0): ?>
    <div class="col-sm-6 col-xs-12">
      <h2><?php echo Yii::t('Results', 'Overall Record Collection'); ?></h2>
      <?php
      $this->widget('GridView', array(
        'dataProvider'=>$overAllDataProvider,
        'front'=>true,
        'template'=>'{items}',
        'columns'=>array(
          array(
            'name'=>Yii::t('Results', 'WR'),
            'type'=>'raw',
            'value'=>'$data["WR"] ?: ""',
          ),
          array(
            'name'=>Yii::t('Results', 'CR'),
            'type'=>'raw',
            'value'=>'$data["CR"] ?: ""',
          ),
          array(
            'name'=>Yii::t('Results', 'NR'),
            'type'=>'raw',
            'value'=>'$data["NR"] ?: ""',
          ),
        ),
      )); ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php if (!empty($historyWR)): ?>
  <h2><?php echo Yii::t('Results', 'History of World Records'); ?></h2>
  <?php
  $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider($historyWR, array(
      'pagination'=>false,
      'sort'=>false,
    )),
    'itemsCssClass'=>'table table-condensed table-hover table-boxed',
    'groupKey'=>'eventId',
    'groupHeader'=>'CHtml::tag("span", array(
        "class"=>"event-icon event-icon event-icon-" . $data->eventId,
        "title"=>Yii::t("event", $data->event->cellName),
      ), Yii::t("event", $data->event->cellName))',
    'columns'=>array(
      array(
        'name'=>Yii::t('common', 'Event'),
        'type'=>'raw',
        'value'=>'',
      ),
      array(
        'name'=>Yii::t('common', 'Single'),
        'type'=>'raw',
        'value'=>'$data->regionalSingleRecord == "WR" ? $data->getTime("best") : ""',
      ),
      array(
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->regionalAverageRecord == "WR" ? $data->getTime("average"): ""',
      ),
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
        'name'=>Yii::t('common', 'Detail'),
        'type'=>'raw',
        'value'=>'$data->getDetail(true)',
      ),
    ),
  )); ?>
  <?php endif; ?>
  <?php if (!empty($historyCR)): ?>
  <h2><?php echo Yii::t('Results', 'History of Continental Records'); ?></h2>
  <?php
  $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider($historyCR, array(
      'pagination'=>false,
      'sort'=>false,
    )),
    'itemsCssClass'=>'table table-condensed table-hover table-boxed',
    'groupKey'=>'eventId',
    'groupHeader'=>'CHtml::tag("span", array(
        "class"=>"event-icon event-icon event-icon-" . $data->eventId,
        "title"=>Yii::t("event", $data->event->cellName),
      ), Yii::t("event", $data->event->cellName))',
    'columns'=>array(
      array(
        'name'=>Yii::t('common', 'Event'),
        'type'=>'raw',
        'value'=>'',
      ),
      array(
        'name'=>Yii::t('common', 'Single'),
        'type'=>'raw',
        'value'=>'!in_array($data->regionalSingleRecord, array("WR", "NR", "")) ? $data->getTime("best") : ""',
      ),
      array(
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'!in_array($data->regionalAverageRecord, array("WR", "NR", "")) ? $data->getTime("average"): ""',
      ),
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
        'name'=>Yii::t('common', 'Detail'),
        'type'=>'raw',
        'value'=>'$data->getDetail(true)',
      ),
    ),
  )); ?>
  <?php endif; ?>
  <?php if (!empty($historyNR)): ?>
  <h2><?php echo Yii::t('Results', 'History of National Records'); ?></h2>
  <?php
  $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider($historyNR, array(
      'pagination'=>false,
      'sort'=>false,
    )),
    'itemsCssClass'=>'table table-condensed table-hover table-boxed',
    'groupKey'=>'eventId',
    'groupHeader'=>'CHtml::tag("span", array(
        "class"=>"event-icon event-icon event-icon-" . $data->eventId,
        "title"=>Yii::t("event", $data->event->cellName),
      ), Yii::t("event", $data->event->cellName))',
    'columns'=>array(
      array(
        'name'=>Yii::t('common', 'Event'),
        'type'=>'raw',
        'value'=>'',
      ),
      array(
        'name'=>Yii::t('common', 'Single'),
        'type'=>'raw',
        'value'=>'$data->regionalSingleRecord == "NR" ? $data->getTime("best") : ""',
      ),
      array(
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->regionalAverageRecord == "NR" ? $data->getTime("average"): ""',
      ),
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
        'name'=>Yii::t('common', 'Detail'),
        'type'=>'raw',
        'value'=>'$data->getDetail(true)',
      ),
    ),
  )); ?>
  <?php endif; ?>
  <h2><?php echo Yii::t('Persons', 'History'); ?></h2>
  <?php
  $this->widget('GroupRankGridView', array(
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
    'rankKey'=>'competitionId',
    'repeatHeader'=>true,
    'columns'=>array(
      array(
        'class'=>'RankColumn',
        'name'=>Yii::t('Results', 'Competition'),
        'type'=>'raw',
        'value'=>'$displayRank ? $data->competitionLink : ""',
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
        'value'=>'$data->detail',
      ),
    ),
  )); ?>
</div>