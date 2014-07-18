<div class="col-lg-8 col-lg-offset-2 col-xs-12">
  <div class="panel panel-theme">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo Yii::t('common', 'Activate Account'); ?></h3>
    </div>
    <div class="panel-body">
      <?php $form = $this->beginWidget('CActiveForm', array(
        'id'=>'forget-password-form',
        'htmlOptions'=>array(
          //'class'=>'form-login',
          'role'=>'form',
        ),
      )); ?>
        <?php echo Html::formGroup(
          $model, 'email', array(),
          $form->labelEx($model, 'email'),
          Html::activeTextField($model, 'email', array('readonly'=>true)),
          Yii::t('common', 'If you have got problems in activating your account, please contact the administrator via {email}.', array(
            '{email}'=>CHtml::mailto('<i class="fa fa-envelope"></i> ' . Yii::app()->params->adminEmail, Yii::app()->params->adminEmail),
          ))
        );?>
        <?php echo Html::formGroup(
          $model, 'verifyCode', array(),
          $form->labelEx($model, 'verifyCode'),
          Html::activeTextField($model, 'verifyCode'),
          $this->widget('CCaptcha', array(
            'clickableImage'=>true,
            'showRefreshButton'=>false,
          ), true),
          $form->error($model, 'verifyCode', array('class'=>'text-danger'))
        );?>
        <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
      <?php $this->endWidget(); ?>
    </div>
  </div>
</div>