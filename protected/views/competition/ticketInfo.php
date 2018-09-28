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
</div>