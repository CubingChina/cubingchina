<?php

class SchedulesForm extends Widget {
	public $model;
	public $name = 'events';
	public $htmlOptions = array();
	public $tableOptions = array();
	public function run() {
		$htmlOptions = $this->htmlOptions;
		$tableOptions = $this->tableOptions;
		$model = $this->model;
		$name = $this->name;
		if (!isset($htmlOptions['class'])) {
			$htmlOptions['class'] = 'table-responsive';
		} else {
			$htmlOptions['class'] .= ' table-responsive';
		}
		$htmlOptions['id'] = 'schedules';
		if (!isset($tableOptions['class'])) {
			$tableOptions['class'] = 'table table-condensed';
		} else {
			$tableOptions['class'] .= ' table table-condensed';
		}
		echo CHtml::openTag('div', $htmlOptions);
		echo CHtml::openTag('table', $tableOptions);
		echo CHtml::openTag('thead');
		echo CHtml::openTag('tr');
		echo CHtml::tag('th', array(), '第几天');
		echo CHtml::tag('th', array(), '赛区');
		echo CHtml::tag('th', array(), '开始时间');
		echo CHtml::tag('th', array(), '结束时间');
		echo CHtml::tag('th', array(), '项目');
		echo CHtml::tag('th', array(), '分组');
		echo CHtml::tag('th', array(), '轮次');
		echo CHtml::tag('th', array(), '赛制');
		echo CHtml::tag('th', array(), '及格线(秒)');
		echo CHtml::tag('th', array(), '还原时限(秒)');
		echo CHtml::tag('th', array(), '人数');
		echo CHtml::closeTag('tr');
		echo CHtml::closeTag('thead');

		$schedules = $model->$name;
		$schedules[] = Schedule::model()->attributes;
		$events = Events::getScheduleEvents();
		foreach ($events as $key=>$value) {
			$events[$key] = Yii::t('event', $value);
		}
		$formats = Formats::getAllFormats();
		foreach ($formats as $key=>$value) {
			$formats[$key] = Yii::t('common', $value);
		}
		$rounds = Rounds::getAllRounds();
		unset($rounds['0']);
		unset($rounds['h']);
		unset($rounds['b']);
		foreach ($rounds as $key=>$value) {
			$rounds[$key] = Yii::t('Rounds', $value);
		}
		$stages = Schedule::getStages();
		echo CHtml::openTag('tbody');
		// CVarDumper::dump($schedules, 10, 1);exit;
		foreach ($schedules as $key=>$value) {
			extract($value);
			echo CHtml::openTag('tr');
			echo CHtml::tag('td', array(), CHtml::activeNumberField($model, "{$name}[day][$key]", array(
				'value'=>$day ?: 1,
				'min'=>1,
				'max'=>4,
			)));
			echo CHtml::tag('td', array(), CHtml::dropDownList(CHtml::activeName($model, "{$name}[stage][$key]"), $stage, $stages));
			echo CHtml::tag('td', array(), CHtml::activeTextField($model, "{$name}[start_time][$key]", array(
				'value'=>$start_time ? date('H:i', $start_time) : '',
				'class'=>'datetime-picker',
				'data-date-format'=>'hh:ii',
				'data-max-view'=>'1',
				'data-start-view'=>'1',
			)));
			echo CHtml::tag('td', array(), CHtml::activeTextField($model, "{$name}[end_time][$key]", array(
				'value'=>$end_time ? date('H:i', $end_time) : '',
				'class'=>'datetime-picker',
				'data-date-format'=>'hh:ii',
				'data-max-view'=>'1',
				'data-start-view'=>'1',
			)));
			echo CHtml::tag('td', array(), CHtml::dropDownList(CHtml::activeName($model, "{$name}[event][$key]"), $event, $events, array(
				'prompt'=>'',
				'class'=>'schedule-event',
			)));
			echo CHtml::tag('td', array(), CHtml::activeTextField($model, "{$name}[group][$key]", array(
				'value'=>$group,
			)));
			echo CHtml::tag('td', array(), CHtml::dropDownList(CHtml::activeName($model, "{$name}[round][$key]"), $round, $rounds, array(
				'prompt'=>'',
				'class'=>'schedule-round',
			)));
			echo CHtml::tag('td', array(), CHtml::dropDownList(CHtml::activeName($model, "{$name}[format][$key]"), $format, $formats, array('prompt'=>'')));
			echo CHtml::tag('td', array(), CHtml::activeNumberField($model, "{$name}[cut_off][$key]", array(
				'value'=>$cut_off,
				// 'max'=>3600,
			)));
			echo CHtml::openTag('td');
			echo CHtml::activeNumberField($model, "{$name}[time_limit][$key]", array(
				'value'=>$time_limit,
				// 'max'=>3600,
			));
			echo CHtml::activeCheckBox($model, "{$name}[cumulative][$key]", array(
				'checked'=>$cumulative == Schedule::YES,
			));
			echo CHtml::closeTag('td');
			echo CHtml::tag('td', array(), CHtml::activeNumberField($model, "{$name}[number][$key]", array(
				'value'=>$number,
			)));

			echo CHtml::closeTag('tr');
		}
		echo CHtml::closeTag('tbody');

		echo CHtml::closeTag('table');
		echo CHtml::closeTag('div');
		$onlyScheculeEvents = json_encode(Events::getOnlyScheduleEvents());
		Yii::app()->clientScript->registerScript('SchedulesForm',
<<<EOT
  var onlyScheculeEvents = {$onlyScheculeEvents};
  var combinedRounds = ['c', 'd', 'e', 'g'];
  var length = $('#schedules table tbody tr').length;
  $(document).on('focus', '#schedules table tbody tr:last-child', function() {
    var last = $(this).clone().insertAfter(this);
    last.find('input, select').each(function() {
      var name = this.name;
      $(this).attr('name', name.replace(/\[\d*\]/, '[' + length + ']'));
    });
    length++;
    last.find('.datetime-picker').datetimepicker({
      autoclose: true
    });
  }).on('change', '.schedule-event', function(e) {
    var that = $(this);
    var event = that.val();
    if (onlyScheculeEvents[event] !== undefined) {
      that.parent().nextAll().find('select, input').prop('disabled', true);
    } else {
      that.parent().nextAll().find('select, input').prop('disabled', false);
    }
  }).on('change', '.schedule-round', function(e) {
    var that = $(this);
    var round = that.val();
    var format = that.parent().next().find('option');
    format.prop('disabled', false);
    if (combinedRounds.indexOf(round) > -1) {
      format.filter(':not([value="2/a"]):not([value="1/m"])').prop('disabled', true);
    } else {
      format.filter('[value="2/a"], [value="1/m"]').prop('disabled', true);
    }
  });
  $('.schedule-event, .schedule-round').change();
EOT
		);
	}
}