<div class="table-responsive">
  <table class="table table-bordered table-condensed table-hover table-boxed pk-table">
    <thead>
      <tr class="persons-<?php echo count($persons); ?>">
        <th colspan="2" class="pk-attribute"><?php echo Yii::t('Results', 'Name'); ?></th>
        <?php foreach ($persons as $person): ?>
        <th><?php echo Persons::getLinkByNameNId($person['person']->name, $person['person']->id); ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
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
        <td>
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
      <tr>
        <td><?php echo Yii::t('statistics', Yii::t('Results', 'Records')); ?></td>
        <td>
          <?php echo Yii::t('statistics', 'Score'); ?>:<br>
          WR * 10 + <br>
          CR * 5 + <br>
          NR * 1
        </td>
        <?php foreach ($persons as $person): ?>
        <td class="has-table"<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
          <table class="table table-bordered table-condensed table-hover table-boxed table-hover">
            <tbody>
              <tr>
                <td colspan="3"<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
                  <?php echo $person['results']['score']; ?>
                </td>
              </tr>
              <tr>
                <td>
                  <?php echo Yii::t('Results', 'WR'); ?>
                </td>
                <td>
                  <?php echo Yii::t('Results', 'CR'); ?>
                </td>
                <td>
                  <?php echo Yii::t('Results', 'NR'); ?>
                </td>
              </tr>
              <tr>
                <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
                  <?php echo $person['results']['overAll']['WR']; ?>
                </td>
                <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
                  <?php echo $person['results']['overAll']['CR']; ?>
                </td>
                <td<?php echo $this->getWinnerCSSClass($winners, $person, 'records'); ?>>
                  <?php echo $person['results']['overAll']['NR']; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('statistics', Yii::t('statistics', 'Medals')); ?></td>
        <?php foreach ($persons as $person): ?>
        <td class="has-table"<?php echo $this->getWinnerCSSClass($winners, $person, 'medals'); ?>>
          <table class="table table-bordered table-condensed table-boxed table-hover">
            <tbody>
              <tr>
                <td>
                  <?php echo Yii::t('statistics', 'Gold'); ?>
                </td>
                <td>
                  <?php echo Yii::t('statistics', 'Silver'); ?>
                </td>
                <td>
                  <?php echo Yii::t('statistics', 'Bronze'); ?>
                </td>
              </tr>
              <tr>
                <td<?php echo $this->getWinnerCSSClass($winners, $person, 'medals'); ?>>
                  <?php echo $person['results']['overAll']['gold']; ?>
                </td>
                <td<?php echo $this->getWinnerCSSClass($winners, $person, 'medals'); ?>>
                  <?php echo $person['results']['overAll']['silver']; ?>
                </td>
                <td<?php echo $this->getWinnerCSSClass($winners, $person, 'medals'); ?>>
                  <?php echo $person['results']['overAll']['bronze']; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php foreach (Events::getNormalTranslatedEvents() as $eventId=>$eventName): ?>
      <?php if (isset($eventIds[$eventId])): ?>
      <?php $nonAverage = in_array("$eventId", array('333mbf', '444bf', '555bf')) || !$eventIds[$eventId]; ?>
      <tr class="event-row">
        <td colspan="<?php echo 2 + count($persons); ?>">&nbsp;</td>
      </tr>
      <tr class="event-row">
        <td colspan="<?php echo count($persons) + 2; ?>">
          <?php echo CHtml::tag('span', array(
            'class'=>'event-icon event-icon-' . $eventId,
          ), $eventName); ?>
        </td>
      </tr>
      <tr class="event-row">
        <td rowspan="<?php echo 3 + $sameCountry + $sameContinent; ?>"><?php echo Yii::t('common', 'Single'); ?></td>
      </tr>
      <tr>
        <td><?php echo Yii::t('common', 'Result'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Single'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'best'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php if ($sameCountry): ?>
      <tr>
        <td><?php echo Yii::t('statistics', 'NR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'SingleNR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'countryRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <?php if ($sameContinent): ?>
      <tr>
        <td><?php echo Yii::t('statistics', 'CR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'SingleCR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'continentRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <tr>
        <td><?php echo Yii::t('statistics', 'WR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'SingleWR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'worldRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php if (!$nonAverage): ?>
      <tr class="event-row">
        <td rowspan="<?php echo 3 + $sameCountry + $sameContinent; ?>"><?php echo Yii::t('common', 'Average'); ?></td>
      </tr>
      <tr>
        <td><?php echo Yii::t('common', 'Result'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Average'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.best'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php if ($sameCountry): ?>
      <tr>
        <td><?php echo Yii::t('statistics', 'NR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'AverageNR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.countryRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <?php if ($sameContinent): ?>
      <tr>
        <td><?php echo Yii::t('statistics', 'CR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'AverageCR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.continentRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <tr>
        <td><?php echo Yii::t('statistics', 'WR'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'AverageWR'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'average.worldRank'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <?php if (!$nonAverage): ?>
      <tr>
        <td colspan="2"><?php echo Yii::t('common', 'Single'); ?>/<?php echo Yii::t('common', 'Average'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'SDA'); ?>>
          <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.sda'); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <tr>
        <td colspan="2"><?php echo Yii::t('statistics', 'Solves/Attempts'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Solves'); ?>>
          <?php $solves = $this->getPersonRankValue($person['results'], $eventId, 'medals.solve'); ?>
          <?php echo $solves; ?>
          <?php if ($solves !== '-' && $eventId === '333bf') {
            echo sprintf('(%.2f%%)', $this->evaluateExpression($solves) * 100);
          } ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td colspan="2"><?php echo Yii::t('statistics', 'Medals'); ?></td>
        <?php foreach ($persons as $person): ?>
        <td class="has-table"<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
          <table class="table table-bordered table-condensed table-boxed table-hover">
            <tbody>
              <tr>
                <td>
                  <?php echo Yii::t('statistics', 'Gold'); ?>
                </td>
                <td>
                  <?php echo Yii::t('statistics', 'Silver'); ?>
                </td>
                <td>
                  <?php echo Yii::t('statistics', 'Bronze'); ?>
                </td>
              </tr>
              <tr>
                <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
                  <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.gold'); ?>
                </td>
                <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
                  <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.silver'); ?>
                </td>
                <td<?php echo $this->getWinnerCSSClass($winners, $person, $eventId . 'Medals'); ?>>
                  <?php echo $this->getPersonRankValue($person['results'], $eventId, 'medals.bronze'); ?>
                </td>
              </tr>
            </tbody>
          </table>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php if (isset($rivalries[$eventId])): ?>
      <tr>
        <td rowspan="2"><?php echo Yii::t('common', 'Rivalries'); ?></td>
        <td><?php echo Yii::t('common', 'Overall'); ?></td>
        <?php foreach ($persons as $key=>$person): ?>
        <td<?php echo $this->getRivalryWinnerCSSClass($person, $eventId, $rivalries, 'overAll'); ?>>
          <?php echo $this->getRivalryResult($rivalries[$eventId][$person['person']->id]['overAll']); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <tr>
        <td><?php echo Yii::t('common', 'Finals'); ?></td>
        <?php foreach ($persons as $key=>$person): ?>
        <td<?php echo $this->getRivalryWinnerCSSClass($person, $eventId, $rivalries, 'final'); ?>>
          <?php echo $this->getRivalryResult($rivalries[$eventId][$person['person']->id]['final']); ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endif; ?>
      <?php endif; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php Yii::app()->clientScript->registerScript('pk',
<<<EOT
  //hide empty row
  var lastGroup;
  $('.pk-table tr:nth-of-type(n+5)').each(function() {
    var that = $(this);
    var hasData = false;
    if (that.hasClass('event-row')) {
      lastGroup = null;
      return;
    }
    if (that.find('td:first-child').attr('rowspan')) {
      lastGroup = that;
      return;
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
  });
  //make a sample table
  var pkTable = $('.pk-table').parent();
  var fixTable = pkTable.clone();
  fixTable.css({
    height: pkTable.find('tr:first-child').height() + 1,
    overflow: 'hidden'
  }).insertBefore(pkTable).addClass('hide');
  $(window).on('resize', function() {
    fixTable.css({
      left: pkTable.offset().left,
      right: $(window).width() - pkTable.outerWidth() - pkTable.offset().left
    });
  }).resize();
  $(document).on('scroll', function() {
    if (document.body.scrollTop >= pkTable.offset().top + fixTable.height()) {
      if (!fixTable.hasClass('fix-top')) {
        fixTable.addClass('fix-top').removeClass('hide');
      }
    } else {
      fixTable.removeClass('fix-top').addClass('hide');
    }
  });
  pkTable.on('scroll touchmove', function() {
    fixTable[0].scrollLeft = this.scrollLeft;
  });
EOT
);