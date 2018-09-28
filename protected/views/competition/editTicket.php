<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <div class="row">
    <div class="col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
      <div class="panel panel-primary">
        <div class="panel-heading"><?php echo Yii::t('Competition', 'Edit Ticket'); ?></div>
        <div class="panel-body">
          <?php $form = $this->beginWidget('ActiveForm', [
            'id'=>'buy-ticket-form',
            'htmlOptions'=>[
            ],
          ]); ?>
          <h3><?php echo Yii::t('Competition', 'Who\'s the ticket for') ;?></h3>
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
        </div>
      </div>
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
