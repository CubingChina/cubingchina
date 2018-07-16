<?php $showMap = isset($showMap) && $showMap; ?>
<?php if ($competition->tba == Competition::YES): ?>
<?php echo Yii::t('common', 'To be announced'); ?>
<?php elseif ($competition->isMultiLocation()): ?>
<ol>
<?php foreach ($competition->sortedLocations as $location): ?>
  <li>
  <?php echo $location->getFullAddress(); ?><br>
  <?php if ($competition->multi_countries || $competition->complex_multi_location): ?>
  <dl class="dl-horizontal location-delegate">
    <?php if ($competition->multi_countries): ?>
    <dt><?php echo Yii::t('Competition', 'Delegate'); ?></dt>
    <dd>
      <?php echo (new CMarkdownParser())->transform($location->getDelegateInfo()); ?>
    </dd>
    <?php endif; ?>
    <?php if ($location->organizer): ?>
    <dt><?php echo Yii::t('Competition', 'Organizer'); ?></dt>
    <dd><?php echo $location->organizer->getMailtoLink(); ?></dd>
    <?php endif; ?>
    <dt><?php echo Yii::t('Competition', ($competition->complex_multi_location ? 'Base ' : '') . 'Entry Fee'); ?></dt>
    <dd><?php echo $location->getFeeInfo(); ?></dd>
    <?php if ($location->competitor_limit > 0): ?>
    <dt><?php echo Yii::t('Competition', 'Competitor Limit'); ?></dt>
    <dd><?php echo $location->competitor_limit; ?></dd>
  <?php endif; ?>
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
<?php echo $competition->location[0]->getFullAddress(); ?>
<?php if ($showMap): ?>
<?php $this->widget('LocationMap', [
  'competition'=>$competition,
  'location'=>$competition->location[0],
]); ?>
<?php endif; ?>
<?php endif; ?>
