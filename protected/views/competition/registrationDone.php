<?php $this->renderPartial('operation', $_data_); ?>
<?php if ($canRegister): ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php if ($registration->isPending()): ?>
  <div class="alert alert-success">
    <?php echo Yii::t('Competition', 'Your registration was submitted successfully.'); ?>
    <?php if ($registration->payable): ?>
    <?php echo Yii::t('Competition', 'It will be verified automatically after your {paying}.', array(
      '{paying}'=>CHtml::tag('b', array('class'=>'text-danger'), Yii::t('common', 'paying the fee online')),
    )); ?>
    <?php else: ?>
    <?php echo Yii::t('Competition', 'It will be verified by the organisation team soon. Please wait with patience.'); ?>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php if ($registration->isAccepted() && $registration->payable): ?>
  <div class="alert alert-warning">
    <?php echo Yii::t('Registration', 'Your registration has some events in pending status. Please pay for them.'); ?>
  </div>
  <?php endif; ?>
  <?php if ($registration->isWaiting()): ?>
  <div class="alert alert-warning">
    <?php echo Yii::t('Registration', 'Your registration is on the waiting list.'); ?>
  </div>
  <?php endif; ?>
  <div class="row">
    <div class="col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
      <div class="panel panel-primary">
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
          <?php if ($registration->isAcceptedOrWaiting()): ?>
          <h4><?php echo Yii::t('Registration', 'Events'); ?></h4>
          <p><?php echo $registration->isAccepted() ? $registration->getAcceptedEventsString() : $registration->getWaitingEventsString(); ?></p>
          <h4><?php echo Yii::t('common', 'Total Fee'); ?></h4>
          <p><?php echo $registration->getFeeInfo(); ?></p>
          <?php endif; ?>
          <?php if ($registration->isCancelled() && $registration->getRefundAmount() > 0): ?>
          <h4><?php echo Yii::t('Registration', 'Returned Fee to Registrant') ;?></h4>
          <p><i class="fa fa-rmb"></i><?php echo $registration->getRefundFee(); ?></p>
          <p class="text-info"><?php echo Yii::t('Registration', 'The refund will be made via the payment method which you have used at the registration.'); ?></p>
          <?php endif; ?>
          <?php if ($registration->location->payment_method): ?>
          <h4><?php echo Yii::t('Competition', 'Payment Method') ;?></h4>
          <p><?php echo $registration->location->payment_method; ?></p>
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
          <?php if ($registration->getDisqualifiedEvents() !== []): ?>
          <h4><?php echo Yii::t('Registration', 'Disqualified Events'); ?> <small><?php echo CHtml::link(Yii::t('Competition', 'Regulations'), $competition->getUrl('regulations')); ?></small></h4>
          <p><?php echo $registration->getDisqualifiedEventsString(); ?></p>
          <?php endif; ?>
          <?php if ($registration->isWaiting()): ?>
          <h4><?php echo Yii::t('common', 'Waiting'); ?></h4>
          <p><?php echo Yii::t('Registration', 'Your registration is on the waiting list.'), Yii::t('Registration', 'If there are sufficient openings from other competitors canceling their registration, your registration will be automatically accepted. If at the end of the registration period your registration has not been accepted, your registration fee will be returned in full.'); ?></p>
          <?php endif; ?>
          <?php if ($competition->fill_passport && $user->passport_type != User::NO && !$registration->isDisqualified()): ?>
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
          <?php $this->renderPartial('payRegistration', $_data_); ?>
        </div>
      </div>
    </div>
  </div>
  <?php $this->renderPartial('editRegistration', $_data_); ?>
  <?php $this->renderPartial('resetPayment', $_data_); ?>
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
          <p><i class="fa fa-rmb"></i><?php echo $registration->getRefundFee(); ?></p>
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
endif;
