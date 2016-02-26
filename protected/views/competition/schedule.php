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
          (<i class="fa fa-rmb"></i><?php echo $competition->getEventFee($event); ?>)
          <?php endif; ?>
        </label>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php $hasManyStages = false; ?>
  <?php $listableSchedules = $competition->getListableSchedules(); ?>
  <?php foreach ($listableSchedules as $day=>$stages): ?>
    <?php foreach ($stages as $stage=>$schedules): ?>
      <?php if (count($stages) > 1): ?>
        <?php $hasManyStages = true; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endforeach; ?>
  <?php if (!empty($listableSchedules)): ?>
  <?php if ($hasManyStages): ?>
  <ul class="nav nav-tabs">
    <li class="active"><a href="#consice" data-toggle="tab"><?php echo Yii::t('common', 'Event List'); ?></a></li>
    <li><a href="#old-style" data-toggle="tab"><?php echo Yii::t('common', 'Schedule'); ?></a></li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="consice">
      <?php foreach ($listableSchedules as $day=>$schedules): ?>
      <div class="panel panel-info">
        <div class="panel-heading">
          <h3 class="panel-title"><?php echo date('Y-m-d', $competition->date + ($day - 1) * 86400); ?></h3>
        </div>
        <div class="panel-body">
          <?php $this->widget('ConciseSchedule', array(
            'competition'=>$competition,
            'schedules'=>$schedules,
          )); ?>
        </div>
      </div>
      <?php endforeach;?>
    </div>
    <div class="tab-pane" id="old-style">
  <?php endif; ?>
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
  <?php if ($hasManyStages): ?>
    </div>
  </div>
  <?php endif; ?>
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
      events = $('tr.event-' + event),
      events2 = $('td.event-' + event),
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
    });
    events2[func]('highlight');
  }).on('mouseenter mouseleave', 'td.event', function(e) {
    var func = e.type == 'mouseenter' ? 'addClass' : 'removeClass';
    var that = $(this);
    var tr = that.parent();
    var endTime = tr.nextAll().eq(this.rowSpan - 1);
    endTime.find('.time span')[func]('hover');
  });
EOT
);