<?php $this->renderPartial('operation', $_data_); ?>
<div class="col-lg-12 competition-<?php echo strtolower($competition->type); ?>">
  <?php if ($competition->tba == Competition::NO): ?>
  <div class="row schedule-event">
    <?php
      foreach ($competition->events as $event=>$value):
        if ($value['round'] <= 0) {
          continue;
        }
    ?>
    <div class="col-lg-3 col-md-4 col-xs-6">
      <div class="checkbox">
        <label>
          <input type="checkbox" data-event="<?php echo $event; ?>">
          <?php echo Events::getEventIcon($event); ?>
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
  <p><?php echo Yii::t('Competition', 'The following schedule is proposed by the organizing team. Be aware that the organizing team may change it according to the actual situation. Please pay attention to the announced schedule at the venue to avoid missing your events.'); ?></p>
  <?php if ($hasManyStages || $userSchedules != []): ?>
  <ul class="nav nav-tabs">
    <?php if ($hasManyStages): ?>
    <li<?php if ($userSchedules == []) echo ' class="active"'; ?>><a href="#concise" data-toggle="tab"><?php echo Yii::t('common', 'Event List'); ?></a></li>
    <?php endif; ?>
    <li<?php if (!$hasManyStages && $userSchedules == []) echo ' class="active"'; ?>><a href="#old-style" data-toggle="tab"><?php echo Yii::t('common', 'Schedule'); ?></a></li>
    <?php if ($userSchedules != []): ?>
    <li<?php if ($userSchedules != []) echo ' class="active"'; ?>><a href="#user"data-toggle="tab"><?php echo Yii::t('common', 'My Schedule'); ?></a></li>
    <?php endif; ?>
  </ul>
  <div class="tab-content schedule">
    <?php if ($hasManyStages): ?>
    <div class="tab-pane<?php if ($userSchedules == []) echo ' active'; ?>" id="concise">
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
    <?php endif; ?>
    <div class="tab-pane<?php if (!$hasManyStages && $userSchedules == []) echo ' active'; ?>" id="old-style">
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
  <?php if ($hasManyStages || $userSchedules != []): ?>
    </div>
    <?php if ($userSchedules != []): ?>
    <div class="tab-pane active" id="user">
      <?php foreach ($userSchedules as $day=>$stages): ?>
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
    </div>
    <?php endif; ?>
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
      func = $(this).prop('checked') ? 'addClass' : 'removeClass',
      func2 = $(this).prop('checked') ? 'removeClass' : 'addClass',
      uncheckAll = true;
    $('.schedule-event input').each(function() {
      if (this.checked) {
        uncheckAll = false;
        return false;
      }
    })
    events.each(function() {
      var c = classes[$(this).data('round')] || 'info';
      $(this)[func](c);
    });
    events2[func2]('unselected');
    $('.concise-schedule')[uncheckAll ? 'removeClass' : 'addClass']('highlight');
  }).on('mouseenter mouseleave', 'td.event', function(e) {
    var func = e.type == 'mouseenter' ? 'addClass' : 'removeClass';
    var that = $(this);
    var tr = that.parent();
    var endTime = tr.nextAll().eq(this.rowSpan - 1);
    endTime.find('.time span')[func]('hover');
  });
EOT
);