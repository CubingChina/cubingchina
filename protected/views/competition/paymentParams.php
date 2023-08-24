<div class="pay-channels clearfix">
  <?php foreach (Yii::app()->params->payments as $channel=>$params): ?>
  <div class="pay-channel pay-channel-<?php echo $channel; ?>" data-channel="<?php echo $channel; ?>">
    <img src="<?php echo $params['img']; ?>">
  </div>
  <?php endforeach; ?>
  <?php if ($this->user->country_id > 1 && isset($competition) && $competition->paypal_link): ?>
  <div class="pay-channel pay-channel-paypal">
    <a href="<?php echo $competition->getPaypalLink($registration); ?>" target="_blank">
      <img src="/f/images/pay/paypal.png">
    </a>
    <p class="text-danger"><?php echo Yii::t('Registration', 'Payment via Paypal is not accepted automatically. Please wait patiently if you\'ve already paid. We will accept your registration soon.'); ?></p>
  </div>
  <?php endif; ?>
</div>
<p class="text-danger"><?php echo Yii::t('common', 'If you were unable to pay online, please contact the organizer.'); ?></p>
<div class="text-center">
  <button id="pay" class="btn btn-lg btn-primary"><?php echo Yii::t('common', 'Pay'); ?></button>
</div>
<div class="hide text-center" id="pay-tips">
  <?php echo CHtml::image('https://i.cubing.com/animatedcube.gif'); ?>
  <br>
  <?php echo Yii::t('common', 'You are being redirected to the payment, please wait patiently.'); ?>
</div>
<?php echo CHtml::hiddenField('paymentId', $payment->id, ['id'=>'payment-id']); ?>
<?php echo CHtml::hiddenField('channel', $payment->channel, ['id'=>'payment-channel']); ?>
<?php echo CHtml::hiddenField('tradeType', $payment->getWechatTradeType(), ['id'=>'trade-type']); ?>
<script>
window.wxScanDialogOptions = {
  type: 'type-success',
  title: '<?php echo Yii::t('Pay', 'Please scan the QR code to pay with Wechat'); ?>',
  btnOKLabel: '<?php echo Yii::t('Pay', 'Successfully paid'); ?>',
  btnCancelLabel: '<?php echo Yii::t('Pay', 'Not yet paid'); ?>',
}
window.operationsDialogOptions = {
  type: 'type-success',
  title: '<?php echo Yii::t('Pay', 'Have you successfully paid?'); ?>',
  btnOKLabel: '<?php echo Yii::t('Pay', 'Successfully paid'); ?>',
  btnCancelLabel: '<?php echo Yii::t('Pay', 'Not yet paid'); ?>',
}
</script>
