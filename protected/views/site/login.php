<div class="col-lg-4 col-lg-offset-4 col-xs-12">
  <div class="panel panel-theme">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo Yii::t('common', 'Login'); ?></h3>
    </div>
    <div class="panel-body">
      <div class="alert alert-warning">
        <?php echo Yii::t('common', 'This legacy version of Cubing China is now read-only. Please visit the new site at {url}.', [
          '{url}'=>ArchiveMode::newSiteLink(ArchiveMode::newSiteUrl()),
        ]); ?>
      </div>
      <?php $form = $this->beginWidget('ActiveForm', array(
        'id'=>'login-form',
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
          $model, 'password', array(),
          $form->labelEx($model, 'password', array('class'=>'login-password')),
          $form->passwordField($model, 'password', array('class'=>'form-control', 'required'=>true)),
          $form->error($model, 'password', array('class'=>'text-danger'))
        ); ?>
        <div class="checkbox">
          <label for="<?php echo Html::activeId($model, 'rememberMe'); ?>">
            <?php echo $form->checkBox($model, 'rememberMe'); ?> <?php echo $model->getAttributeLabel('rememberMe'); ?>
          </label>
        </div>
        <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
        <?php echo ArchiveMode::newSiteLink(Yii::t('common', 'Go to the new Cubing China'), [
          'class'=>'btn btn-info',
        ]); ?>
      <?php $this->endWidget(); ?>
    </div>
  </div>
</div>