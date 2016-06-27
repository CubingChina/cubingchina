<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <div class="alert alert-success">
    <?php echo Yii::t('Competition', 'Your registration was submitted successfully.'); ?>
    <?php if ($accepted): ?>
    <?php echo Yii::t('Competition', 'Click {here} to view the competitors list.', array(
      '{here}'=>CHtml::link(Yii::t('common', 'here'), $competition->getUrl('competitors')),
    )); ?>
    <?php elseif ($registration->payable): ?>
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
  <?php if ($accepted && $competition->show_qrcode): ?>
  <div class="col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
    <div class="panel panel-info">
      <div class="panel-body">
        <p><?php echo Yii::t('Registration', 'You succeeded in registering for '), $competition->getAttributeValue('name'), Yii::t('common', '.'); ?></p>
        <p><?php echo Yii::t('Registration', 'The QR code below is for check-in and relevant matters. You can find it in your registration page at all time. Please show <b class="text-danger">the QR code and the corresponding ID credentials</b> to our staffs for check-in.'); ?></p>
        <p class="text-center">
          <?php echo CHtml::image($registration->qrCodeUrl); ?>
          <br>
          <?php echo CHtml::link(Yii::t('common', 'Download'), $registration->qrCodeUrl, array(
            'class'=>'btn btn-theme btn-large',
            'target'=>'_blank',
          )); ?>
        </p>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php if ($registration->payable): ?>
  <div class="col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
    <h4>
      <?php echo Yii::t('common', 'Total Fee:'); ?>&nbsp; <b class="text-danger"><i class="fa fa-rmb"></i><?php echo $registration->getTotalFee(); ?></b>
    </h4>
    <h4><?php echo Yii::t('common', 'Please choose a payment channel.'); ?></h4>
    <div class="pay-channels clearfix">
      <?php foreach (Yii::app()->params->payments as $channel=>$payment): ?>
      <?php if (!isset($payment['active']) || $payment['active'] == true): ?>
      <div class="pay-channel pay-channel-<?php echo $channel; ?>" data-channel="<?php echo $channel; ?>">
        <img src="<?php echo $payment['img']; ?>">
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
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
  $('.pay-channel').first().addClass('active');
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