<div class="col-lg-12 summary-person results-person">
  <?php if ($competitions == 0): ?>
  <p><?php echo Yii::t('summary', '{genderPronoun} was very lazy so that nothing left.', [
    '{genderPronoun}'=>strtolower($person->gender) == 'f' ? Yii::t('common', 'She') : Yii::t('common', 'He'),
  ]); ?></p>
  <?php else: ?>
  <p>
    <?php echo Yii::t('summary', 'In the past year ({year}), {personName} competed in {competitions} competition{cs} and {rounds} round{rs} across {events} event{es}.', [
      '{year}'=>$year,
      '{personName}'=>$person->name,
      '{competitions}'=>CHtml::tag('span', ['class'=>'num'], $competitions),
      '{rounds}'=>CHtml::tag('span', ['class'=>'num'], $rounds),
      '{events}'=>CHtml::tag('span', ['class'=>'num'], $events),
      '{cs}'=>$competitions > 1 ? 's' : '',
      '{rs}'=>$rounds > 1 ? 's' : '',
      '{es}'=>$events > 1 ? 's' : '',
    ]); ?>
  </p>
  <?php if (($temp = array_sum($records)) != 0): ?>
  <h3><?php echo Yii::t('common', 'Records'); ?></h3>
  <p>
    <?php echo Yii::t('summary', '{genderPronoun} broke {record} record{rs}, {recordsDetail}.', [
      '{genderPronoun}'=>strtolower($person->gender) == 'f' ? Yii::t('common', 'She') : Yii::t('common', 'He'),
      '{record}'=>CHtml::tag('span', ['class'=>'num'], $temp),
      '{rs}'=>$temp > 1 ? 's' : '',
      '{recordsDetail}'=>Summary2016::getRecordsDetail($records, $person),
    ]); ?>
  </p>
  <div class="col-lg-12 row">
    <?php
    $this->widget('GroupGridView', array(
      'dataProvider'=>new CArrayDataProvider($recordList, array(
        'pagination'=>false,
        'sort'=>false,
      )),
      'itemsCssClass'=>'table table-condensed table-hover table-boxed',
      'groupKey'=>'eventId',
      'groupHeader'=>'Events::getFullEventNameWithIcon($data->eventId)',
      'columns'=>array(
        array(
          'name'=>Yii::t('common', 'Event'),
          'type'=>'raw',
          'value'=>'',
        ),
        array(
          'name'=>Yii::t('common', 'Single'),
          'type'=>'raw',
          'value'=>'$data->regionalSingleRecord != "" ? $data->getTime("best", false, true) : ""',
        ),
        array(
          'name'=>Yii::t('common', 'Average'),
          'type'=>'raw',
          'value'=>'$data->regionalAverageRecord != "" ? $data->getTime("average", false, true): ""',
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
  </div>
  <div class="clearfix"></div>
  <?php endif; ?>
  <?php if (($temp = array_sum($medals)) != 0): ?>
  <h3><?php echo Yii::t('common', 'Podiums'); ?></h3>
  <p>
    <?php echo Yii::t('summary', '{genderPronoun} has been on the podium {medal} times, {medalsDetail}.', [
      '{genderPronoun}'=>strtolower($person->gender) == 'f' ? Yii::t('common', 'She') : Yii::t('common', 'He'),
      '{medal}'=>CHtml::tag('span', ['class'=>'num'], $temp),
      '{ms}'=>$temp > 1 ? 's' : '',
      '{medalsDetail}'=>Summary2016::getMedalsDetail($medals, $person),
    ]); ?>
  </p>
  <div class="col-md-6 col-lg-4 row">
    <?php
    $this->widget('GridView', array(
      'dataProvider'=>new CArrayDataProvider($medalList, array(
        'pagination'=>false,
        'sort'=>false,
      )),
      'front'=>true,
      'template'=>'{items}',
      'columns'=>array(
        array(
          'type'=>'raw',
          'value'=>'Events::getFullEventNameWithIcon($data["event"])',
          'header'=>Yii::t('common', 'Event'),
        ),
        array(
          'name'=>'gold',
          'value'=>'$data["gold"] ?: ""',
          'header'=>Yii::t('statistics', 'Gold'),
        ),
        array(
          'name'=>'silver',
          'value'=>'$data["silver"] ?: ""',
          'header'=>Yii::t('statistics', 'Silver'),
        ),
        array(
          'name'=>'bronze',
          'value'=>'$data["bronze"] ?: ""',
          'header'=>Yii::t('statistics', 'Bronze'),
        ),
      ),
    )); ?>
  </div>
  <div class="clearfix"></div>
  <?php endif; ?>
  <h3><?php echo Yii::t('statistics', 'Solves/Attempts'); ?></h3>
  <p>
    <?php echo Yii::t('summary', '{genderPronoun} attempted {attempt} solves and completed {solve}.', [
      '{genderPronoun}'=>strtolower($person->gender) == 'f' ? Yii::t('common', 'She') : Yii::t('common', 'He'),
      '{attempt}'=>CHtml::tag('span', ['class'=>'num'], $solves['total']['attempt']),
      '{solve}'=>CHtml::tag('span', ['class'=>'num'], $solves['total']['solve']),
    ]); ?>
  </p>
  <div class="col-md-6 col-lg-4 row">
    <?php
    $this->widget('GridView', array(
      'dataProvider'=>new CArrayDataProvider($solves['events'], array(
        'pagination'=>false,
        'sort'=>false,
      )),
      'front'=>true,
      'template'=>'{items}',
      'columns'=>array(
        array(
          'type'=>'raw',
          'value'=>'Events::getFullEventNameWithIcon($data["event"])',
          'header'=>Yii::t('common', 'Event'),
        ),
        array(
          'name'=>'solve',
          'header'=>Yii::t('statistics', 'Solves'),
        ),
        array(
          'name'=>'attempt',
          'header'=>Yii::t('statistics', 'Attempts'),
        ),
      ),
    )); ?>
  </div>
  <div class="clearfix"></div>
  <?php if ($personalBests != []): ?>
  <h3><?php echo Yii::t('Results', 'Personal Bests'); ?></h3>
  <p>
    <?php echo Yii::t('summary', '{genderPronoun} broke {genderPronoun2} personal best {total} times, including {best} single{bs} and {average} average{as}.', [
      '{genderPronoun}'=>strtolower($person->gender) == 'f' ? Yii::t('common', 'She') : Yii::t('common', 'He'),
      '{genderPronoun2}'=>strtolower($person->gender) == 'f' ? Yii::t('common', 'her') : Yii::t('common', 'his'),
      '{total}'=>CHtml::tag('span', ['class'=>'num'], $personalBests['total']['total']),
      '{best}'=>CHtml::tag('span', ['class'=>'num'], $personalBests['total']['best']),
      '{average}'=>CHtml::tag('span', ['class'=>'num'], $personalBests['total']['average']),
      '{bs}'=>$personalBests['total']['best'] > 1 ? 's' : '',
      '{as}'=>$personalBests['total']['average'] > 1 ? 's' : '',
    ]); ?>
  </p>
  <div class="col-md-6 col-lg-4 row">
    <?php
    $this->widget('GridView', array(
      'dataProvider'=>new CArrayDataProvider($personalBests['events'], array(
        'pagination'=>false,
        'sort'=>false,
      )),
      'front'=>true,
      'template'=>'{items}',
      'columns'=>array(
        array(
          'type'=>'raw',
          'value'=>'Events::getFullEventNameWithIcon($data["event"])',
          'header'=>Yii::t('common', 'Event'),
        ),
        array(
          'name'=>'total',
          'header'=>Yii::t('Results', 'Times'),
        ),
        array(
          'name'=>'best',
          'header'=>Yii::t('common', 'Single'),
        ),
        array(
          'name'=>'average',
          'header'=>Yii::t('common', 'Average'),
        ),
      ),
    )); ?>
  </div>
  <div class="clearfix"></div>
  <?php endif; ?>
  <h3><?php echo Yii::t('common', 'Cubers'); ?></h3>
  <p>
    <?php echo Yii::t('summary', '{genderPronoun} met {cubers} cubers{moreThanOne}.{onlyOne}', [
      '{genderPronoun}'=>strtolower($person->gender) == 'f' ? Yii::t('common', 'She') : Yii::t('common', 'He'),
      '{cubers}'=>CHtml::tag('span', ['class'=>'num'], $cubers),
      '{moreThanOne}'=>$cubers == $onceCubers ? '' : Yii::t('summary', ', {moreThanOne} of them competed with {genderPronoun3} more than once', [
        '{genderPronoun3}'=>strtolower($person->gender) == 'f' ? Yii::t('summary', 'her') : Yii::t('common', 'him'),
        '{moreThanOne}'=>CHtml::tag('span', ['class'=>'num'], $cubers - $onceCubers),
      ]),
      '{onlyOne}'=>$onlyOne === false ? '' : Yii::t('summary', ' {cuber} was the only one who accompanied the whole {competitions} competitions.', [
        '{cuber}'=>Persons::getLinkByNameNId($onlyOne["personName"], $onlyOne["personId"]),
       '{competitions}'=>CHtml::tag('span', ['class'=>'num'], $competitions),
      ]),
    ]); ?>
  </p>
  <div class="row">
    <?php if ($closestCubers !== []): ?>
    <div class="col-md-6 col-lg-4">
      <?php
      $this->widget('GridView', array(
        'dataProvider'=>new CArrayDataProvider($closestCubers, array(
          'pagination'=>false,
          'sort'=>false,
        )),
        'front'=>true,
        'template'=>'{items}',
        'columns'=>array(
          array(
            'name'=>Yii::t('Results', 'Person'),
            'type'=>'raw',
            'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
          ),
          array(
            'name'=>'count',
            'header'=>Yii::t('Results', 'Shared Competitions'),
          ),
        ),
      )); ?>
    </div>
    <?php endif; ?>
    <?php if (count($seenCubers) > 2): ?>
    <div class="col-md-6 col-lg-4">
      <?php
      $this->widget('GridView', array(
        'dataProvider'=>new CArrayDataProvider($seenCubers, array(
          'pagination'=>false,
          'sort'=>false,
        )),
        'front'=>true,
        'template'=>'{items}',
        'columns'=>array(
          array(
            'name'=>'count',
            'header'=>Yii::t('Results', 'Shared Competitions'),
          ),
          array(
            'name'=>'competitors',
            'header'=>Yii::t('Results', 'Competitors'),
          ),
        ),
      )); ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="clearfix"></div>
  <?php if ($visitedCities != 0): ?>
  <h3><?php echo Yii::t('common', 'Cities'); ?></h3>
  <p>
    <?php echo Yii::t('summary', 'In China, {genderPronoun} visited {cities} cit{ies} while competing.', [
      '{genderPronoun}'=>strtolower($person->gender) == 'f' ? Yii::t('common', 'she') : Yii::t('common', 'he'),
      '{cities}'=>CHtml::tag('span', ['class'=>'num'], $visitedCities),
      '{ies}'=>$visitedCities > 1 ? 'ies' : 'y',
    ]); ?>
  </p>
  <div class="col-md-6 col-lg-4 row">
    <?php
    $this->widget('GridView', array(
      'dataProvider'=>new CArrayDataProvider($visitedCityList, array(
        'pagination'=>false,
        'sort'=>false,
      )),
      'front'=>true,
      'template'=>'{items}',
      'columns'=>array(
        array(
          'name'=>'count',
          'header'=>Yii::t('Results', 'Times'),
        ),
        array(
          'header'=>Yii::t('common', 'City'),
          'value'=>'Yii::t("Region", ActiveRecord::getModelAttributeValue($data, "name"))',
        ),
      ),
    )); ?>
  </div>
  <div class="clearfix"></div>
  <?php endif; ?>
  <?php endif; ?>
</div>
