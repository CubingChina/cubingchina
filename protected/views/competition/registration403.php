<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <div class="alert alert-warning">
    <?php echo Yii::t('Competition', 'Your account is inactive now, please {activate} it before registration.', array(
      '{activate}'=>CHtml::link(Yii::t('common', 'activate'), array('/site/reactivate')),
    )); ?>
  </div>
</div>