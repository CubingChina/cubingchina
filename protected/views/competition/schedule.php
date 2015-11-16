<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php if ($competition->tba == Competition::NO): ?>
  <div class="row schedule-event">
    <?php
      foreach ($competition->events as $event=>$value):
        if ($value['round'] <= 0) {
          continue;
        }
    ?>
    <div class="col-lg-3 col-md-4 col-xs-<?php echo Yii::app()->language == 'zh_cn' ? 6 : 12; ?>">
      <div class="checkbox">
        <label class="event-icon event-icon-<?php echo $event; ?>">
          <input type="checkbox" data-event="<?php echo $event; ?>">
          <?php echo Yii::t('event', Events::getFullEventName($event)); ?>
          - <?php echo $value['round']; ?><?php echo Yii::t('Competition', $value['round'] > 1 ? ' rounds' : ' round'); ?>
          <?php if ($value['fee'] > 0): ?>
          (<i class="fa fa-rmb"></i><?php echo $competition->secondStageFee($value['fee'], $competition->second_stage_date <= time() && $competition->second_stage_all); ?>)
          <?php endif; ?>
        </label>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php $hasManyStages = false; ?>
  <?php $listableSchedules = $competition->getListableSchedules(); ?>
  <?php if (!empty($listableSchedules)): ?>
  <?php foreach ($listableSchedules as $day=>$stages): ?>
    <?php foreach ($stages as $stage=>$schedules): ?>
      <?php if (count($stages) > 1): ?>
        <?php $hasManyStages = true; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endforeach; ?>
  <?php foreach ($listableSchedules as $day=>$stages): ?>
  <div class="panel panel-info">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo date('Y-m-d', $competition->date + ($day - 1) * 86400); ?></h3>
    </div>
    <div class="panel-body">
      <?php foreach ($stages as $stage=>$schedules): ?>
      <?php if ($hasManyStages): ?>
      <h3><?php echo Schedule::getStageText($stage); ?></h3>
      <?php endif; ?>
      <?php $this->widget('GridView', array(
        'dataProvider'=>new CArrayDataProvider($schedules, array(
          'pagination'=>false,
        )),
        'enableSorting'=>false,
        'front'=>true,
        'rowCssClassExpression'=>'"event-" . $data["event"]',
        'rowHtmlOptionsExpression'=>'array(
          "data-round"=>$data["round"],
        )',
        'columns'=>$competition->getScheduleColumns($schedules),
      ));
      ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <?php else: ?>
  <div class="panel panel-info">
    <div class="panel-body">
      <?php echo Yii::t('Competition', 'The schedule is to be determined.'); ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php
Yii::app()->clientScript->registerScript('schedule',
<<<EOT
  $(document).on('change', '.schedule-event input', function() {
    var event = $(this).data('event'),
      events = $('tr.event-' + event)
      classes = {
        1: 'info',
        2: 'success',
        3: 'warning',
        c: 'danger',
        d: 'info',
        e: 'success',
        f: 'danger',
        g: 'warning'
      },
      func = $(this).prop('checked') ? 'addClass' : 'removeClass';
    events.each(function() {
      var c = classes[$(this).data('round')] || 'info';
      $(this)[func](c);
    })
  })
EOT
);