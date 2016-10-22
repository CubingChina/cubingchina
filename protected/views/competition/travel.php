<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <dl>
    <dt><?php echo Yii::t('Competition', 'Location'); ?></dt>
    <dd>
      <?php $this->renderPartial('locations', $_data_); ?>
    </dd>
    <dt><?php echo Yii::t('Competition', 'Travel Info'); ?></dt>
    <dd><?php echo $competition->getAttributeValue('travel'); ?></dd>
  </dl>
</div>