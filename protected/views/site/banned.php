<div class="content-wrapper col-lg-12">
  <div class="alert alert-danger">
    <?php echo Yii::t('common', 'Your account was suspended. Please contact the {administrator} if you have any question.', array(
    	'{administrator}'=>CHtml::mailto(Yii::t('common', 'administrator'), Yii::app()->params->adminEmail),
    )); ?>
  </div>
</div>