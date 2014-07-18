<div class="col-lg-4 col-lg-offset-4 col-xs-12">
  <div class="panel panel-theme">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo Yii::t('common', 'Reset password'); ?></h3>
    </div>
    <div class="panel-body">
      <?php $form = $this->beginWidget('CActiveForm', array(
        'id'=>'reset-password-form',
        'htmlOptions'=>array(
          'role'=>'form',
        ),
      )); ?>
        <?php echo Html::formGroup(
          $model, 'password', array(),
          $form->labelEx($model, 'password'),
          $form->passwordField($model, 'password', array('class'=>'form-control', 'required'=>true)),
          $form->error($model, 'password', array('class'=>'help-block'))
        ); ?>
        <?php echo Html::formGroup(
          $model, 'repeatPassword', array(),
          $form->labelEx($model, 'repeatPassword'),
          $form->passwordField($model, 'repeatPassword', array('class'=>'form-control', 'required'=>true)),
          $form->error($model, 'repeatPassword', array('class'=>'help-block'))
        ); ?>
        <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
      <?php $this->endWidget(); ?>
    </div>
  </div>
</div>