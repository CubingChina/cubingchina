<?php $this->renderPartial('operation', $_data_); ?>
<?php if ($canRegister): ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php $form = $this->beginWidget('ActiveForm', array(
    'id'=>'registration-form',
    'htmlOptions'=>array(
    ),
  )); ?>
    <?php if (!$competition->multi_countries): ?>
    <p><b><?php echo Yii::t('Competition', 'Base Entry Fee'); ?></b></p>
    <p>
      <i class="fa fa-rmb"></i><span id="entry-fee"><?php echo $competition->getEventFee(Competition::EVENT_FEE_ENTRY); ?></span>
      <?php if ($competition->isWCACompetition() && $competition->date >= Competition::WCA_DUES_START): ?>
      <?php echo Yii::t('Competition', '(includes {dues} for WCA Dues)', [
        '{dues}' => '<i class="fa fa-rmb"></i>' . $competition->getEventFee(Competition::EVENT_FEE_WCA_DUES, Competition::STAGE_FIRST),
      ]); ?>
      <?php endif; ?>
    </p>
    <?php endif; ?>
    <?php echo Html::formGroup(
      $model, 'events', array(),
      $form->labelEx($model, 'events'),
      $this->widget('EventsForm', array(
        'model'=>$model,
        'competition'=>$competition,
        'name'=>'events',
        'events'=>$competition->getRegistrationEvents(),
        'unmetEvents'=>$unmetEvents,
        'shouldDisableUnmetEvents'=>$competition->shouldDisableUnmetEvents,
        'type'=>'checkbox',
      ), true)
      // $form->error($model, 'events', array('class'=>'text-danger'))
    );?>
    <div id="fee" class="hide">
      <p><b><?php echo Yii::t('Registration', $competition->multi_countries ? 'Fee' : 'Fee (CNY)'); ?></b></p>
      <p id="totalFee"></p>
    </div>
    <hr>
    <?php if ($competition->fill_passport && $this->user->passport_type == User::NO): ?>
    <div class="bg-danger important-border">
      <b class="text-danger">
        <?php echo Yii::t('Registration', 'Please fill your ID number {here} before you register.', [
          '{here}'=>CHtml::link(Yii::t('common', 'here'), ['/user/edit']),
        ]); ?>
      </b>
    </div>
    <?php endif; ?>
    <?php if ($competition->entourage_limit): ?>
    <div class="bg-info important-border<?php if ($model->hasErrors('has_entourage')) echo ' bg-danger'; ?>">
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
          $form->error($model, 'entourage_passport_number', array('class'=>'text-danger'))
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
    <?php if ($competition->t_shirt): ?>
    <div class="bg-info important-border">
      <p>
        <?php echo Yii::t('Registration', '<b class="text-danger">Note</b>: Please choose your T-shirt size according the below sizes.'); ?>
      </p>
      <?php echo Html::formGroup(
        $model, 't_shirt_size', array(),
        $form->labelEx($model, 't_shirt_size', array(
          'label'=>Yii::t('Registration', 'Please choose from the options listed below.'),
        )),
        $form->dropDownList($model, 't_shirt_size', Registration::getTShirtSizes(), array(
          'prompt'=>'',
          'class'=>'form-control',
        )),
        $form->error($model, 't_shirt_size', array('class'=>'text-danger'))
      ); ?>
      <p>
        <a href="/f/images/t-shirt-size.jpg" target="_blank">
          <img src="/f/images/t-shirt-size.jpg" alt="">
        </a>
      </p>
    </div>
    <?php endif; ?>
    <?php if ($competition->staff): ?>
    <div class="bg-info important-border<?php if ($model->hasErrors('staff_type')) echo ' bg-danger'; ?>">
      <?php echo Html::formGroup(
        $model, 'staff_type', array(),
        $form->labelEx($model, 'staff_type'),
        $form->dropDownList($model, 'staff_type', Registration::getStaffTypes(), array(
          'prompt'=>'',
          'class'=>'form-control',
        ))
      );?>
      <div class="staff-info hide">
        <p>
          <?php echo Yii::t('Registration', '<b class="text-danger">Note</b>: Please introduce yourself about your ability in competitions. Don\'t forget to mark the type if you choose other.'); ?>
        </p>
        <?php echo Html::formGroup(
          $model, 'staff_statement', array(),
          $form->labelEx($model, 'staff_statement'),
          $form->textArea($model, 'staff_statement', array(
            'class'=>'form-control',
          )),
          $form->error($model, 'staff_statement', array('class'=>'text-danger'))
        ); ?>
      </div>
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
        <input id="regulations" class="registration-agreements" value="agree" type="checkbox" name="regulations" checked>
        <?php echo Yii::t('Competition', 'I have read and am familiar with this competition’s {regulations}.', [
          '{regulations}'=>CHtml::link(Yii::t('Competition', 'regulations'), $competition->getUrl('regulations')),
        ]); ?>
      </label>
    </div>
    <div class="checkbox">
      <label>
        <input id="disclaimer" class="registration-agreements" value="agree" type="checkbox" name="disclaimer" checked>
        <?php echo Yii::t('Competition', 'I have read and am familiar the {disclaimer} of Cubing China.', [
          '{disclaimer}'=>CHtml::link(Yii::t('Competition', 'disclaimer'), ['/site/page', 'view'=>'disclaimer']),
        ]); ?>
      </label>
    </div>
    <?php $disabled = $competition->fill_passport && $this->user->passport_type == User::NO || count($competition->getRegistrationEvents()) == count($unmetEvents); ?>
    <?php echo CHtml::tag('button', [
      'type'=>'submit',
      'class'=>'btn btn-theme' . ($disabled ? ' disabled' : ''),
      'id'=>'submit-button',
      'disabled'=>$disabled,
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
$regulations = Yii::app()->params->regulations;
$options = json_encode([
  'multiCountries'=>!!$competition->multi_countries,
  'complexMultiLocation'=>!!$competition->complex_multi_location,
  'showRegulations'=>!!$competition->show_regulations,
  'regulationDesc'=>Yii::t('Competition', 'Please deeply remember the followings to avoid any inconveniences.'),
  'basicFee' => $competition->getEventFee(Competition::EVENT_FEE_ENTRY),
  'wcaDuesFee' => 0,
  'entourageFee' => intval($competition->entourage_fee),
  'regulations'=>[
    'common'=>array_merge([
      Yii::t('Competition', 'Click {here} to read the regulations for this competition. Please read the regulations before registering, and contact the organizers if you have any questions.', [
        '{here}'=>CHtml::link(Yii::t('common', 'here'), $competition->getUrl('regulations'), ['target'=>'_blank']),
      ]),
    ], ActiveRecord::getModelAttributeValue($regulations, 'common')),
    'special'=>ActiveRecord::getModelAttributeValue($regulations, 'special'),
  ],
  'unmetEvents'=>$unmetEvents,
  'qualifyingEnd'=>date('Y-m-d H:i:s', $competition->qualifying_end_time),
  'unmetEventsMessage'=>Yii::t('Competition', 'You must meet the qualifying times of following events before <b>{date}</b> or they will be removed.', [
    '{date}'=>date('Y-m-d H:i:s', $competition->qualifying_end_time),
  ]),
  'delimiter'=>Yii::t('common', ', '),
]);
echo <<<EOT
<script>
  window.registrationOptions = {$options};
</script>
EOT
;
Yii::app()->clientScript->registerScriptFile('/f/js/registration' . (DEV ? '' : '.min') . '.js?ver=20231002');
endif;
