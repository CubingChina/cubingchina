<?php
$this->renderPartial('side', $_data_);
?>
<div class="content-wrapper col-md-10 col-sm-9">
  <h3 class="has-divider text-highlight">
    <?php echo Yii::t('common', 'Edit preferred events'); ?>
  </h3>
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
    ),
  )); ?>
  <?php echo $form->hiddenField($user, 'preferredEvents[]', ['value'=>'']); ?>
  <?php echo Html::formGroup(
    $user, 'preferredEvents', array(),
    $form->labelEx($user, 'preferredEvents'),
    $this->widget('EventsForm', array(
      'model'=>$user,
      'name'=>'preferredEvents',
      'events'=>Events::getAllEvents(),
      'type'=>'checkbox',
    ), true)
  );?>
  <button type="submit" class="btn btn-theme btn-lg"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
</div>
