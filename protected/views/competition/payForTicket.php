<?php if ($userTicket->isUnpaid()): ?>
<?php $this->renderPartial('ticketInfo', [
  'userTicket'=>$userTicket,
]); ?>
<?php if (count(Yii::app()->params->payments) > 1): ?>
<h4><?php echo Yii::t('common', 'Please choose a payment channel.'); ?></h4>
<?php endif; ?>
<div class="pay-channels clearfix">
  <?php foreach (Yii::app()->params->payments as $channel=>$payment): ?>
  <div class="pay-channel pay-channel-<?php echo $channel; ?>" data-channel="<?php echo $channel; ?>">
    <img src="<?php echo $payment['img']; ?>">
  </div>
  <?php endforeach; ?>
</div>
<p class="hide lead text-danger" id="redirect-tips">
  <?php echo Yii::t('common', 'Alipay has been blocked by wechat.'); ?><br>
  <?php echo Yii::t('common', 'Please open with browser!'); ?>
</p>
<div class="text-center">
  <button id="pay" class="btn btn-lg btn-primary"><?php echo Yii::t('common', 'Pay'); ?></button>
</div>
<div class="hide text-center" id="pay-tips">
  <?php echo CHtml::image('https://i.cubingchina.com/animatedcube.gif'); ?>
  <br>
  <?php echo Yii::t('common', 'You are being redirected to the payment, please wait patiently.'); ?>
</div>
<?php endif; ?>
<?php
if ($userTicket->isUnpaid()) {
  $paymentId = $userTicket->createPayment()->id;
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
