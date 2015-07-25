<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <div class="alert alert-success">
    <?php echo Yii::t('Competition', 'Your registration was submitted successfully.'); ?>
    <?php if ($accepted): ?>
    <?php echo Yii::t('Competition', 'Click {here} to view the competitors list.', array(
      '{here}'=>CHtml::link(Yii::t('common', 'here'), $competition->getUrl('competitors')),
    )); ?>
    <?php elseif ($competition->isOnlinePay() && $registration->getTotalFee() > 0): ?>
    <?php echo Yii::t('Competition', 'It will be verified automatically after your {paying}.', array(
      '{paying}'=>CHtml::tag('b', array('class'=>'text-danger'), Yii::t('common', 'paying the fee online')),
    )); ?>
    <?php else: ?>
    <?php echo Yii::t('Competition', 'It will be verified by the organisation team soon. Please wait with patience.'); ?>
    <?php endif; ?>
    <?php if (Yii::app()->language === 'zh_cn'): ?>
    <?php echo '点击上面的分享按钮告诉你的小伙伴吧～'; ?>
    <?php endif; ?>
  </div>
  <?php if ($registration->payable): ?>
  <div class="col-lg-8 col-lg-push-2">
    <h4><?php echo Yii::t('common', 'Please choose a payment channel.'); ?></h4>
    <div class="pay-channels clearfix">
      <div class="pay-channel alipay col-md-6 active" data-channel="alipay">
        <img src="/f/images/pay/alipay.png">
      </div>
      <div class="pay-channel nowpay col-md-6" data-channel="nowPay">
        <img src="/f/images/pay/nowpay.png">
        <p>
          <?php echo Yii::t('common', 'It supports Unionpay and many China bankcards.'); ?>
        </p>
      </div>
    </div>
    <p class="text-danger"><?php echo Yii::t('common', 'If you were unable to pay online, please contact the organizer.'); ?></p>
    <div class="text-center">
      <button id="pay" class="btn btn-lg btn-primary"><?php echo Yii::t('common', 'Pay'); ?></button>
    </div>
    <div class="hide text-center" id="pay-tips">
      <?php echo CHtml::image('http://s.cubingchina.com/animatedcube.gif'); ?>
      <br>
      <?php echo Yii::t('common', 'You are being redirected to the payment, please wait patiently.'); ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php
if ($registration->payable) {
  Yii::app()->clientScript->registerScript('pay',
<<<EOT
  var channel = $('.pay-channel.active').data('channel');
  $('.pay-channel').on('click', function() {
    channel = $(this).data('channel');
    $(this).addClass('active').siblings().removeClass('active');
  });
  $('#pay').on('click', function() {
    $('#pay-tips').removeClass('hide');
    $(this).prop('disabled', true);
    $('.pay-channel').off('click');
    $.ajax({
      url: '/pay/params',
      data: {
        id: '{$registration->pay->id}',
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