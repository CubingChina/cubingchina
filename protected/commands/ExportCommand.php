<?php

class ExportCommand extends CConsoleCommand {
	private $translations = [];

	public function actionHeat() {
		$path = Yii::app()->basePath . '/messages/zh_cn/';
		$this->translations = array_merge(
			include $path . 'Schedule.php',
			include $path . 'common.php',
			include $path . 'Rounds.php',
			include $path . 'event.php'
		);
		$competition = Competition::model()->findByPk(440);
		$registrations = Registration::getRegistrations($competition);
		$mpdf = new mPDF();
		$mpdf->useAdobeCJK = true;
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont = true;
		$stylesheet = file_get_contents(Yii::app()->basePath . '/style.css');
		$mpdf->WriteHTML($stylesheet, 1);
		var_dump(ini_set('memory_limit', '2G'));
		foreach ($registrations as $registration) {
			$schedules = HeatScheduleUser::model()->findAllByAttributes([
				'user_id'=>$registration->user_id,
				'competition_id'=>$registration->competition_id,
			]);
			$schedules = CHtml::listData($schedules, 'id', 'schedule');
			usort($schedules, function($a, $b) {
				$temp = $a->day - $b->day;
				if ($temp == 0) {
					$temp = $a->start_time - $b->start_time;
				}
				if ($temp == 0) {
					$temp = $a->end_time - $b->end_time;
				}
				return $temp;
			});
			$temp = [];
			foreach ($schedules as $schedule) {
				$temp[$schedule->day][$schedule->stage][] = $schedule;
			}
			$mpdf->WriteHTML(sprintf('<h4>No.%d %s</h4>', $registration->number, $registration->user->getCompetitionName()), 2);
			foreach ($temp as $day=>$stages) {
				$mpdf->WriteHTML(sprintf('<h3>%s</h3>', date('Y-m-d', $competition->date + 86400 * ($day - 1))), 2);
				foreach ($stages as $stage=>$schedules) {
					$temp = Schedule::getStageText($stage);
					$mpdf->WriteHTML(sprintf('<h4>%s / %s</h4>', strtr($temp, $this->translations), $temp), 2);
					$table .= CHtml::tag('td', [], implode('<br>', [
						strtr($temp, $this->translations),
						$temp,
					]));
					$table = $this->buildTable($schedules);
					$mpdf->WriteHTML($table, 2);
				}
			}
			$mpdf->AddPage();
			var_dump($registration->number);
			// break;
		}
		$mpdf->output(Yii::app()->basePath . '/xxx.pdf', 'F');
	}

	private function buildTable($schedules) {
		$table = CHtml::openTag('table', []);
		$table .= CHtml::openTag('thead', []);
		$table .= CHtml::openTag('tr', []);
		$columns = [
			'Start Time',
			'End Time',
			'Event',
			'Group',
			'Round',
			'Format',
			'Cut Off',
			'Time Limit',
		];
		foreach ($columns as $column) {
			$table .= CHtml::tag('th', [], implode('<br>', [
				strtr($column, $this->translations),
				$column,
			]));
		}
		$table .= CHtml::closeTag('tr', []);
		$table .= CHtml::closeTag('thead', []);
		$table .= CHtml::openTag('tbody', []);
		foreach ($schedules as $schedule) {
			$table .= CHtml::openTag('tr', []);
			$table .= CHtml::tag('td', [], date('H:i', $schedule->start_time));
			$table .= CHtml::tag('td', [], date('H:i', $schedule->end_time));
			$temp = Events::getFullEventName($schedule->event);
			$table .= CHtml::tag('td', [], implode('<br>', [
				strtr($temp, $this->translations),
				$temp,
			]));
			$table .= CHtml::tag('td', [], $schedule->group);
			$temp = Rounds::getFullRoundName($schedule->round);
			$table .= CHtml::tag('td', [], implode('<br>', [
				strtr($temp, $this->translations),
				$temp,
			]));
			$temp = Formats::getFullFormatName($schedule->format);
			$table .= CHtml::tag('td', [], implode('<br>', [
				strtr($temp, $this->translations),
				$temp,
			]));
			$table .= CHtml::tag('td', [], $this->formatTime($schedule->cut_off));
			$table .= CHtml::tag('td', [], $this->formatTime($schedule->time_limit));
			$table .= CHtml::closeTag('tr', []);
		}
		$table .= CHtml::closeTag('tbody', []);
		$table .= CHtml::closeTag('table', []);
		return $table;
	}

	private function formatTime($second) {
		$second = intval($second);
		if ($second <= 0) {
			return '';
		}
		if ($second < 60) {
			return sprintf('%ds', $second);
		}
		$minute = floor($second / 60);
		$second = $second % 60;
		$params = array(
			'{minute}'=>$minute,
			'{second}'=>$second,
		);
		if ($second == 0) {
			if ($minute > 1) {
				return Yii::t('common', '{minute}mins', $params);
			} else {
				return Yii::t('common', '{minute}min', $params);
			}
		} else {
			if ($minute > 1) {
				return Yii::t('common', '{minute}mins {second}s', $params);
			} else {
				return Yii::t('common', '{minute}min {second}s', $params);
			}
		}
	}
}