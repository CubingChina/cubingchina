<?php

class ConciseSchedule extends Widget {
	public $schedules;
	public $competition;
	public $startTime = PHP_INT_MAX;
	public $endTime = 0;
	public $timeSpan = 300;
	public $space = '';

	public function run() {
		if (Yii::app()->language === 'en') {
			$this->space = ' ';
		}
		$stageKeys = array();
		foreach ($this->schedules as $stage=>$schedules) {
			if ($schedules[0]['schedule']->start_time < $this->startTime) {
				$this->startTime = $schedules[0]['schedule']->start_time;
			}
			$end = end($schedules)['schedule'];
			if ($end->end_time > $this->endTime) {
				$this->endTime = $end->end_time;
			}
			$stageKeys[$stage] = 0;
		}
		echo CHtml::openTag('div', array('class'=>'table-responsive'));
		echo CHtml::openTag('table', array(
			'class'=>'table table-condensed table-bordered concise-schedule',
		));

		//table head
		echo CHtml::openTag('thead');
		echo CHtml::openTag('tr');
		echo '<th class="time">&nbsp</th>';
		foreach ($this->schedules as $stage=>$schedules) {
			echo CHtml::tag('th', array(
				'class'=>'stage-' . $schedules[0]['schedule']->stage,
			), Schedule::getStageText($schedules[0]['schedule']->stage));
		}
		echo CHtml::closeTag('tr');
		echo CHtml::closeTag('thead');
		//table
		echo CHtml::openTag('tbody');
		for ($time = $this->startTime; $time < $this->endTime; $time += $this->timeSpan) {
			$hasEventStart = false;
			$hasEventEnd = false;
			foreach ($this->schedules as $stage=>$schedules) {
				if (!isset($schedules[$stageKeys[$stage]])) {
					continue;
				}
				if ($schedules[$stageKeys[$stage]]['schedule']->start_time == $time) {
					$hasEventStart = true;
				}
				if (isset($schedules[$stageKeys[$stage] - 1]) && $schedules[$stageKeys[$stage] - 1]['schedule']->end_time == $time) {
					$hasEventEnd = true;
				}
			}
			echo CHtml::openTag('tr');
			echo CHtml::openTag('td', array(
				'class'=>'time' . ($hasEventStart || $hasEventEnd ? ' has-time' : ''),
			));
			if ($hasEventStart || $hasEventEnd) {
				echo CHtml::tag('span', array(), date('H:i', $time));
			}
			if ($time == $this->endTime - $this->timeSpan) {
				echo CHtml::tag('span', array(
					'class'=>'end-time',
				), date('H:i', $this->endTime));
			}
			foreach ($this->schedules as $stage=>$schedules) {
				if (!isset($schedules[$stageKeys[$stage]])) {
					echo CHtml::tag('td');
					continue;
				}
				$current = $schedules[$stageKeys[$stage]]['schedule'];
				if ($current->end_time == $time + $this->timeSpan) {
					$stageKeys[$stage]++;
				}
				if ($current->start_time == $time) {
					$this->renderEventCell($schedules[$stageKeys[$stage]]);
				} elseif ($current->start_time > $time) {
					echo CHtml::tag('td');
				}
			}
			echo CHtml::closeTag('tr');
		}
		echo CHtml::closeTag('tr');
		echo CHtml::closeTag('tbody');
		
		echo CHtml::closeTag('table');
		echo CHtml::closeTag('div');
	}

	public function renderEventCell($schedule) {
		$text = array(CHtml::tag('span', array(
			'class'=>'event-icon event-icon-' . $schedule['event'],
		), $schedule['Event'] . ' ' . $schedule['Round']));
		foreach (array('Cut Off', 'Time Limit', 'Group') as $key) {
			if (isset($schedule[$key]) && $schedule[$key] != '') {
				$text[] = Yii::t('Schedule', $key) . $this->space . $schedule[$key];
			}
		}
		echo CHtml::tag('td', array(
			'class'=>implode(' ', array(
				'event-' . $schedule['schedule']->event,
				'round-' . $schedule['schedule']->round,
			)),
			'rowspan'=>($schedule['schedule']->end_time - $schedule['schedule']->start_time) / $this->timeSpan,
		), implode('<br>', $text));
	}
}