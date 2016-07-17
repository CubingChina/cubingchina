<div class="col-lg-12 competition-wca">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form',
    ),
    'method'=>'get',
    'action'=>$competition->getUrl('statistics', array('type'=>'sum-of-ranks')),
  )); ?>
    <div class="form-group row">
      <?php foreach (array_chunk($events, 3, true) as $events): ?>
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
        <label for="Competition_year"><?php echo Yii::t('common', 'Gender'); ?></label>
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
        'pageSize'=>$statistic['count'],
        'pageVar'=>'page',
      ),
      'sliceData'=>false,
      'totalItemCount'=>$statistic['count'],
    )),
    'template'=>'{items}',
    'enableSorting'=>false,
    'front'=>true,
    'rankKey'=>$statistic['rankKey'],
    'rank'=>0,
    'columns'=>array_map(function($column) {
      $column['header'] = Yii::app()->evaluateExpression($column['header']);
      return $column;
    }, $statistic['columns']),
  )); ?>
</div>