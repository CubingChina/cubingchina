<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form-inline',
    ),
    'method'=>'get',
    'action'=>$competition->getUrl('competitors'),
  )); ?>
    <div class="form-group">
      <label for="event"><?php echo Yii::t('common', 'Psych Sheet'); ?></label>
      <?php echo CHtml::dropDownList('sort', $this->sGet('sort'), array_intersect_key(Events::getNormalTranslatedEvents(), $competition->getRegistrationEvents()), array(
        'class'=>'form-control',
        'prompt'=>'',
      )); ?>
    </div>
    <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
  <div class="form-group show-on-small">
    <?php echo Html::switch('show-all', false, [
      'data-label-text'=>Yii::t('common', 'Show all information?'),
      'data-label-width'=>'150',
    ]); ?>
  </div>
  <?php $columns = $competition->getEventsColumns(); ?>
  <?php $this->widget('GridView', array(
    'id'=>'competitors',
    'dataProvider'=>$model->search($columns),
    'front'=>true,
    'footerOnTop'=>true,
    'columns'=>$columns,
  )); ?>
</div>
<?php
Yii::app()->clientScript->registerScript('schedule',
<<<EOT
  $(document).on('switchChange.bootstrapSwitch', '#show-all', function(e) {
    var func = $(this).prop('checked') ? 'addClass' : 'removeClass';
    $('#competitors')[func]('show-all')
  });
EOT
);
