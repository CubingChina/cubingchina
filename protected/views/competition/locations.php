<?php $showMap = isset($showMap) && $showMap; ?>
<?php if ($competition->tba == Competition::YES): ?>
<?php echo Yii::t('common', 'To be announced'); ?>
<?php elseif ($competition->isMultiLocation()): ?>
<ol>
<?php foreach ($competition->sortedLocations as $location): ?>
  <li>
  <?php if ($competition->multi_countries || $competition->complex_multi_location): ?>
  <?php $showRegion = $competition->multi_countries; ?>
  <?php if (array_map(function($location) { return $location->country_id; }, $competition->location) == array_fill(0, count($competition->location), $competition->location[0]->country_id)): ?>
  <?php $showRegion = false; ?>
  <?php endif; ?>
  <div class="competition-location">
    <div class="attribute"><?php echo Yii::t('common', 'City'); ?></div>
    <div class="value"><?php echo $location->getCityName(true, $showRegion); ?></div>
    <div class="attribute"><?php echo Yii::t('Competition', 'Address'); ?></div>
    <div class="value"><?php echo $location->getAttributeValue('venue'); ?></div>
    <?php if ($competition->multi_countries): ?>
    <div class="attribute"><?php echo Yii::t('Competition', 'Delegate'); ?></div>
    <div class="value">
      <?php echo (new CMarkdownParser())->transform($location->getDelegateInfo()); ?>
    </div>
    <?php endif; ?>
    <?php if ($location->organizer): ?>
    <div class="attribute"><?php echo Yii::t('Competition', 'Organizer'); ?></div>
    <div class="value"><?php echo $location->organizer->getMailtoLink(); ?></div>
    <?php endif; ?>
    <div class="attribute"><?php echo Yii::t('Competition', ($competition->complex_multi_location ? 'Base ' : '') . 'Entry Fee'); ?></div>
    <div class="value"><?php echo $location->getFeeInfo(); ?></div>
    <?php if ($location->payment_method): ?>
    <div class="attribute"><?php echo Yii::t('Competition', 'Payment Method'); ?></div>
    <div class="value"><?php echo $location->payment_method; ?></div>
    <?php endif; ?>
    <?php if ($location->competitor_limit > 0): ?>
    <div class="attribute"><?php echo Yii::t('Competition', 'Competitor Limit'); ?></div>
    <div class="value"><?php echo $location->competitor_limit; ?></div>
    <?php endif; ?>
  </div>
  <?php else: ?>
  <?php echo $location->getFullAddress(); ?><br>
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
