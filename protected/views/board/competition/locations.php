<?php $showMap = isset($showMap) && $showMap; ?>
<?php if ($competition->tba == Competition::YES): ?>
<?php echo Yii::t('common', 'To be announced'); ?>
<?php elseif ($competition->isMultiLocation()): ?>
<ol>
<?php foreach ($competition->sortedLocations as $location): ?>
  <li>
  <?php echo $location->getFullAddress(false); ?><br>
  <?php echo $location->venue_zh; ?><br>
  <?php echo $location->venue; ?><br>
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
  <?php if ($showMap): ?>
  <?php $this->widget('LocationMap', [
    'competition'=>$competition,
    'location'=>$location,
  ]); ?>
  <?php endif; ?>
  </li>
<?php endforeach; ?>
</ol>
<?php else: ?>
<?php echo $competition->location[0]->getFullAddress(false); ?><br>
<?php echo $competition->location[0]->venue_zh; ?><br>
<?php echo $competition->location[0]->venue; ?>
<?php if ($showMap): ?>
<?php $this->widget('LocationMap', [
  'competition'=>$competition,
  'location'=>$competition->location[0],
]); ?>
<?php endif; ?>
<?php endif; ?>
