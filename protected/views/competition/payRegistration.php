<?php if ($registration->payable): ?>
<hr>
<h4><?php echo Yii::t('Registration', 'Pending Events'); ?></h4>
<p><?php echo $registration->getPendingEvents(); ?></p>
<h4><?php echo Yii::t('common', 'Fee'); ?></h4>
<p><?php echo $registration->getPendingFee(); ?></p>
<?php if (count(Yii::app()->params->payments) > 1): ?>
<h4><?php echo Yii::t('common', 'Please choose a payment channel.'); ?></h4>
<?php endif; ?>
<div class="pay-channels clearfix">
  <?php foreach (Yii::app()->params->payments as $channel=>$payment): ?>
  <div class="pay-channel pay-channel-<?php echo $channel; ?>" data-channel="<?php echo $channel; ?>">
    <img src="<?php echo $payment['img']; ?>">
  </div>
  <?php endforeach; ?>
  <?php if ($this->user->country_id > 1 && $competition->paypal_link): ?>
  <div class="pay-channel pay-channel-<?php echo $channel; ?>">
    <a href="<?php echo $competition->getPaypalLink($registration); ?>" target="_blank">
      <img src="/f/images/pay/paypal.png">
    </a>
    <p class="text-danger"><?php echo Yii::t('Registration', 'Payment via Paypal is not accepted automatically. Please wait patiently if you\'ve already paid. We will accept your registration soon.'); ?></p>
  </div>
  <?php endif; ?>
</div>
<p class="hide lead text-danger" id="redirect-tips">
  <?php echo Yii::t('common', 'Alipay has been blocked by wechat.'); ?><br>
  <?php echo Yii::t('common', 'Please open with browser!'); ?>
</p>
<p class="text-danger"><?php echo Yii::t('common', 'If you were unable to pay online, please contact the organizer.'); ?></p>
<div class="text-center">
  <button id="pay" class="btn btn-lg btn-primary"><?php echo Yii::t('common', 'Pay'); ?></button>
</div>
<div class="hide text-center" id="pay-tips">
  <?php echo CHtml::image('https://i.cubingchina.com/animatedcube.gif'); ?>
  <br>
  <?php echo Yii::t('common', 'You are being redirected to the payment, please wait patiently.'); ?>
</div>
<?php if ($this->user->country_id > 1 && !$competition->paypal_link): ?>
<hr>
<p>
  <?php echo Yii::t('common', 'International competitors that are not able to pay online with alipay, please enter the verification code. Your registration will be accepted, please pay cash at the venue.'); ?>
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

<?php
if ($registration->payable) {
  $paymentId = $registration->getUnpaidPayment()->id;
  Yii::app()->clientScript->registerScript('pay',
<<<EOT
  if (navigator.userAgent.match(/MicroMessenger/i)) {
    $('#redirect-tips').removeClass('hide').nextAll().hide();
    $('#pay').prop('disabled', true);
  }
  $('.pay-channel').first().addClass('active');
  var channel = $('.pay-channel.active').data('channel');
  $('.pay-channel').on('click', function() {
    channel = $(this).data('channel');
    if (channel) {
      $(this).addClass('active').siblings().removeClass('active');
    }
  });
  $('#pay').on('click', function() {
    $('#pay-tips').removeClass('hide');
    $(this).prop('disabled', true);
    $('.pay-channel').off('click');
    $.ajax({
      url: '/pay/params',
      data: {
        id: {$paymentId},
        is_mobile: Number('ontouchstart' in window),
        channel: channel
      },
      dataType: 'json',
      success: function(data) {
        if (data.data.url) {
          location.href = data.data.url;
        } else {
          submitForm(data.data);
        }
      }
    });
  });
  function submitForm(data) {
    var form = $('<form>').attr({
      action: data.action,
      method: data.method || 'get'
    });
    for (var k in data.params) {
      $('<input type="hidden">').attr('name', k).val(data.params[k]).appendTo(form);
    }
    form.appendTo(document.body);
    form.submit();
  }
EOT
  );
}
