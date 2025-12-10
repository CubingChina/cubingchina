<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <dl>
    <?php if ($competition->type == Competition::TYPE_WCA): ?>
    <dt><?php echo Yii::t('Competition', 'WCA Competition'); ?></dt>
    <dd>
      <?php echo Yii::t('Competition', 'This competition is recognized as an official World Cube Association (WCA) competition. Therefore, all competitors should be familiar with the {regulations}.<br/>Competitors are solely responsible for any penalties imposed due to unfamiliarity with the competition rules or failure to comply with on-site regulations. Such penalties shall not be attributed to Cubing China, the WCA, or the competition organizers.', array(
      '{regulations}'=>Html::wcaRegulationLink(Yii::t('Competition', 'WCA regulations')),
    ));?>
    </dd>
    <?php endif; ?>
    <dt><?php echo Yii::t('Competition', 'Regulations'); ?></dt>
    <dd>
      <?php if (!$competition->automatic_regulations): ?>
      <?php echo $competition->getAttributeValue('regulations'); ?>
      <?php elseif ($this->isCN): ?>
        <?php $this->renderPartial('regulations_cn', $_data_); ?>
      <?php else: ?>
        <?php $this->renderPartial('regulations_en', $_data_); ?>
      <?php endif; ?>
    </dd>
  </dl>
  <?php $this->renderPartial('disclaimer'); ?>
</div>
