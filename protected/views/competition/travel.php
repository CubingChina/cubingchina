<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <dl>
    <dt><?php echo Yii::t('Competition', 'Location'); ?></dt>
    <dd>
      <?php $this->renderPartial('locations', $_data_); ?>
    </dd>
    <?php if ($competition->getAttributeValue('travel')): ?>
    <dt><?php echo Yii::t('Competition', 'Travel Info'); ?></dt>
    <dd><?php echo $competition->getAttributeValue('travel'); ?></dd>
    <?php endif; ?>
  </dl>
  <?php $this->renderPartial('disclaimer'); ?>
</div>
