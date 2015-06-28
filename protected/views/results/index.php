<div class="col-lg-12 competition-wca">
  <p><?php echo Yii::t('statistics', 'Welcome to the Cubing China results page, where you can find the personal rankings, official records, and fun statistics.'); ?></p>
  <ul>
    <li>
      <p><?php echo CHtml::link(Yii::t('common', 'Persons'), array('/results/person')); ?></p>
      <p><?php echo Yii::t('statistics', 'Here you can find WCA competitors.'); ?></p>
    </li>
    <li>
      <p><?php echo CHtml::link(Yii::t('Competitions', 'Competitions'), array('/results/competition')); ?></p>
      <p><?php echo Yii::t('statistics', 'Here you can find WCA competitions.'); ?></p>
    </li>
    <li>
      <p><?php echo CHtml::link(Yii::t('common', 'Rankings'), array('/results/rankings')); ?></p>
      <p><?php echo Yii::t('statistics', 'Global personal rankings in each official event are listed, based on the {url}.', array(
        '{url}'=>CHtml::link(Yii::t('statistics', 'official WCA rankings'), 'https://www.worldcubeassociation.org/results/events.php?regionId=China', array('target'=>'_blank')),
      )); ?></p>
    </li>
    <li>
      <p><?php echo CHtml::link(Yii::t('common', 'Records'), array('/results/records')); ?></p>
      <p><?php echo Yii::t('statistics', 'Regional records are displayed on the page, based on the {url}.', array(
        '{url}'=>CHtml::link(Yii::t('statistics', 'official WCA records'), 'https://www.worldcubeassociation.org/results/regions.php', array('target'=>'_blank')),
      )); ?></p>
    </li>
    <li>
      <p><?php echo CHtml::link(Yii::t('common', 'Statistics'), array('/results/statistics')); ?></p>
      <p><?php echo Yii::t('statistics', 'We generate several WCA statistics about Chinese competitions and competitors, based on {url}.', array(
        '{url}'=>CHtml::link(Yii::t('statistics', 'official WCA statistics'), 'https://www.worldcubeassociation.org/results/statistics.php', array('target'=>'_blank')),
      )); ?></p>
    </li>
  </ul>
</div>