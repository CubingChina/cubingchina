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
    <?php if ($competition->fill_passport): ?>
    <div class="bg-info important-border">
      <p>
        <?php echo Yii::t('Registration', '<b class="text-danger">Note</b>: ID number is collected for registration confirmation and purchase of event insurance by the organizers. Please confirm your information is correct in order to avoid unnecessary inconveniences.'); ?>
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
        Yii::app()->language == 'zh_cn' ? '<div class="help-text">如果您提供的身份证件为个人身份证，请注意身份证上的<b class="text-danger">出生日期必须与您在粗饼网注册的信息一致</b>，否则会提示输入错误。需要修改生日信息，请联系admin@cubingchina.com。</div>' : '',
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
    <button type="submit" class="btn btn-theme" id="submit-button"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
</div>
<?php if ($competition->show_regulations): ?>
<div class="modal fade" tabindex="-1" role="dialog" id="regulation-modal">
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
<?php endif; ?>
<?php
if (!$competition->multi_countries) {
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
}
if ($competition->fill_passport) {
  Yii::app()->clientScript->registerScript('registration-passport',
<<<EOT
  $(document).on('change', '#Registration_passport_type', function() {
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
if ($competition->show_regulations) {
  $regulations = Yii::app()->params->regulations;
  $regulationsJson = json_encode(array(
    'common'=>ActiveRecord::getModelAttributeValue($regulations, 'common'),
    'special'=>ActiveRecord::getModelAttributeValue($regulations, 'special'),
  ));
  Yii::app()->clientScript->registerScript('registration-regulation',
<<<EOT
  var regulations = window.regulations = {$regulationsJson};
  var modal = $('#regulation-modal');
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