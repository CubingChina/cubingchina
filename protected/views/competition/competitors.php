<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form-inline',
    ),
    'method'=>'get',
  )); ?>
    <div class="form-group">
      <label for="event"><?php echo Yii::t('common', 'Psych Sheet'); ?></label>
      <?php echo CHtml::dropDownList('sort', $this->sGet('sort'), Events::getNormalTranslatedEvents(), array(
        'class'=>'form-control',
        'prompt'=>'',
      )); ?>
    </div>
    <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
  <?php $columns = $competition->getEventsColumns(); ?>
  <?php $this->widget('RepeatHeaderGridView', array(
    'dataProvider'=>$model->search($columns),
    // 'filter'=>false,
    // 'enableSorting'=>false,
    'front'=>true,
    'footerOnTop'=>true,
    'columns'=>$columns,
  )); ?>
</div>