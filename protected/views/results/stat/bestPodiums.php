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
      'name'=>'best-podiums',
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
                <?php echo CHtml::radioButton('event', $eventId == $event, array(
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
    <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
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
    'count'=>($page - 1) * BestPodiums::$limit,
    'columns'=>array_map(function($column) {
      $column['header'] = Yii::app()->evaluateExpression($column['header']);
      return $column;
    }, $statistic['columns']),
  )); ?>
</div>