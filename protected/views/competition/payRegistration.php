<?php if ($registration->payable): ?>
<?php $pay = $registration->getUnpaidPayment(); ?>
<hr>
<h4><?php echo Yii::t('Registration', 'Pending Events'); ?></h4>
<p><?php echo $registration->getPendingEventsString(); ?></p>
<h4><?php echo Yii::t('common', 'Fee'); ?></h4>
<p><?php echo $registration->getPendingFee(); ?></p>
<?php if (count(Yii::app()->params->payments) > 1): ?>
<h4><?php echo Yii::t('common', 'Please choose a payment channel.'); ?></h4>
<?php endif; ?>
<?php $this->renderPartial('paymentParams', [
  'payment'=>$registration->getUnpaidPayment(),
  'competition'=>$competition,
]); ?>
<?php if ($this->user->country_id > 1 && !$competition->paypal_link && $competition->auto_accept == Competition::YES): ?>
<hr>
<p>
  <?php echo Yii::t('common', 'International competitors that are not able to pay online, please enter the verification code. Your registration will be accepted, please pay cash at the venue.'); ?>
</p>
<?php $form = $this->beginWidget('ActiveForm', [
  'id'=>'registration-form',
  'htmlOptions'=>[
    'role'=>'form',
  ],
]); ?>
<?php echo Html::formGroup(
  $overseaUserVerifyForm, 'verifyCode', [],
  $form->labelEx($overseaUserVerifyForm, 'verifyCode'),
  Html::activeTextField($overseaUserVerifyForm, 'verifyCode', []),
  $this->widget('CCaptcha', [
    'captchaAction'=>'site/captcha',
    'clickableImage'=>true,
    'showRefreshButton'=>false,
  ], true),
  $form->error($overseaUserVerifyForm, 'verifyCode', ['class'=>'text-danger'])
);?>
<button type="submit" class="btn btn-theme btn-lg"><?php echo Yii::t('common', 'Submit'); ?></button>
<?php $this->endWidget(); ?>
<?php endif; ?>
<?php endif; ?>
