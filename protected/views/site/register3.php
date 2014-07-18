<?php $this->renderPartial('registerSide', $_data_); ?>
<div class="content-wrapper col-md-10 col-sm-9">
  <h3 class="has-divider text-highlight">
    <?php echo Yii::t('common', 'Step 3. Register successful.'); ?>
  </h3>
  <div class="progress progress-striped active">
    <div class="progress-bar progress-bar-theme" style="width: 100%">
      <span class="sr-only"><?php echo Yii::t('common', 'Step Three'); ?></span>
    </div>
  </div>
  <div class="box box-theme">
    <?php echo Yii::t('common', 'Welcome to Cubing China!'); ?>
    <?php if ($this->user && $this->user->isUnchecked()): ?>
    <br>
    <?php echo Yii::t('common', 'An activation mail was sent to your email address, please follow the description to activate your account.'); ?>
    <br>
    <?php echo Yii::t('common', 'If you have got problems in activating your account, please contact the administrator via {email}.', array(
      '{email}'=>CHtml::mailto('<i class="fa fa-envelope"></i> ' . Yii::app()->params->adminEmail, Yii::app()->params->adminEmail),
    )); ?>
    <?php endif; ?>
  </div>
</div>