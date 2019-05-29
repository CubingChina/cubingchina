<div class="ticket<?php if ($userTicket->signed_in || $userTicket->isUnpaid() && !$userTicket->isPayable()) echo ' used'; ?>">
  <div class="ticket-info">
    <h4><?php echo $userTicket->ticket->getAttributeValue('name'); ?></h4>
    <p><?php echo $userTicket->ticket->getAttributeValue('description'); ?></p>
    <dl>
      <dt><?php echo $userTicket->getAttributeLabel('fee'); ?></dt>
      <dd><?php echo $userTicket->fee; ?></dd>
      <dt><?php echo $userTicket->getAttributeLabel('name'); ?></dt>
      <dd><?php echo $userTicket->name; ?></dd>
      <dt><?php echo $userTicket->getAttributeLabel('passport_type'); ?></dt>
      <dd><?php echo $userTicket->getPassportTypeText(); ?></dd>
      <dt><?php echo $userTicket->getAttributeLabel('passport_number'); ?></dt>
      <dd><?php echo $userTicket->passport_number; ?></dd>
    </dl>
    <?php if (!isset($showButton)): ?>
    <p>
      <?php if ($userTicket->isPayable()): ?>
      <?php echo CHtml::link(Yii::t('common', 'Pay'), $competition->getUrl('ticket', [
        'id'=>$userTicket->id,
      ]), [
        'class'=>'btn btn-sm btn-success'
      ]); ?>
      <?php elseif ($userTicket->isEditable()): ?>
      <?php echo CHtml::link(Yii::t('common', 'Edit'), $competition->getUrl('ticket', [
        'id'=>$userTicket->id,
      ]), [
        'class'=>'btn btn-sm btn-primary'
      ]); ?>
      <?php endif; ?>
    </p>
    <?php endif; ?>
  </div>
  <?php if ($userTicket->isPaid()): ?>
  <div class="ticket-qrcode">
    <?php echo CHtml::image($userTicket->getQRCodeUrl()); ?>
  </div>
  <?php endif; ?>
</div>
