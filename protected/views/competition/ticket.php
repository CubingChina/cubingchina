<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <div class="row">
    <div class="col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
      <?php if ($user->hasPaidTickets($competition)): ?>
      <div class="panel panel-info">
        <div class="panel-heading">
          <a data-toggle="collapse" href="#my-tickets"><?php echo Yii::t('Competition', 'My Tickets'); ?></a>
        </div>
        <div class="panel-body collapse in" id="my-tickets">
          <div class="my-ticket-list">
            <?php foreach ($user->getTickets($competition, UserTicket::STATUS_PAID) as $userTicket): ?>
            <div class="ticket">
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
              <div class="ticket-qrcode">
                <?php echo CHtml::image($userTicket->getQRCodeUrl()); ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
      <div class="panel panel-primary">
        <div class="panel-heading"><?php echo Yii::t('Competition', 'Buy Tickets'); ?></div>
        <div class="panel-body">
          <?php if ($user->hasUnpaidTickets($competition)): ?>
          <?php $_data_['userTicket'] = $user->getUnpaidTicket($competition); ?>
          <?php $this->renderPartial('payForTicket', $_data_); ?>
          <?php else: ?>
          <?php $form = $this->beginWidget('ActiveForm', [
            'id'=>'buy-ticket-form',
            'htmlOptions'=>[
            ],
          ]); ?>
          <h3><?php echo Yii::t('Competition', 'Choose a ticket'); ?> <span class="required">*</span></h3>
          <div class="ticket-list">
            <?php foreach ($tickets as $ticket): ?>
            <div class="ticket" data-fee="<?php echo $ticket->fee; ?>">
              <?php echo $form->radioButton($model, 'ticket_id', [
                'id'=>'ticket-' . $ticket->id,
                'value'=>$ticket->id,
                'uncheckValue'=>null,
              ]); ?>
              <label for="ticket-<?php echo $ticket->id; ?>">
                <h4><?php echo $ticket->getAttributeValue('name'); ?></h4>
                <p><?php echo $ticket->getAttributeValue('description'); ?></p>
                <?php if ($model->discount > 0): ?>
                <p>
                  <s><?php echo Html::fontAwesome('rmb'), $ticket->fee; ?></s>
                  <?php echo Html::fontAwesome('rmb'), $ticket->fee * $model->discount / 100; ?>
                </p>
                <?php else: ?>
                <p><?php echo Html::fontAwesome('rmb'), $ticket->fee; ?></p>
                <?php endif; ?>
              </label>
            </div>
            <?php endforeach; ?>
            <?php echo $form->error($model, 'ticket_id', ['class'=>'text-danger']); ?>
          </div>
          <h3><?php echo Yii::t('Competition', 'Fill who\'s the ticket for') ;?></h3>
          <?php echo Html::formGroup(
            $model, 'name', [
            ],
            $form->labelEx($model, 'name'),
            Html::activeTextField($model, 'name', [
              'class'=>'form-control',
            ]),
            $form->error($model, 'name', ['class'=>'text-danger'])
          ); ?>
          <?php echo Html::formGroup(
            $model, 'passport_type', [],
            $form->labelEx($model, 'passport_type'),
            $form->dropDownList($model, 'passport_type', User::getPassportTypes(), [
              'prompt'=>'',
              'class'=>'form-control',
            ]),
            $form->error($model, 'passport_type', ['class'=>'text-danger'])
          ); ?>
          <?php echo Html::formGroup(
            $model, 'passport_name', [
              'class'=>'hide',
            ],
            $form->labelEx($model, 'passport_name'),
            Html::activeTextField($model, 'passport_name', [
              'class'=>'form-control',
            ]),
            $form->error($model, 'passport_name', ['class'=>'text-danger'])
          ); ?>
          <?php echo Html::formGroup(
            $model, 'passport_number', [],
            $form->labelEx($model, 'passport_number'),
            Html::activeTextField($model, 'passport_number', [
              'class'=>'form-control',
            ]),
            $form->error($model, 'passport_number', ['class'=>'text-danger'])
          ); ?>
          <?php echo Html::formGroup(
            $model, 'repeatPassportNumber', [],
            $form->labelEx($model, 'repeatPassportNumber'),
            Html::activeTextField($model, 'repeatPassportNumber', [
              'class'=>'form-control',
            ]),
            $form->error($model, 'repeatPassportNumber', ['class'=>'text-danger'])
          ); ?>
          <?php echo CHtml::tag('button', [
            'type'=>'submit',
            'class'=>'btn btn-primary',
            'id'=>'submit-button',
          ], Yii::t('common', 'Submit')); ?>
          <?php $this->endWidget(); ?>
          <?php endif; ?>
        </div>
      </div>
      <p class="help-text text-danger"><?php echo Yii::t('Ticket', 'All the information collected will ONLY be used for identity confirmation, insurance and government information backup of the competition.') ;?></p>
    </div>
  </div>
</div>
<?php
Yii::app()->clientScript->registerScript('edit',
<<<EOT
  $(document).on('change', '#UserTicket_passport_type', function() {
    changePassportType(true);
  });
  changePassportType();
  function changePassportType(focus) {
    var type = $('#UserTicket_passport_type').val();
    if (type == 3) {
      $('#UserTicket_passport_name').parent().removeClass('hide');
      if (focus) {
        $('#UserTicket_passport_name').focus();
      }
    } else {
      $('#UserTicket_passport_name').parent().addClass('hide');
    }
  }
EOT
);
