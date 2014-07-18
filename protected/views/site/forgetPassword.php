<div class="col-lg-4 col-lg-offset-4 col-xs-12">
  <div class="panel panel-theme">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo Yii::t('common', 'Find my password'); ?></h3>
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
          Html::activeTextField($model, 'email', array('class'=>'form-control', 'autofocus'=>true, 'required'=>true, 'type'=>'email')),
          $form->error($model, 'email', array('class'=>'text-danger'))
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