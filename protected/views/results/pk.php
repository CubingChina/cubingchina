<div class="table-responsive">
  <table class="table table-bordered table-condensed table-hover table-boxed pk-table">
    <tbody>
      <tr id="names">
        <td colspan="2"><?php echo Yii::t('Results', 'Name'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td><?php echo Persons::getLinkByNameNId($person['person']->name, $person['person']->id); ?></td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('common', 'WCA ID'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td><?php echo Persons::getWCAIconLinkByNameNId($person['person']->name, $person['person']->id); ?></td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('common', 'Region'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td><?php echo Yii::t('Region', $person['person']->country->name); ?></td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('common', 'Gender'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'gender'); ?>>
          <?php echo strtolower($person['person']->gender) == 'f' ? Yii::t('common', 'Female') : Yii::t('common', 'Male'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('Results', 'Competitions'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'competitions'); ?>>
          <?php echo count($person['results']['competitions']); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('Results', 'Emulation'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, 'emulation'); ?>>
          <?php echo sprintf('%d.%02d.%02d - %d.%02d.%02d',
            $person['results']['firstCompetition']->year, $person['results']['firstCompetition']->month, $person['results']['firstCompetition']->day,
            $person['results']['lastCompetition']->year, $person['results']['lastCompetition']->endMonth, $person['results']['lastCompetition']->endDay);
          ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php foreach ($persons[0]['results']['overAll'] as $type=>$overAll): ?>
      <tr>
        <td colspan="2"><?php echo Yii::t('statistics', Yii::t('Results', ucfirst($type))); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $type); ?>>
          <?php echo $person['results']['overAll'][$type]; ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
      <?php foreach (Events::getNormalTranslatedEvents() as $eventId=>$eventName): ?>
      <?php $nonAverage = in_array("$eventId", array('333mbf', '444bf', '555bf')); ?>
      <?php if (isset($eventIds[$eventId])): ?>
      <tr>
        <td rowspan="<?php echo $nonAverage ? 8 : 12; ?>"><?php echo $eventName; ?></td>
        <td><?php echo Yii::t('statistics', 'NR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'SingleNR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'countryRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'CR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'SingleCR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'continentRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'WR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'SingleWR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'worldRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('common', 'Single'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Single'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'best'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php if (!$nonAverage): ?>
      <tr>
        <td><?php echo Yii::t('common', 'Average'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Average'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.best'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'WR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'AverageWR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.worldRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'CR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'AverageCR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.continentRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'NR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'AverageNR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.countryRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <tr>
        <td><?php echo Yii::t('statistics', 'Gold'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Gold'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.gold'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'Silver'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Silver'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.silver'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'Bronze'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Bronze'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.bronze'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('statistics', 'Solves/Attempts'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Solves'); ?>>
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
  var names = $('#names');
  var table = names.parent().parent();
  $(window).on('resize', function() {
    names.css({
      left: table.offset().left,
      right: $(window).width() - table.outerWidth() - table.offset().left
    });
    var td = names.next().find('td');
    names.find('td').each(function(index) {
      var width = td.eq(index).width();
      td.eq(index).width(width);
      $(this).width(width);
    });
  }).resize();
  $(document).on('scroll', function() {
    if (document.body.scrollTop >= table.offset().top + names.height()) {
      if (!names.hasClass('fix-top')) {
        names.addClass('fix-top');
        $(window).resize();
      }
    } else {
      names.removeClass('fix-top');
    }
  });
  var lastGroup;
  $('.pk-table tr:nth-of-type(n+5)').each(function() {
    var that = $(this);
    var hasData = false;
    console.log(that.find('td:first-child'))
    if (that.find('td:first-child').attr('rowspan')) {
      lastGroup = that;
    }
    $(this).find('td').each(function() {
      if ($(this).hasClass('winner')) {
        hasData = true;
        return false;
      }
    });
    if (!hasData) {
      $(this).remove();
      if (lastGroup) {
        var rowspan = parseInt(lastGroup.find('td:first-child').attr('rowspan'));
        lastGroup.find('td:first-child').attr('rowspan', rowspan - 1);
      }
    }
  })
EOT
);