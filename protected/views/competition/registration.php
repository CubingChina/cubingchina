<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'id'=>'login-form',
    'htmlOptions'=>array(
    ),
  )); ?>
    <p><b><?php echo Yii::t('Competition', 'Base Entry Fee'); ?></b></p>
    <p><?php echo $competition->getEventFee('entry'); ?></p>
    <?php echo Html::formGroup(
      $model, 'events', array(),
      $form->labelEx($model, 'events'),
      $this->widget('EventsForm', array(
        'model'=>$model,
        'competition'=>$competition,
        'name'=>'events',
        'events'=>$competition->getRegistrationEvents(),
        'type'=>'checkbox',
      ), true),
      $form->error($model, 'events', array('class'=>'text-danger'))
    );?>
    <div id="fee" class="hide">
      <p><b><?php echo Yii::t('Registration', 'Fee (CNY)'); ?></b></p>
      <p id="totalFee"></p>
    </div>
    <?php if ($competition->fill_passport): ?>
    <?php echo Html::formGroup(
      $model, 'passport_type', array(),
      $form->labelEx($model, 'passport_type'),
      $form->dropDownList($model, 'passport_type', Registration::getPassportTypes(), array(
        'prompt'=>'',
        'class'=>'form-control',
      )),
      $form->error($model, 'passport_type', array('class'=>'text-danger'))
    ); ?>
    <?php echo Html::formGroup(
      $model, 'passport_number', array(),
      $form->labelEx($model, 'passport_number'),
      Html::activeTextField($model, 'passport_number', array(
        'class'=>'form-control',
      )),
      $form->error($model, 'passport_number', array('class'=>'text-danger'))
    ); ?>
    <?php endif; ?>
    <?php echo Html::formGroup(
      $model, 'comments', array(),
      $form->labelEx($model, 'comments'),
      $form->textArea($model, 'comments', array(
        'class'=>'form-control',
        'rows'=>4,
      )),
      $form->error($model, 'comments', array('class'=>'text-danger'))
    ); ?>
    <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
</div>
<?php
$basicFee = $competition->getEventFee('entry');
Yii::app()->clientScript->registerScript('registration',
<<<EOT
  var basicFee = {$basicFee};
  var fee = $('#fee');
  $(document).on('change', '.registration-events', function() {
    var totalFee = basicFee;
    $('.registration-events:checked').each(function() {
      totalFee += $(this).data('fee');
    });
    if (totalFee > 0) {
      fee.removeClass('hide').find('#totalFee').text(totalFee);
    } else {
      fee.addClass('hide');
    }
  });
EOT
);