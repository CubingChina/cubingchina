<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <dl>
    <?php if ($competition->type == Competition::TYPE_WCA): ?>
    <dt><?php echo Yii::t('Competition', 'WCA Competition'); ?></dt>
    <dd>
      <?php echo Yii::t('Competition', 'This competition is recognized as an official World Cube Association competition. Therefore, all competitors should be familiar with the {regulations}.', array(
      '{regulations}'=>Html::wcaRegulationLink(Yii::t('Competition', 'WCA regulations')),
    ));?>
    </dd>
    <?php endif; ?>
    <dt><?php echo Yii::t('Competition', 'Regulations'); ?></dt>
    <dd><?php echo $competition->getAttributeValue('regulations'); ?></dd>
  </dl>
  <?php $this->renderPartial('disclaimer'); ?>
</div>
