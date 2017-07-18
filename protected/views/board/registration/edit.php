<div class="row">
  <div class="col-lg-12">
    <div class="page-title">
      <h1><?php echo $model->isNewRecord ? '新增' : '编辑'; ?>报名信息</h1>
    </div>
  </div>
  <!-- /.col-lg-12 -->
</div>
<div class="row">
  <div class="col-lg-12">
    <div class="portlet portlet-default">
      <div class="portlet-heading">
        <div class="portlet-title">
          <h4>报名信息</h4>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="panel-collapse collapse in">
        <div class="portlet-body">
          <?php $form = $this->beginWidget('ActiveForm', array(
            'htmlOptions'=>array(
            ),
          )); ?>
           <?php echo Html::formGroup(
              $model, 'user', array(),
              $form->labelEx($model, 'user_id'),
              CHtml::textField('', $model->user->getCompetitionName(), array(
                'class'=>'form-control',
                'disabled'=>true,
              ))
            );?>
           <?php echo Html::formGroup(
              $model, 'events', array(),
              $form->labelEx($model, 'events'),
              $this->widget('EventsForm', array(
                'model'=>$model,
                'competition'=>$model->competition,
                'name'=>'events',
                'events'=>$model->competition->getRegistrationEvents(),
                'type'=>'checkbox',
              ), true),
              $form->error($model, 'events', array('class'=>'text-danger'))
            );?>
            <?php echo Html::formGroup(
              $model, 'total_fee', array(),
              $form->labelEx($model, 'total_fee'),
              Html::activeTextField($model, 'total_fee', array(
              )),
              Html::tag('div', array('class'=>'text-danger'), '此数值会影响已经通过审核的选手的报名费显示，不影响未通过的支付金额'),
              $form->error($model, 'total_fee', array('class'=>'text-danger'))
            ); ?>
            <?php echo Html::formGroup(
              $model, 'comments', array(),
              $form->labelEx($model, 'comments'),
              $form->textArea($model, 'comments', array(
                'class'=>'form-control',
                'rows'=>4,
              )),
              $form->error($model, 'comments', array('class'=>'text-danger'))
            ); ?>
            <?php if ($model->competition->require_avatar): ?>
            <?php echo Html::formGroup(
              $model, 'avatar_type', array(),
              $form->labelEx($model, 'avatar_type', array(
                'label'=>Yii::t('Registration', 'Please choose from the options listed below.'),
              )),
              $form->dropDownList($model, 'avatar_type', Registration::getAvatarTypes($model->competition), array(
                'prompt'=>'',
                'class'=>'form-control',
                'options'=>array(
                  Registration::AVATAR_TYPE_NOW=>array(
                    'disabled'=>$model->user->avatar == null,
                  ),
                ),
              )),
              $form->error($model, 'avatar_type', array('class'=>'text-danger'))
            ); ?>
            <?php endif; ?>
            <?php if ($model->competition->entourage_limit): ?>
            <?php echo Html::formGroup(
              $model, 'has_entourage', array(),
              $form->labelEx($model, 'has_entourage', array(
              )),
              $form->dropDownList($model, 'has_entourage', Registration::getYesOrNo(), array(
                'prompt'=>'',
                'class'=>'form-control',
              )),
              $form->error($model, 'has_entourage', array('class'=>'text-danger'))
            ); ?>
            <div class="entourage-info hide">
              <?php echo Html::formGroup(
                $model, 'entourage_name', array(
                ),
                $form->labelEx($model, 'entourage_name'),
                Html::activeTextField($model, 'entourage_name', array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'entourage_name', array('class'=>'text-danger'))
              ); ?>
              <?php echo Html::formGroup(
                $model, 'entourage_passport_type', array(),
                $form->labelEx($model, 'entourage_passport_type'),
                $form->dropDownList($model, 'entourage_passport_type', User::getPassportTypes(), array(
                  'prompt'=>'',
                  'class'=>'form-control',
                )),
                $form->error($model, 'entourage_passport_type', array('class'=>'text-danger'))
              ); ?>
              <?php echo Html::formGroup(
                $model, 'entourage_passport_name', array(
                  'class'=>'hide',
                ),
                $form->labelEx($model, 'entourage_passport_name'),
                Html::activeTextField($model, 'entourage_passport_name', array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'entourage_passport_name', array('class'=>'text-danger'))
              ); ?>
              <?php echo Html::formGroup(
                $model, 'entourage_passport_number', array(),
                $form->labelEx($model, 'entourage_passport_number'),
                Html::activeTextField($model, 'entourage_passport_number', array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'entourage_passport_number', array('class'=>'text-danger'))
              ); ?>
              <?php echo Html::formGroup(
                $model, 'repeatPassportNumber', array(),
                $form->labelEx($model, 'repeatPassportNumber'),
                Html::activeTextField($model, 'repeatPassportNumber', array(
                  'class'=>'form-control',
                )),
                $form->error($model, 'repeatPassportNumber', array('class'=>'text-danger'))
              ); ?>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
          <?php $this->endWidget(); ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
Yii::app()->clientScript->registerScript('registration',
<<<EOT
  var totalFee = $('#Registration_total_fee');
  $(document).on('change', '.registration-events', function() {
    var fee = $(this).data('fee');
    if (this.checked) {
      totalFee.val(+totalFee.val() + fee);
    } else {
      totalFee.val(+totalFee.val() - fee);
    }
  }).on('change', '#Registration_has_entourage', function() {
    $('.entourage-info')[this.value == 1 ? 'removeClass' : 'addClass']('hide');
  });
  $('#Registration_has_entourage').trigger('change');
EOT
);
