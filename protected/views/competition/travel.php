<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
	<dl>
		<dt><?php echo Yii::t('Competition', 'Location'); ?></dt>
		<dd>
			<?php if (isset($competition->location[1])): ?>
			<ol>
			<?php foreach ($competition->location as $location): ?>
				<li>
				<?php echo $location->getFullAddress(); ?>
				</li>
			<?php endforeach; ?>
			</ol>
			<?php else: ?>
			<?php echo $competition->location[0]->getFullAddress(); ?>
			<?php endif; ?>
		</dd>
		<dt><?php echo Yii::t('Competition', 'Travel Info'); ?></dt>
		<dd><?php echo $competition->getAttributeValue('travel'); ?></dd>
	</dl>
</div>