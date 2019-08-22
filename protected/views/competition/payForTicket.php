<?php if ($userTicket->isPayable()): ?>
<div class="panel panel-warning">
  <div class="panel-heading"><?php echo Yii::t('Competition', 'Pay for Ticket'); ?></div>
  <div class="panel-body">
    <?php $_data_['showButton'] = false; ?>
    <?php $this->renderPartial('ticketInfo', $_data_); ?>
    <?php if (count(Yii::app()->params->payments) > 1): ?>
    <h4><?php echo Yii::t('common', 'Please choose a payment channel.'); ?></h4>
    <?php endif; ?>
    <?php $this->renderPartial('paymentParams', [
      'payment'=>$userTicket->createPayment(),
    ]); ?>
  </div>
</div>
<?php endif; ?>
