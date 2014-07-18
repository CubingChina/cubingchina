<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
	<dl>
		<dt><?php echo Yii::t('Competition', 'Location'); ?></dt>
		<dd>
			<?php if ($this->isCN): ?>
			<?php echo $competition->province->getAttributeValue('name');?><?php echo $competition->city->getAttributeValue('name');?><?php echo $competition->getAttributeValue('venue'); ?>
			<?php else: ?>
			<?php echo $competition->getAttributeValue('venue'); ?>, <?php echo $competition->city->getAttributeValue('name');?>, <?php echo $competition->province->getAttributeValue('name');?>
			<?php endif; ?>
		</dd>
		<dt><?php echo Yii::t('Competition', 'Travel Info'); ?></dt>
		<dd><?php echo $competition->getAttributeValue('travel'); ?></dd>
	</dl>
</div>