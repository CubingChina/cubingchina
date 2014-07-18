<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
	<dl>
		<dt><?php echo Yii::t('Competition', 'Regulations'); ?></dt>
		<dd><?php echo $competition->getAttributeValue('regulations'); ?></dd>
	</dl>
</div>