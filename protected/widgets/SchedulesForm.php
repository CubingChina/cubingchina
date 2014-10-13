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
		foreach ($rounds as $key=>$value) {
			$rounds[$key] = Yii::t('common', $value);
		}
		$stages = Schedule::getStages();
		echo CHtml::openTag('tbody');
		foreach ($schedules as $key=>$value) {
			extract($value);
			echo CHtml::openTag('tr');
			echo CHtml::tag('td', array(), CHtml::activeNumberField($model, "{$name}[day][]", array(
				'value'=>$day ?: 1,
				'min'=>1,
				'max'=>4,
			)));
			echo CHtml::tag('td', array(), CHtml::dropDownList(CHtml::activeName($model, "{$name}[stage][]"), $stage, $stages));
			echo CHtml::tag('td', array(), CHtml::activeTextField($model, "{$name}[start_time][]", array(
				'value'=>$start_time ? date('H:i', $start_time) : '',
				'class'=>'time-picker'
			)));
			echo CHtml::tag('td', array(), CHtml::activeTextField($model, "{$name}[end_time][]", array(
				'value'=>$end_time ? date('H:i', $end_time) : '',
				'class'=>'time-picker'
			)));
			echo CHtml::tag('td', array(), CHtml::dropDownList(CHtml::activeName($model, "{$name}[event][]"), $event, $events, array('prompt'=>'')));
			echo CHtml::tag('td', array(), CHtml::activeTextField($model, "{$name}[group][]", array(
				'value'=>$group,
			)));
			echo CHtml::tag('td', array(), CHtml::dropDownList(CHtml::activeName($model, "{$name}[round][]"), $round, $rounds, array('prompt'=>'')));
			echo CHtml::tag('td', array(), CHtml::dropDownList(CHtml::activeName($model, "{$name}[format][]"), $format, $formats, array('prompt'=>'')));
			echo CHtml::tag('td', array(), CHtml::activeTextField($model, "{$name}[cut_off][]", array(
				'value'=>$cut_off,
			)));
			echo CHtml::tag('td', array(), CHtml::activeTextField($model, "{$name}[time_limit][]", array(
				'value'=>$time_limit,
			)));

			echo CHtml::closeTag('tr');
		}
		echo CHtml::closeTag('tbody');

		echo CHtml::closeTag('table');
		echo CHtml::closeTag('div');
		Yii::app()->clientScript->registerScript('SchedulesForm',
<<<EOT
  $(document).on('focus', '#schedules table tbody tr:last-child', function() {
    $(this).clone().insertAfter(this);
    $('.time-picker').timepicker({
      showMeridian: false,
      defaultTime: null
    });
  });
EOT
		);
	}
}