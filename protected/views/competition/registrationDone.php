<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php if ($registration->isPending() || $registration->isAccepted()): ?>
  <div class="alert alert-success">
    <?php echo Yii::t('Competition', 'Your registration was submitted successfully.'); ?>
    <?php if ($registration->isAccepted()): ?>
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
  </div>
  <?php endif; ?>
  <div class="row">
    <div class="col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
      <div class="panel panel-info">
        <div class="panel-heading"><?php echo Yii::t('Registration', 'Registration Detail'); ?></div>
        <div class="panel-body">
          <?php if ($registration->isAccepted()): ?>
          <p><?php echo Yii::t('Registration', 'You succeeded in registering for '), $competition->getAttributeValue('name'), Yii::t('common', '.'); ?></p>
          <hr>
          <?php elseif ($registration->isCancelled()): ?>
          <p><?php echo Yii::t('Registration', 'Your registration has been cancelled.'); ?></p>
          <hr>
          <?php elseif ($registration->isDisqualified()): ?>
          <p><?php echo Yii::t('Registration', 'Your registration has been disqualified.'); ?></p>
          <hr>
          <?php endif; ?>
          <h4><?php echo Yii::t('Registration', 'Name'); ?></h4>
          <p><?php echo $registration->user->getCompetitionName(); ?></p>
          <?php if ($registration->isAccepted()): ?>
          <h4><?php echo Yii::t('Registration', 'No.'); ?></h4>
          <p><?php echo $registration->getUserNumber(); ?></p>
          <?php endif; ?>
          <?php if ($competition->isMultiLocation()): ?>
          <h4><?php echo Yii::t('Competition', 'Location'); ?></h4>
          <p><?php echo $registration->location->getCityName(); ?></p>
          <?php endif; ?>
          <h4><?php echo Yii::t('Registration', 'Events'); ?></h4>
          <p><?php echo $registration->getRegistrationEvents(); ?></p>
          <h4><?php echo Yii::t('common', 'Total Fee'); ?></h4>
          <p><?php echo $registration->getFeeInfo(); ?></p>
          <?php if ($registration->isCancelled() && $registration->getRefundFee() > 0): ?>
          <h4><?php echo Yii::t('Registration', 'Returned Fee to Registrant') ;?></h4>
          <p><i class="fa fa-rmb"></i><?php echo number_format($registration->getRefundFee() / 100, 2, '.', ''); ?></p>
          <p class="text-info"><?php echo Yii::t('Registration', 'The refund will be made via the payment method which you have used at the registration.'); ?></p>
          <?php endif; ?>
          <h4><?php echo Yii::t('Registration', 'Registration Time'); ?></h4>
          <p><?php echo date('Y-m-d H:i:s', $registration->date); ?></p>
          <?php if ($registration->isAccepted()): ?>
          <h4><?php echo Yii::t('Registration', 'Acception Time'); ?></h4>
          <p><?php echo date('Y-m-d H:i:s', $registration->accept_time); ?></p>
          <?php endif; ?>
          <?php if ($registration->isCancelled()): ?>
          <h4><?php echo Yii::t('Registration', 'Cancellation Time'); ?></h4>
          <p><?php echo date('Y-m-d H:i:s', $registration->cancel_time); ?></p>
          <?php endif; ?>
          <hr>
          <?php if ($registration->payable): ?>
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
          <?php endif; ?>
          <?php if ($registration->isWaiting()): ?>
          <h4><?php echo Yii::t('common', 'Waiting'); ?></h4>
          <p><?php echo Yii::t('Registration', 'Your registration is on the waiting list. There are {count} people on the list ahead of you.', [
            '{count}'=>$registration->getWaitingNumber(),
          ]); ?></p>
          <?php endif; ?>
          <?php if ($competition->fill_passport && $user->passport_type != User::NO): ?>
          <hr>
          <p><?php echo Yii::t('Registration', 'All the information collected will ONLY be used for identity confirmation, insurance and government information backup of the competition. You may choose to delete it after competition or keep it in the database for the use of future competition.') ;?></p>
          <h4><?php echo Yii::t('Registration', 'Type of Identity'); ?></h4>
          <p><?php echo $registration->user->getPassportTypeText(); ?></p>
          <h4><?php echo Yii::t('Registration', 'Identity Number'); ?></h4>
          <p><?php echo $registration->user->passport_number; ?></p>
          <?php endif; ?>
          <?php if ($registration->isAccepted() && $competition->show_qrcode): ?>
          <p><?php echo Yii::t('Registration', 'The QR code below is for check-in and relevant matters. You can find it in your registration page at all time. Please show <b class="text-danger">the QR code and the corresponding ID credentials</b> to our staffs for check-in.'); ?></p>
          <p class="text-center">
            <?php echo CHtml::image($registration->qrCodeUrl); ?>
            <br>
            <?php echo CHtml::link(Yii::t('common', 'Download'), $registration->qrCodeUrl, array(
              'class'=>'btn btn-theme btn-large',
              'target'=>'_blank',
            )); ?>
          </p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php if ($registration->isCancellable()): ?>
  <div class="row">
    <div class="col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
      <div class="panel panel-warning">
        <div class="panel-heading">
          <a data-toggle="collapse" href="#cancellation"><?php echo Yii::t('Registration', 'Registration Cancellation'); ?></a>
        </div>
        <div class="panel-body collapse" id="cancellation">
          <h4 class="text-danger"><?php echo Yii::t('Registration', '<b>Warning:</b> Once you cancel your registration, you will <b>NOT</b> be a competitor and you cannot register for this competition any longer.'); ?></h4>
          <?php $form = $this->beginWidget('ActiveForm', array(
            'id'=>'cancel-form',
            'htmlOptions'=>array(
            ),
          )); ?>
          <p><?php echo Yii::t('Registration', 'You can cancel your registration before {time}.', [
            '{time}'=>date('Y-m-d H:i:s', $competition->cancellation_end_time),
          ]); ?></p>
          <?php echo Html::countdown($competition->cancellation_end_time, [
            'data-total-days'=>$competition->reg_start > 0 ? floor(($competition->cancellation_end_time - $competition->reg_start) / 86400) : 30,
          ]); ?>
          <input type="hidden" name="cancel" value="1">
          <?php if ($registration->getPaidFee() > 0): ?>
          <h4><?php echo Yii::t('Registration', 'Paid Fee via Cubing China') ;?></h4>
          <p><i class="fa fa-rmb"></i><?php echo $registration->getPaidFee(); ?></p>
          <h4><?php echo Yii::t('Registration', 'Returned Fee to Registrant') ;?></h4>
          <p><i class="fa fa-rmb"></i><?php echo number_format($registration->getRefundFee() / 100, 2, '.', ''); ?></p>
          <p class="text-info"><?php echo Yii::t('Registration', 'The refund will be made via the payment method which you have used at the registration.'); ?></p>
          <?php endif; ?>
          <?php echo CHtml::tag('button', [
            'id'=>'cancel',
            'type'=>'button',
            'class'=>'btn btn-danger',
          ], Yii::t('common', 'Submit')); ?>
          <?php $this->endWidget(); ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php
$cancellationMessage = json_encode(Yii::t('Registration', 'Please double-confirm your cancellation.'));
if ($registration->isCancellable()) {
  Yii::app()->clientScript->registerScript('cancel',
<<<EOT
  var cancellationMessage = {$cancellationMessage};
  $('#cancel').on('click', function() {
    var that = $(this);
    CubingChina.utils.confirm(cancellationMessage, {
      type: 'type-warning'
    }).then(function() {
      $('#cancel-form').submit();
    })
  });
EOT
  );
}

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
