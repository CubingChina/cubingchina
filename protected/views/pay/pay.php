<div class="col-lg-12 text-center">
  <h4><?php echo Yii::t('common', 'Please choose a payment channel.'); ?></h4>
  <div class="pay-channel">
    <label>
      <input type="radio" name="channel" value="alipay" checked>
      <img src="/f/images/pay/alipay.png">
    </label>
    <label>
      <input type="radio" name="channel" value="nowPay">
      <img src="/f/images/pay/unionpay.jpg">
    </label>
  </div>
  <button id="pay" class="btn btn-primary"><?php echo Yii::t('common', 'Pay'); ?></button>
  <div class="hide" id="pay-tips">
    <?php echo CHtml::image('http://s.cubingchina.com/animatedcube.gif'); ?>
    <br>
    <?php echo Yii::t('common', 'You are being redirected to the payment, please wait patiently.'); ?>
  </div>
</div>
<?php
Yii::app()->clientScript->registerScript('pay',
<<<EOT
  $('#pay').on('click', function() {
    $('#pay-tips').removeClass('hide');
    $(this).prop('disabled', true);
    var channel = $('input[name="channel"]:checked').val();
    $.ajax({
      url: '/pay/params',
      data: {
        id: '{$model->id}',
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