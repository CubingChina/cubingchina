<?php if ($registration->isEditable()): ?>
<div class="row">
  <div class="col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
    <div class="panel panel-info">
      <div class="panel-heading">
        <a data-toggle="collapse" href="#edit"><?php echo Yii::t('Registration', 'Edit Registration'); ?></a>
      </div>
      <div class="panel-body collapse" id="edit">
        <?php $form = $this->beginWidget('ActiveForm', array(
          'id'=>'cancel-form',
          'htmlOptions'=>array(
          ),
        )); ?>
        <input type="hidden" name="update" value="1">
        <?php echo Html::formGroup(
          $registration, 'events', array(),
          $form->labelEx($registration, 'events'),
          $this->widget('EventsForm', array(
            'model'=>$registration,
            'competition'=>$competition,
            'name'=>'events',
            'events'=>$registration->getEditableEvents(),
            'unmetEvents'=>$unmetEvents,
            'shouldDisableUnmetEvents'=>$competition->shouldDisableUnmetEvents,
            'type'=>'checkbox',
          ), true)
          // $form->error($model, 'events', array('class'=>'text-danger'))
        );?>
        <?php echo CHtml::tag('button', [
          'id'=>'update',
          'type'=>'submit',
          'class'=>'btn btn-primary',
        ], Yii::t('common', 'Submit')); ?>
        <?php $this->endWidget(); ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
