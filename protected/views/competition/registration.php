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
    <div class="bg-info important-border">
      <p>
        <?php echo Yii::t('Registration', '<b class="text-danger">Note</b>: Personal ID is collected for verification and insurance purchasing.<br>The competitors undertake any consequences on <b class="text-danger">failing in registration</b> and <b class="text-danger">insuring</b> due to incorrect personal information.'); ?>
      </p>
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
        $model, 'passport_name', array(
          'class'=>'hide',
        ),
        $form->labelEx($model, 'passport_name'),
        Html::activeTextField($model, 'passport_name', array(
          'class'=>'form-control',
        )),
        $form->error($model, 'passport_name', array('class'=>'text-danger'))
      ); ?>
      <?php echo Html::formGroup(
        $model, 'passport_number', array(),
        $form->labelEx($model, 'passport_number'),
        Html::activeTextField($model, 'passport_number', array(
          'class'=>'form-control',
        )),
        $form->error($model, 'passport_number', array('class'=>'text-danger'))
      ); ?>
      <?php echo Html::formGroup(
        $model, 'repeatPassportNumber', array(),
        $form->labelEx($model, 'repeatPassportNumber'),
        Html::activeTextField($model, 'repeatPassportNumber', array(
          'class'=>'form-control',
        )),
        $form->error($model, 'repeatPassportNumber', array('class'=>'text-danger'))
      ); ?>
    </div>
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
if ($competition->fill_passport) {
  Yii::app()->clientScript->registerScript('registrationAddtion',
<<<EOT
  $(document).on('change', '.registration-events', function() {
    var event = $(this).val();
    if (this.checked) {
      console.log(event)
    }
  }).on('change', '#Registration_passport_type', function() {
    changePassportType(true);
  }).on('contextmenu', '#Registration_passport_number, #Registration_repeatPassportNumber', function(e) {
    e.preventDefault();
    return false;
  }).on('keydown', '#Registration_passport_number, #Registration_repeatPassportNumber', function(e) {
    if (e.which == 86 && (e.ctrlKey || e.metaKey)) {
      e.preventDefault();
      return false;
    }
  });
  $('label[for="Registration_passport_name"]').append('<span class="required">*</span>');
  changePassportType();
  function changePassportType(focus) {
    var type = $('#Registration_passport_type').val();
    if (type == 3) {
      $('#Registration_passport_name').parent().removeClass('hide');
      if (focus) {
        $('#Registration_passport_name').focus();
      }
    } else {
      $('#Registration_passport_name').parent().addClass('hide');
    }
  }
EOT
  );
}