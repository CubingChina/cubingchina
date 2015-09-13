<div class="table-responsive">
  <table class="table table-bordered table-condensed table-hover table-boxed pk-table">
    <tbody>
      <tr id="names">
        <td colspan="2"><?php echo Yii::t('Results', 'Name'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"><?php echo Persons::getLinkByNameNId($person['person']->name, $person['person']->id); ?></td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('common', 'WCA ID'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"><?php echo Persons::getWCAIconLinkByNameNId($person['person']->name, $person['person']->id); ?></td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('common', 'Region'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"><?php echo Yii::t('Region', $person['person']->country->name); ?></td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('common', 'Gender'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3">
          <?php echo strtolower($person['person']->gender) == 'f' ? Yii::t('common', 'Female') : Yii::t('common', 'Male'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('Results', 'Competitions'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, 'competitions'); ?>>
          <?php echo count($person['results']['competitions']); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('Results', 'Emulation'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, 'emulation'); ?>>
          <?php echo sprintf('%d.%02d.%02d - %d.%02d.%02d',
            $person['results']['firstCompetition']->year, $person['results']['firstCompetition']->month, $person['results']['firstCompetition']->day,
            $person['results']['lastCompetition']->year, $person['results']['lastCompetition']->endMonth, $person['results']['lastCompetition']->endDay);
          ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2" rowspan="4"><?php echo Yii::t('statistics', Yii::t('Results', 'Records')); ?></td>
      </tr>
      <tr>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo $person['results']['score']; ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo Yii::t('Results', 'WR'); ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo Yii::t('Results', 'CR'); ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo Yii::t('Results', 'NR'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo $person['results']['overAll']['WR']; ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo $person['results']['overAll']['CR']; ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo $person['results']['overAll']['NR']; ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2" rowspan="4"><?php echo Yii::t('statistics', Yii::t('statistics', 'Medals')); ?></td>
      </tr>
      <tr>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, 'medals'); ?>>
          <?php echo $person['results']['score']; ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'medals'); ?>>
          <?php echo Yii::t('statistics', 'Gold'); ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'medals'); ?>>
          <?php echo Yii::t('statistics', 'Silver'); ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'medals'); ?>>
          <?php echo Yii::t('statistics', 'Bronze'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo $person['results']['overAll']['gold']; ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo $person['results']['overAll']['silver']; ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <?php echo $person['results']['overAll']['bronze']; ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php foreach (Events::getNormalTranslatedEvents() as $eventId=>$eventName): ?>
      <?php $nonAverage = in_array("$eventId", array('333mbf', '444bf', '555bf')); ?>
      <?php if (isset($eventIds[$eventId])): ?>
      <tr>
        <td rowspan="<?php echo $nonAverage ? 6 : 8; ?>"><?php echo $eventName; ?></td>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'WR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'SingleWR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'worldRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('common', 'Single'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Single'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'best'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php if (!$nonAverage): ?>
      <tr>
        <td><?php echo Yii::t('common', 'Average'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Average'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.best'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'WR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'AverageWR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.worldRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <tr>
        <td rowspan="2"><?php echo Yii::t('statistics', 'Medals'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
          <?php echo Yii::t('statistics', 'Gold'); ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
          <?php echo Yii::t('statistics', 'Silver'); ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
          <?php echo Yii::t('statistics', 'Bronze'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.gold'); ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.silver'); ?>
        </td>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.bronze'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'Solves/Attempts'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Solves'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.solve'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php Yii::app()->clientScript->registerScript('pk',
<<<EOT
  // var names = $('#names');
  // var table = names.parent().parent();
  // $(document).on('scroll', function() {
  //   if (document.body.scrollTop >= table.offset().top + names.height()) {
  //     if (!names.hasClass('fix-top')) {
  //       names.addClass('fix-top');
  //       $(window).resize();
  //     }
  //   } else {
  //     names.removeClass('fix-top');
  //   }
  // });
  var lastGroup;
  // $('.pk-table tr:nth-of-type(n+5)').each(function() {
  //   var that = $(this);
  //   var hasData = false;
  //   if (that.find('td:first-child').attr('rowspan')) {
  //     lastGroup = that;
  //     return;
  //   }
  //   $(this).find('td').each(function() {
  //     if ($(this).hasClass('winner')) {
  //       hasData = true;
  //       return false;
  //     }
  //   });
  //   if (!hasData) {
  //     $(this).remove();
  //     if (lastGroup) {
  //       var rowspan = parseInt(lastGroup.find('td:first-child').attr('rowspan'));
  //       lastGroup.find('td:first-child').attr('rowspan', rowspan - 1);
  //     }
  //   }
  // })
EOT
);