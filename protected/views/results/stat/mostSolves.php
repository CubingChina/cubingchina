<div class="col-lg-12 competition-wca">
  <p class="text-muted"><small><?php echo Yii::t('statistics', 'Generated on {time}.', array(
    '{time}'=>date('Y-m-d H:i:s', $time),
  )); ?></small></p>
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form',
    ),
    'method'=>'get',
    'action'=>array(
      '/results/statistics',
      'name'=>'most-solves',
    ),
  )); ?>
    <div class="form-group row">
      <?php foreach (array_chunk(Events::getNormalTranslatedEvents(), 3, true) as $events): ?>
      <div class="col-md-4 col-xs-6">
        <div class="row">
        <?php foreach ($events as $eventId=>$name): ?>
          <div class="col-xs-4">
            <div class="checkbox">
              <label>
                <?php echo CHtml::checkBox('event[]', in_array("$eventId", $eventIds), array(
                  'value'=>$eventId,
                )); ?>
                <?php echo CHtml::tag('span', array(
                  'class'=>'event-icon event-icon-' . $eventId,
                ), '&nbsp;'); ?>
              </label>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="form-inline">
      <div class="form-group">
        <label for="region"><?php echo Yii::t('common', 'Region'); ?></label>
        <?php echo CHtml::dropDownList('region', $region, Region::getWCARegions(), array(
          'class'=>'form-control',
        )); ?>
      </div>
      <div class="form-group">
        <label for="gender"><?php echo Yii::t('common', 'Gender'); ?></label>
        <?php echo CHtml::dropDownList('gender', $gender, Persons::getGenders(), array(
          'class'=>'form-control',
        )); ?>
      </div>
      <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
    </div>
  <?php $this->endWidget(); ?>
  <?php
  $this->widget('RankGridView', array(
    'dataProvider'=>new NonSortArrayDataProvider($statistic['rows'], array(
      'pagination'=>array(
        'pageSize'=>Statistics::$limit,
        'pageVar'=>'page',
      ),
      'sliceData'=>false,
      'totalItemCount'=>$statistic['count'],
    )),
    'template'=>'{items}{pager}',
    'enableSorting'=>false,
    'front'=>true,
    'rankKey'=>$statistic['rankKey'],
    'rank'=>$statistic['rank'],
    'count'=>($page - 1) * 100,
    'columns'=>array_map(function($column) {
      $column['header'] = Yii::app()->evaluateExpression($column['header']);
      return $column;
    }, $statistic['columns']),
  )); ?>
</div>