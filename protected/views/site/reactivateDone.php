<div class="content-wrapper col-lg-12">
  <div class="alert alert-success">
    <?php echo Yii::t('common', 'An activation mail was sent to your email address, please follow the description to activate your account.'); ?>
  </div>
  <div class="alert alert-info">
    <?php echo Yii::t('common', 'If you have got problems in activating your account, please contact the administrator via {email}.', array(
      '{email}'=>CHtml::mailto('<i class="fa fa-envelope"></i> ' . Yii::app()->params->adminEmail, Yii::app()->params->adminEmail),
    )); ?>
  </div>
</div>