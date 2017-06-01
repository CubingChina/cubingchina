<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'id'=>'registration-form',
    'htmlOptions'=>array(
    ),
  )); ?>
    <?php if (!$competition->multi_countries): ?>
    <p><b><?php echo Yii::t('Competition', 'Base Entry Fee'); ?></b></p>
    <p><i class="fa fa-rmb"></i><?php echo $competition->getEventFee('entry'); ?></p>
    <?php endif; ?>
    <?php echo Html::formGroup(
      $model, 'events', array(),
      $form->labelEx($model, 'events'),
      $this->widget('EventsForm', array(
        'model'=>$model,
        'competition'=>$competition,
        'name'=>'events',
        'events'=>$competition->getRegistrationEvents(),
        'type'=>'checkbox',
      ), true)
      // $form->error($model, 'events', array('class'=>'text-danger'))
    );?>
    <div id="fee" class="hide">
      <p><b><?php echo Yii::t('Registration', 'Fee (CNY)'); ?></b></p>
      <p id="totalFee"></p>
    </div>
    <?php if ($competition->fill_passport && $this->user->passport_type == User::NO): ?>
    <div class="bg-info important-border">
      <b class="text-danger">
        <?php echo Yii::t('Registration', 'Please fill your ID number {here} before you register.', [
          '{here}'=>CHtml::link(Yii::t('common', 'here'), ['/user/edit']),
        ]); ?>
      </b>
    </div>
    <?php endif; ?>
    <?php if ($competition->entourage_limit): ?>
    <div class="bg-info important-border">
      <?php echo Html::formGroup(
        $model, 'has_entourage', array(),
        $form->labelEx($model, 'has_entourage'),
        $form->dropDownList($model, 'has_entourage', Registration::getYesOrNo(), array(
          'prompt'=>'',
          'class'=>'form-control',
        )),
        Yii::t('Registration', 'Only competitors and registered guests may enter the venue. Each competitor may register at most one guest. Guest registration is {fee} RMB. This fee is necessary for venue liability insurance.', [
          '{fee}'=>$competition->entourage_fee,
        ])
      );?>
      <div class="entourage-info hide">
        <p>
          <?php echo Yii::t('Registration', '<b class="text-danger">Note</b>: ID number is collected for registration confirmation and purchase of event insurance by the organizers. Please confirm your information is correct in order to avoid unnecessary inconveniences.'); ?>
        </p>
        <?php echo Html::formGroup(
          $model, 'entourage_name', array(
          ),
          $form->labelEx($model, 'entourage_name'),
          Html::activeTextField($model, 'entourage_name', array(
            'class'=>'form-control',
          )),
          $form->error($model, 'entourage_name', array('class'=>'text-danger'))
        ); ?>
        <?php echo Html::formGroup(
          $model, 'entourage_passport_type', array(),
          $form->labelEx($model, 'entourage_passport_type'),
          $form->dropDownList($model, 'entourage_passport_type', User::getPassportTypes(), array(
            'prompt'=>'',
            'class'=>'form-control',
          )),
          $form->error($model, 'entourage_passport_type', array('class'=>'text-danger'))
        ); ?>
        <?php echo Html::formGroup(
          $model, 'entourage_passport_name', array(
            'class'=>'hide',
          ),
          $form->labelEx($model, 'entourage_passport_name'),
          Html::activeTextField($model, 'entourage_passport_name', array(
            'class'=>'form-control',
          )),
          $form->error($model, 'entourage_passport_name', array('class'=>'text-danger'))
        ); ?>
        <?php echo Html::formGroup(
          $model, 'entourage_passport_number', array(),
          $form->labelEx($model, 'entourage_passport_number'),
          Html::activeTextField($model, 'entourage_passport_number', array(
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
    </div>
    <?php endif; ?>
    <?php if ($competition->require_avatar): ?>
    <div class="bg-info important-border">
      <p>
        <?php echo Yii::t('Registration', '<b class="text-danger">Note</b>: A photo is needed to finish your registration.'); ?>
      </p>
      <?php echo Html::formGroup(
        $model, 'avatar_type', array(),
        $form->labelEx($model, 'avatar_type', array(
          'label'=>Yii::t('Registration', 'Please choose from the options listed below.'),
        )),
        $form->dropDownList($model, 'avatar_type', Registration::getAvatarTypes($competition), array(
          'prompt'=>'',
          'class'=>'form-control',
          'options'=>array(
            Registration::AVATAR_TYPE_NOW=>array(
              'disabled'=>$this->user->avatar == null,
            ),
          ),
        )),
        $form->error($model, 'avatar_type', array('class'=>'text-danger')),
        CHtml::link(Yii::t('common', 'Upload Now'), array('/user/profile'))
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
    <div class="checkbox">
      <label>
        <input id="disclaimer" value="agree" type="checkbox" name="disclaimer" checked>
        <?php echo Yii::t('Competition', 'I have read and know the {disclaimer} of Cubing China.', [
          '{disclaimer}'=>CHtml::link(Yii::t('Competition', 'disclaimer'), ['/site/page', 'view'=>'disclaimer']),
        ]); ?>
      </label>
    </div>
    <?php echo CHtml::tag('button', [
      'type'=>'submit',
      'class'=>'btn btn-theme',
      'id'=>'submit-button',
      'disabled'=>$competition->fill_passport && $this->user->passport_type == User::NO,
    ], Yii::t('common', 'Submit')); ?>
  <?php $this->endWidget(); ?>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="tips-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><?php echo Yii::t('common', 'Tips'); ?></h4>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" id="cancel-button"><?php echo Yii::t('common', 'Close'); ?></button>
        <button type="button" class="btn btn-theme" id="confirm-button"><?php echo Yii::t('common', 'Confirm'); ?></button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>
<?php
Yii::app()->clientScript->registerScript('registration-disclaimer',
<<<EOT
  $(document).on('change', '#disclaimer', function() {
    $('#submit-button').prop('disabled', !this.checked);
  });
EOT
);

if (!$competition->multi_countries) {
  $basicFee = $competition->getEventFee('entry');
  $entourageFee = $competition->entourage_fee;
Yii::app()->clientScript->registerScript('registration',
<<<EOT
  var basicFee = {$basicFee};
  var entourageFee = {$entourageFee};
  var fee = $('#fee');
  $(document).on('change', '.registration-events', updateFee)
  .on('change', '#Registration_has_entourage', function() {
    $('.entourage-info')[this.value == 1 ? 'removeClass' : 'addClass']('hide');
    updateFee();
  });
  function updateFee() {
    var totalFee = basicFee;
    if ($('#Registration_has_entourage').val() == 1) {
      totalFee += entourageFee;
    }
    $('.registration-events:checked').each(function() {
      totalFee += $(this).data('fee');
    });
    if (totalFee > 0) {
      fee.removeClass('hide').find('#totalFee').text(totalFee);
    } else {
      fee.addClass('hide');
    }
  }
  $('.registration-events').trigger('change');
  $('#Registration_has_entourage').trigger('change');
EOT
  );
}
if ($competition->show_regulations) {
  $regulations = Yii::app()->params->regulations;
  $regulationsJson = json_encode(array(
    'common'=>ActiveRecord::getModelAttributeValue($regulations, 'common'),
    'special'=>ActiveRecord::getModelAttributeValue($regulations, 'special'),
  ));
  Yii::app()->clientScript->registerScript('registration-regulation',
<<<EOT
  var regulations = {$regulationsJson};
  var modal = $('#tips-modal');
  var modalBody = modal.find('.modal-body');
  var callback;
  var cancelCallback;
  $(document).on('change', '.registration-events', function() {
    var that = this;
    var event = $(that).val();
    var msg;
    if (that.checked) {
      switch (event) {
        case '333ft':
        case 'clock':
          msg = regulations.special[event];
          break;
        case '333bf':
          msg = regulations.special.bf;
          break;
        case '444bf':
        case '555bf':
          msg = [regulations.special.bf, regulations.special.lbf, regulations.special.bbf];
          break;
        case '333mbf':
          msg = [regulations.special.bf, regulations.special.lbf];
          break;
      }
      if (msg) {
        showModal(msg, null, function() {
          that.checked = false;
        });
      }
    }
  }).on('click', '#submit-button', function(e) {
    e.preventDefault();
    showModal(regulations.common, function() {
      $('#registration-form').submit();
    });
    return false;
  }).on('click', '#cancel-button', function(e) {
    if (cancelCallback) {
      cancelCallback();
    }
    modal.modal('hide');
  }).on('click', '#confirm-button', function(e) {
    if (callback) {
      callback();
    }
    modal.modal('hide');
  }).on('hide.bs.modal', '#modal', function(e) {
    callback = null;
    cancelCallback = null;
  });
  function showModal(msg, cb, cancelCb) {
    modalBody.html(makeMsg(msg));
    if (cb) {
      callback = cb;
    }
    if (cancelCb) {
      cancelCallback = cancelCb;
    }
    modal.modal({
      backdrop: 'static',
      keyboard: false
    });
  }
  function makeMsg(msg) {
    if (!$.isArray(msg)) {
      msg = [msg];
    }
    var ol = $('<ol>');
    $.each(msg, function(i, v) {
      if (i == msg.length - 1) {
        v = v.replace(/；$/, '。');
      }
      ol.append($('<li>').append(v));
    });
    return ol;
  }
EOT
  );
}
if ($unmetEvents != []) {
  $unmetEvents = json_encode($unmetEvents);
  Yii::app()->clientScript->registerScript('registration-unmet-events',
<<<EOT
  var unmetEvents = {$unmetEvents}
  $.each(unmetEvents, function(event, qualifyingTime) {
    $('.registration-events[value="' + event + '"]').parent().addClass('bg-danger').data('qualifyingTime', qualifyingTime);
  })
EOT
  );
}
