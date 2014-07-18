<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <div class="alert alert-success">
    <?php echo Yii::t('Competition', 'Your registration was submitted successfully.'); ?>
    <?php if (!$accepted): ?>
    <?php echo Yii::t('Competition', 'It will be verified by the organisation team soon. Please wait with patience.'); ?>
    <?php else: ?>
    <?php echo Yii::t('Competition', 'Click {here} to view the competitors list.', array(
      '{here}'=>CHtml::link(Yii::t('common', 'here'), $competition->getUrl('competitors')),
    )); ?>
    <?php endif; ?>
    <?php if (Yii::app()->language === 'zh_cn'): ?>
    <?php echo '点击上面的分享按钮告诉你的小伙伴吧～'; ?>
    <?php endif; ?>
  </div>
</div>