<?php if ($competition->tba == Competition::YES): ?>
<?php echo Yii::t('common', 'To be announced'); ?>
<?php elseif ($competition->isMultiLocation()): ?>
<ol>
<?php foreach ($competition->location as $location): ?>
  <li>
  <?php echo $location->getFullAddress(); ?><br>
  <?php if ($competition->multi_countries): ?>
  <dl class="dl-horizontal location-delegate">
    <dt><?php echo Yii::t('Competition', 'Delegate'); ?></dt>
    <dd>
      <?php echo (new CMarkdownParser())->transform($location->getDelegateInfo()); ?>
    </dd>
    <dt><?php echo Yii::t('Competition', 'Entry Fee'); ?></dt>
    <dd><?php echo $location->getFeeInfo(); ?></dd>
  </dl>
  <?php endif; ?>
  </li>
<?php endforeach; ?>
</ol>
<?php else: ?>
<?php echo $competition->location[0]->getFullAddress(); ?>
<?php endif; ?>