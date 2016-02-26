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
		$headColSpans = array();
		foreach ($this->schedules as $stage=>$schedules) {
			if ($schedules[0]['schedule']->start_time < $this->startTime) {
				$this->startTime = $schedules[0]['schedule']->start_time;
			}
			$end = end($schedules)['schedule'];
			if ($end->end_time > $this->endTime) {
				$this->endTime = $end->end_time;
			}
			$headColSpans[$stage] = 0;
		}
		$this->processSchedules();
		$stageKeys = array();
		$colSpans = array();
		foreach ($this->schedules as $key=>$schedules) {
			$stageKeys[$key] = 0;
			$colSpans[$key] = 1;
			$headColSpans[$schedules[0]['schedule']->stage]++;
		}
		echo CHtml::openTag('div', array('class'=>'table-responsive'));
		echo CHtml::openTag('table', array(
			'class'=>'table table-condensed concise-schedule',
		));

		//table head
		echo CHtml::openTag('thead');
		echo CHtml::openTag('tr');
		echo '<th class="time">&nbsp</th>';
		foreach ($headColSpans as $stage=>$colSpan) {
			echo CHtml::tag('th', array(
				'colspan'=>$colSpan,
				'class'=>'stage-' . $stage,
			), Schedule::getStageText($stage));
		}
		echo '<th class="time">&nbsp</th>';
		echo CHtml::closeTag('tr');
		echo CHtml::closeTag('thead');
		//table
		echo CHtml::openTag('tbody');
		for ($time = $this->startTime; $time < $this->endTime; $time += $this->timeSpan) {
			$hasEventStartOrEnd = false;
			foreach ($this->schedules as $key=>$schedules) {
				if (isset($schedules[$stageKeys[$key]]) && $schedules[$stageKeys[$key]]['schedule']->start_time == $time) {
					$hasEventStartOrEnd = true;
					break;
				}
				if (isset($schedules[$stageKeys[$key] - 1]) && $schedules[$stageKeys[$key] - 1]['schedule']->end_time == $time) {
					$hasEventStartOrEnd = true;
					break;
				}
			}
			echo CHtml::openTag('tr');
			echo CHtml::tag('td', array(
				'class'=>'time' . ($hasEventStartOrEnd ? ' has-time' : ''),
			), $hasEventStartOrEnd ? CHtml::tag('span', array(), date('H:i', $time)) : '');
			foreach ($this->schedules as $key=>$schedules) {
				if (!isset($schedules[$stageKeys[$key]])) {
					if (!isset($stageKeys[$key - 1]) || !isset($this->schedules[$key - 1][$stageKeys[$key - 1]]) || $colSpans[$key - 1] == 1) {
						echo CHtml::tag('td');
					}
					continue;
				}
				$current = $schedules[$stageKeys[$key]]['schedule'];
				if ($current->end_time == $time + $this->timeSpan) {
					$stageKeys[$key]++;
					$colSpans[$key] = 1;
				}
				if ($current->start_time == $time) {
					if (isset($stageKeys[$key + 1]) && $this->schedules[$key + 1][0]['schedule']->stage == $current->stage
						&& (!isset($this->schedules[$key + 1][$stageKeys[$key + 1]])
						|| $this->schedules[$key + 1][$stageKeys[$key + 1]]['schedule']->start_time >= $current->end_time)
					) {
						$colSpans[$key] = 2;
					}
					$this->renderEventCell($schedules[$stageKeys[$key]], $colSpans[$key]);
				} elseif ($current->start_time > $time) {
					if (!isset($stageKeys[$key - 1]) || !isset($this->schedules[$key - 1][$stageKeys[$key - 1]]) || $colSpans[$key - 1] == 1) {
						echo CHtml::tag('td');
					}
				}
			}
			echo CHtml::openTag('td', array(
				'class'=>'time' . ($hasEventStartOrEnd ? ' has-time' : ''),
			));
			if ($hasEventStartOrEnd) {
				echo CHtml::tag('span', array(), date('H:i', $time));
			}
			echo CHtml::closeTag('td');
			echo CHtml::closeTag('tr');
		}
		echo CHtml::openTag('tr');
		echo CHtml::tag('td', array(
			'class'=>'time has-time',
		), CHtml::tag('span', array(), date('H:i', $time)));
		echo CHtml::tag('td', array(
			'colspan'=>count($colSpans),
		));
		echo CHtml::tag('td', array(
			'class'=>'time has-time',
		), CHtml::tag('span', array(), date('H:i', $time)));
		echo CHtml::closeTag('tr');
		echo CHtml::closeTag('tbody');
		
		echo CHtml::closeTag('table');
		echo CHtml::closeTag('div');
	}

	private function processSchedules() {
		$newSchedules = array();
		foreach ($this->schedules as $stage=>$schedules) {
			$temp = array();
			$count = count($schedules);
			for ($i = 0; $i < $count - 1; $i++) {
				for ($j = $i + 1; $j < $count; $j++) {
					//conflict
					if ($schedules[$i]['schedule']->end_time > $schedules[$j]['schedule']->start_time) {
						$temp[] = $schedules[$j];
						unset($schedules[$j]);
					} else {
						break;
					}
				}
				$i = $j - 1;
			}
			$newSchedules[] = array_values($schedules);
			if ($temp !== array()) {
				$newSchedules[] = $temp;
			}
		}
		$this->schedules = $newSchedules;
	}

	protected function renderEventCell($schedule, $colSpan = 1) {
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
				'event',
				'event-' . $schedule['schedule']->event,
				'round-' . $schedule['schedule']->round,
			)),
			'colspan'=>$colSpan,
			'rowspan'=>($schedule['schedule']->end_time - $schedule['schedule']->start_time) / $this->timeSpan,
		), implode('<br>', $text));
	}
}