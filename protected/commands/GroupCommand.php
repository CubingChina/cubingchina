<?php

class GroupCommand extends CConsoleCommand {

	private $_stations = [];
	private $_proposedTime = [
		'222'=>10,
		'333'=>10,
		'pyram'=>10,
		'skewb'=>10,
		'444'=>15,
		'666'=>15,
		'333oh'=>10,
		'333ft'=>15,
		'333bf'=>15,
		'clock'=>15,
		'sq1'=>15,
		'555'=>15,
		'777'=>20,
		'minx'=>20,
	];
	private $_specialEvents = ['333fm', '333mbf'];
	private $_fixedScheduleEvents = ['333fm', '444bf', '555bf', '333mbf'];
	private static $_staffs;
	public $translations = [];

	public function actionExportList($id, $path = null) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			if ($path == null) {
				$path = Yii::app()->basePath . '/' . $competition->name . ' groups.xlsx';
			}
			$registrations = Registration::getRegistrations($competition);
			$associatedEvents = $competition->getAssociatedEvents();
			$excel = new PHPExcel();
			$excel->getProperties()
				->setCreator(Yii::app()->params->author)
				->setLastModifiedBy(Yii::app()->params->author)
				->setTitle($competition->wca_competition_id ?: $competition->name)
				->setSubject($competition->name);
			$excel->removeSheetByIndex(0);
			$sheet = $excel->createSheet();
			$sheet->setTitle('Groups');
			$sheet->setCellValue('A1', 'No.')
				->setCellValue('B1', 'Name')
				->setCellValue('C1', 'Staff');
			$col = 'D';
			$row = 1;
			$staffs = include APP_PATH . '/protected/data/staff.php';
			foreach ($associatedEvents as $event=>$value) {
				$sheet->setCellValue($col . $row, $event);
				$col++;
				// $sheet->setCellValue($col . $row, '开始时间');
				// $col++;
				// $sheet->setCellValue($col . $row, '结束时间');
				// $col++;
			}
			$row++;
			foreach ($registrations as $registration) {
				if (!$this->isStaff($registration, $staffs)) {
					continue;
				}
				$sheet->setCellValue('A' . $row, $registration->number)
					->setCellValue('B' . $row, $registration->user->name_zh ?: $registration->user->name)
					->setCellValue('C' . $row, $this->isStaff($registration, $staffs) ? 1 : '');
				$col = 'D';
				foreach ($associatedEvents as $event=>$value) {
					$userSchedule = UserSchedule::model()->with('schedule')->findByAttributes([
						'user_id'=>$registration->user_id,
						'competition_id'=>$competition->id,
					], [
						'condition'=>'event=:event',
						'params'=>[
							':event'=>"$event",
						],
					]);
					if ($userSchedule != null) {
						$sheet->setCellValue($col . $row, $userSchedule->schedule->group);
						$col++;
						// $sheet->setCellValue($col . $row, date('H:i', $userSchedule->schedule->start_time));
						// $col++;
						// $sheet->setCellValue($col . $row, date('H:i', $userSchedule->schedule->end_time));
						// $col++;
					} else {
						$col++;
						// $col++;
						// $col++;
					}
				}
				$row++;
			}
			$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
			$writer->save($path);
		}
	}

	public function actionClear($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			GroupSchedule::model()->deleteAllByAttributes([
				'competition_id'=>$competition->id,
			]);
			UserSchedule::model()->deleteAllByAttributes([
				'competition_id'=>$competition->id,
			]);
		}
	}

	public function actionMake($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			$scheduleExists = GroupSchedule::model()->countByAttributes([
				'competition_id'=>$competition->id,
			]) > 0;
			if ($scheduleExists && !$this->confirm('regenerate?')) {
				return;
			}
			if ($scheduleExists) {
				GroupSchedule::model()->deleteAllByAttributes([
					'competition_id'=>$competition->id,
				]);
				UserSchedule::model()->deleteAllByAttributes([
					'competition_id'=>$competition->id,
				]);
			}
			$registrations = Registration::getRegistrations($competition);
			$competitors = [];
			foreach ($registrations as $registration) {
				foreach ($registration->getAcceptedEvents() as $registrationEvent) {
					$event = $registrationEvent->event;
					$competitors[$event] = ($competitors[$event] ?? 0) + 1;
				}
			}
			$grouped = [];
			$listableSchedules = $competition->getListableSchedules();
			$lastGroup = [];
			foreach ($listableSchedules as $day=>$stages) {
				foreach ($stages as $stage=>$schedules) {
					foreach ($schedules as $schedule) {
						$schedule = $schedule['schedule'];
						$firstRound = $competition->getFirstRound($schedule->event);
						if ($schedule->wcaRound === null || $schedule->round != $firstRound->round) {
							continue;
						}
						$groupNum = $this->getProposedGroup($schedule, $competitors[$schedule->event]);
						if (is_array($groupNum)) {
							$groupNum = $this->prompt(
								$this->makeRoundMessage($schedule, $competitors, $groupNum),
								min($groupNum)
							);
						}
						if (!in_array($schedule->event, $this->_specialEvents)) {
							$grouped[$schedule->event] = 1;
						}
						$totalTime = $schedule->end_time - $schedule->start_time;
						$groupTime = floor($totalTime / $groupNum);
						$groupTime = floor($groupTime / 300) * 300;
						$group = $groupNum > 1 ? ($lastGroup[$schedule->event] ?? 'A') : '';
						for ($i = 0; $i < $groupNum; $i++) {
							$groupSchedule = new GroupSchedule();
							$groupSchedule->attributes = $schedule->attributes;
							$groupSchedule->start_time = $schedule->start_time + $i * $groupTime;
							$groupSchedule->end_time = $groupSchedule->start_time + $groupTime;
							$groupSchedule->group = $group;
							$groupSchedule->save();
							$group++;
							$lastGroup[$schedule->event] = $group;
						}
					}
				}
			}
		}
	}

	public function actionCompetitor($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			$scheduleExists = UserSchedule::model()->countByAttributes([
				'competition_id'=>$competition->id,
			]) > 0;
			if ($scheduleExists && !$this->confirm('regenerate?')) {
				return;
			}
			if ($scheduleExists) {
				UserSchedule::model()->deleteAllByAttributes([
					'competition_id'=>$competition->id,
				]);
			}
			$registrations = Registration::getRegistrations($competition);
			$staffs = include APP_PATH . '/protected/data/staff.php';
			$eventRegistrations = [];
			foreach ($registrations as $registration) {
				$isStaff = $this->isStaff($registration, $staffs);
				$registration->isStaff = $isStaff;
				foreach ($registration->getAcceptedEvents() as $registrationEvent) {
					$event = $registrationEvent->event;
					// ignore staff for these events
					if ($isStaff && in_array($event, ['222', '333', 'pyram'])) {
						continue;
					}
					$eventRegistrations[$event][$registration->user_id] = $registration;
				}
			}
			$groupSchedules = GroupSchedule::model()->findAllByAttributes([
				'competition_id'=>$competition->id,
			], [
				'order'=>'id ASC',
			]);
			$temp = [];
			foreach ($groupSchedules as $heatSchedule) {
				$temp[$heatSchedule->event][] = $heatSchedule;
			}
			$groupSchedules = $temp;
			foreach ($eventRegistrations as $event=>$registrations) {
				if (in_array("$event", $this->_specialEvents)) {
					foreach ($registrations as $registration) {
						foreach ($groupSchedules[$event] as $schedule) {
							$this->addUserSchedule($schedule, $registration);
						}
					}
					continue;
				}
				//cache wca id
				$wcaidRegistrations = [];
				foreach ($registrations as $registration) {
					if ($registration->user->wcaid) {
						$wcaidRegistrations[$registration->user->wcaid] = $registration;
					}
				}
				switch ("$event") {
					case '333bf':
					case '444bf':
					case '555bf':
					case '333mbf':
						$modelName = 'RanksSingle';
						break;
					default:
						$modelName = 'RanksAverage';
						break;
				}
				//fetch result
				$results = $modelName::model()->findAllByAttributes(array(
					'eventId'=>"$event",
					'personId'=>array_keys($wcaidRegistrations),
				));
				array_walk($registrations, function($registration) {
					$registration->best = 0;
				});
				foreach ($results as $result) {
					$wcaidRegistrations[$result->personId]->best = $result->best;
				}
				//sort by best desc
				uasort($registrations, function($rA, $rB) {
					if ($rA->best > 0 && $rB->best > 0) {
						$temp = $rA->best - $rB->best;
					} elseif ($rA->best > 0) {
						$temp = -1;
					} elseif ($rB->best > 0) {
						$temp = 1;
					} else {
						$temp = 0;
					}
					return -$temp;
				});
				$count = count($registrations);
				$i = 0;
				$groupCount = 0;
				$groupKey = 0;
				$groupNum = $count / count($groupSchedules[$event]);
				foreach ($registrations as $registration) {
					$schedule = $groupSchedules[$event][$groupKey];
					$this->addUserSchedule($schedule, $registration);
					$i++;
					$groupCount++;
					if ($groupCount >= $groupNum) {
						$groupCount = 0;
						$groupKey++;
					}
				}
			}
		}
	}

	public function actionRound($id, $event, $round) {
		$competition = Competition::model()->findByPk($id);
		$attributes = [
			'competition_id'=>$competition->id,
			'event'=>$event,
			'round'=>$round,
		];
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			$groups = GroupSchedule::model()->findAllByAttributes($attributes);
			if ($groups !== [] && !$this->confirm('regenerate?')) {
				return;
			}
			if ($groups !== []) {
				$groupIds = CHtml::listData($groups, 'id', 'id');
				UserSchedule::model()->deleteAllByAttributes([
					'group_id'=>$groupIds,
				]);
				GroupSchedule::model()->deleteByPk($groupIds);
			}
			$round = LiveEventRound::model()->findByAttributes($attributes);
			if ($round === null) {
				exit('Can not find such round in live result!');
			}
			$results = array_reverse($round->results);
			$schedule = Schedule::model()->findByAttributes($attributes);
			if ($round === null) {
				exit('Can not find such round in schedule!');
			}
			if ($results === []) {
				exit('Please update competitors first!');
			}
 			$competitors = count($results);
			$groupNum = $this->getProposedGroup($schedule, $competitors);
			if (is_array($groupNum)) {
				$groupNum = $this->prompt(
					$this->makeRoundMessage($schedule, $competitors, $groupNum),
					min($groupNum)
				);
			}
			$totalTime = $schedule->end_time - $schedule->start_time;
			$groupTime = floor($totalTime / $groupNum);
			$groupTime = floor($groupTime / 300) * 300;
			$group = $groupNum > 1 ? 'A' : '';
			$results = array_chunk($results, ceil($competitors / $groupNum));
			$groupSchedules = [];
			$userSchedules = [];
			$anyGroup = GroupSchedule::model()->findByAttributes([
				'competition_id'=>$competition->id,
			]);
			$day = date('Y-m-d ', $anyGroup->start_time);
			for ($i = 0; $i < $groupNum; $i++) {
				$groupSchedule = new GroupSchedule();
				$groupSchedule->attributes = $schedule->attributes;
				$groupSchedule->start_time = strtotime($day . date('H:i', $schedule->start_time + $i * $groupTime));
				$groupSchedule->end_time = $groupSchedule->start_time + $groupTime;
				$groupSchedule->group = $group;
				$groupSchedule->save();
				$groupSchedules[] = $groupSchedule;
				foreach ($results[$i] as $result) {
					$userSchedules[] = $this->addUserSchedule($groupSchedule, $result);
				}
				$group++;
			}
			foreach ($userSchedules as $userSchedule) {
				$groupSchedule = $userSchedule->schedule;
				$allUserSchedules = UserSchedule::model()->findAllByAttributes([
					'user_id'=>$userSchedule->user_id,
					'competition_id'=>$competition->id,
				]);
				if ($this->hasConflict($groupSchedule, $allUserSchedules)) {
					foreach ($groupSchedules as $schedule) {
						if (!$this->hasConflict($schedule, $allUserSchedules)) {
							$userSchedule->schedule = $schedule;
							$userSchedule->group_id = $schedule->id;
							$userSchedule->save();
							$this->moveGroup($schedule, $groupSchedule, 1);
							break;
						}
					}
				}
			}
		}
	}

	public function actionSubmission($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			$scheduleExists = GroupSchedule::model()->countByAttributes([
				'competition_id'=>$competition->id,
				'event'=>'submission',
			]) > 0;
			if ($scheduleExists && !$this->confirm('regenerate?')) {
				return;
			}
			if ($scheduleExists) {
				$groups = GroupSchedule::model()->findAllByAttributes([
					'competition_id'=>$competition->id,
					'event'=>'submission',
				]);
				foreach ($groups as $group) {
					UserSchedule::model()->deleteAllByAttributes([
						'group_id'=>$group->id,
					]);
					$group->delete();
				}
			}
			$registrations = Registration::getRegistrations($competition);
			$event = 'submission';
			foreach ($competition->schedule as $schedule) {
				if ($schedule->event !== $event) {
					continue;
				}
				$groupSchedule = new GroupSchedule();
				$groupSchedule->attributes = $schedule->attributes;
				$groupSchedule->save(false);
				foreach ($registrations as $registration) {
					if ($registration->hasRegistered('333mbf')) {
						$this->addUserSchedule($groupSchedule, $registration);
					}
				}
			}
		}
	}

	private function addUserSchedule($schedule, $registration) {
		$userSchedule = new UserSchedule();
		$userSchedule->schedule = $schedule;
		$userSchedule->group_id = $schedule->id;
		$userSchedule->competition_id = $schedule->competition_id;
		$userSchedule->user_id = $registration->user_id;
		$userSchedule->save();
		return $userSchedule;
	}

	public function actionStaffs($id) {
		$competition = Competition::model()->findByPk($id);
		$registrations = Registration::getRegistrations($competition);
		$staffs = [];
		foreach ($registrations as $registration) {
			if ($this->isStaff($registration)) {
				$staffs[] = $registration->user_id;
			}
		}
		$staffs = array_unique($staffs);
		$userSchedules = UserSchedule::model()->with('schedule')->findAllByAttributes([
			'user_id'=>$staffs,
			'competition_id'=>$competition->id,
		], [
			'condition'=>'schedule.group!="A"',
		]);
		foreach ($userSchedules as $userSchedule) {
			var_dump($userSchedule->user_id, $userSchedule->user->name_zh, $userSchedule->schedule->event);
		}
	}

	public function isStaff($registration, $staffs = []) {
		if ($registration->user->isWCADelegate()) {
			return true;
		}
		$name = $registration->user->name_zh ?: $registration->user->name;
		if (in_array($name, $staffs)) {
			return true;
		}
		if (isset($staffs[$name]) && $staffs[$name] == $registration->number) {
			return true;
		}
		return false;
	}

	public function actionSolveConflict($id, $solve = 0, $strict = 0) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			$registrations = Registration::getRegistrations($competition);
			$conflicts = [];
			foreach ($registrations as $registration) {
				$userSchedules = UserSchedule::model()->findAllByAttributes([
					'user_id'=>$registration->user_id,
					'competition_id'=>$competition->id,
				]);
				$count = count($userSchedules);
				for ($i = 0; $i < $count - 1; $i++) {
					for ($j = $i + 1; $j < $count; $j++) {
						if ($this->isConflict($userSchedules[$i]->schedule, $userSchedules[$j]->schedule, $strict)) {
							//@todo to be completed
							$conflict = sprintf('No.%d %d %s: [%s - %s]', $registration->number, $registration->user_id, $registration->user->getCompetitionName(), $userSchedules[$i]->schedule->event, $userSchedules[$j]->schedule->event);
							$conflicts[$userSchedules[$i]->schedule->event] = ($conflicts[$userSchedules[$i]->schedule->event] ?? 0) + 1;
							$conflicts[$userSchedules[$j]->schedule->event] = ($conflicts[$userSchedules[$j]->schedule->event] ?? 0) + 1;
							echo $conflict, PHP_EOL;
							if ($solve) {
								$events = [$userSchedules[$i]->schedule->event, $userSchedules[$j]->schedule->event];
								sort($events);
								// try to move userSchedules[$j]->schedule first
								if (!$this->tryToMove($userSchedules, $i, $j, $strict)) {
									$this->tryToMove($userSchedules, $j, $i, $strict);
								}
							}
						}
					}
				}
				// break;
			}
			asort($conflicts);
			var_dump($conflicts, array_sum($conflicts) / 2);
		}
	}

	public function actionDistribution($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null) {
			$groupSchedules = GroupSchedule::model()->with('users')->findAllByAttributes([
				'competition_id'=>$competition->id,
			]);
			$distribution = [];
			$maxGroup = 'A';
			$eventRounds = [];
			foreach ($groupSchedules as $groupSchedule) {
				$eventRounds[$groupSchedule->event][$groupSchedule->round] = $groupSchedule->round;
				$distribution[$groupSchedule->group ?: 'A'][$groupSchedule->event][$groupSchedule->round] = count($groupSchedule->users);
				if ($groupSchedule->group > $maxGroup) {
					$maxGroup = $groupSchedule->group;
				}
			}
			echo "Group\t";
			foreach ($eventRounds as $event=>$rounds) {
				foreach ($rounds as $round) {
					echo "${event}-${round}\t";
				}
			}
			echo "\n";
			for ($group = 'A'; $group <= $maxGroup; $group++) {
				echo "{$group}\t";
				foreach ($eventRounds as $event=>$rounds) {
					foreach ($rounds as $round) {
						echo $distribution[$group][$event][$round] ?? '';
						echo "\t";
					}
				}
				echo "\n";
			}
		}
	}

	public function actionMoveGroup($id, $event, $from, $to, $num) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm(sprintf('%s %s: %s - %s', $competition->name_zh, $event, $from, $to))) {
			$fromSchedule = GroupSchedule::model()->findByAttributes([
				'competition_id'=>$competition->id,
				'event'=>$event,
				'group'=>$from,
			]);
			if ($fromSchedule == null) {
				return;
			}
			$toSchedule = GroupSchedule::model()->findByAttributes([
				'competition_id'=>$competition->id,
				'event'=>$event,
				'group'=>$to,
			]);
			if ($toSchedule == null) {
				return;
			}
			if ($this->moveGroup($fromSchedule, $toSchedule, $num, true)) {
				echo "{$num} moved\n";
			} else {
				echo "less thant {$num} moves\n";
			}
		}
	}

	public function actionPDF($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null) {
			$app = Yii::app();
			$app->language = 'en';
			$path = $app->basePath . '/messages/zh_cn/';
			$this->translations = array_merge(
				include $path . 'Schedule.php',
				include $path . 'common.php',
				include $path . 'RoundTypes.php',
				include $path . 'event.php'
			);
			$registrations = Registration::getRegistrations($competition);
			$mpdf = new \Mpdf\Mpdf();
			$mpdf->useAdobeCJK = true;
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont = true;
			$stylesheet = file_get_contents($app->basePath . '/data/groups.css');
			$mpdf->WriteHTML($stylesheet, 1);
			ini_set('memory_limit', '2G');
			foreach ($registrations as $registration) {
				$mpdf->AddPage(
				);
				$mpdf->SetHTMLHeader(sprintf('<img src="%s" width="40%%" align="right" style="float:right" />', APP_PATH . '/protected/data/logo.png'), '', true);
				$schedules = UserSchedule::model()->with('schedule')->findAllByAttributes([
					'user_id'=>$registration->user_id,
					'competition_id'=>$registration->competition_id,
				]);
				$schedules = CHtml::listData($schedules, 'id', 'schedule');
				usort($schedules, function($a, $b) {
					$temp = $a->day - $b->day;
					if ($temp == 0) {
						$temp = Schedule::getStagetWeight($a->stage) - Schedule::getStagetWeight($b->stage);
					}
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
				$name = $registration->user->country_id <= 4 ? $registration->user->name_zh : $registration->user->name;
				$name = $name ? $name : $registration->user->name;
				$mpdf->WriteHTML(sprintf('<h2>No.%d %s</h2>', $registration->number, $name, 2));
				foreach ($temp as $day=>$stages) {
					$mpdf->WriteHTML(sprintf('<h3>%s</h3>', date('Y-m-d', $competition->date + 86400 * ($day - 1))), 2);
					foreach ($stages as $stage=>$schedules) {
						$temp = Schedule::getStageText($stage);
						$mpdf->WriteHTML(sprintf('<h4>%s / %s</h4>', strtr($temp, $this->translations), $temp), 2);
						// $table .= CHtml::tag('td', [], implode('<br>', [
						// 	strtr($temp, $this->translations),
						// 	$temp,
						// ]));
						$table = $this->buildTable($schedules);
						$mpdf->WriteHTML($table, 2);
					}
				}
				$mpdf->WriteHTML('<p>选手应在指定时间到达指定赛场参加比赛，错过分组对应的时间将无法进行检录，从而丧失比赛机会。</p>', 2);
				var_dump($registration->number);
				if ($registration->number > 13) {
					// break;
				}
			}
			$mpdf->output(Yii::app()->basePath . '/选手分组信息表.pdf', 'F');
		}
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
			'Cutoff',
			'Time Limit',
		];
		foreach ($columns as $index=>$column) {
			$attributes = [];
			if ($index == count($columns) - 1) {
				$attributes = ['class'=>'bdr'];
			}
			$table .= CHtml::tag('th', $attributes, implode('<br>', [
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
			$temp = RoundTypes::getFullRoundName($schedule->round);
			$table .= CHtml::tag('td', [], implode('<br>', [
				strtr($temp, $this->translations),
				$temp,
			]));
			$temp = Formats::getFullFormatName($schedule->format);
			$table .= CHtml::tag('td', [], implode('<br>', [
				strtr($temp, $this->translations),
				$temp,
			]));
			$table .= CHtml::tag('td', [], $this->formatTime($schedule->cut_off, $schedule->event));
			$table .= CHtml::tag('td', ['class'=>'bdr'], $this->formatTime($schedule->time_limit, $schedule->event));
			$table .= CHtml::closeTag('tr', []);
		}
		$table .= CHtml::closeTag('tbody', []);
		$table .= CHtml::closeTag('table', []);
		return $table;
	}

	private function formatTime($second, $event = '') {
		if ($event === '333mbf') {
			return '';
		}
		$second = intval($second);
		if ($second <= 0) {
			return '';
		}
		// if ($second < 60) {
		// 	return sprintf('%ds', $second);
		// }
		$minute = floor($second / 60);
		$second = $second % 60;
		return sprintf("%d:%02d", $minute, $second);
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

	private function moveGroup($fromSchedule, $toSchedule, $num, $strict = false, $byUserId = []) {
		$userSchedules = UserSchedule::model()->findAllByAttributes([
			'group_id'=>$fromSchedule->id,
		]);
		$count = 0;
		foreach ($userSchedules as $userSchedule) {
			if (isset($byUserId[$userSchedule->user_id])) {
				continue;
			}
			//fetch all
			$schedules = UserSchedule::model()->findAllByAttributes([
				'competition_id'=>$fromSchedule->competition_id,
				'user_id'=>$userSchedule->user_id,
			], [
				'condition'=>'id != ' . $userSchedule->id,
			]);
			$conflict = false;
			foreach ($schedules as $schedule) {
				if ($this->isConflict($toSchedule, $schedule->schedule, $strict)) {
					$conflict = true;
					break;
				}
			}
			if (!$conflict) {
				echo sprintf("Move 1 competitor from %s to group %s.\n", $fromSchedule->group, $toSchedule->group);
				$userSchedule->group_id = $toSchedule->id;
				$userSchedule->save();
				$count++;
			}
			if ($count == $num) {
				break;
			}
		}
		return $count == $num;
	}

	private function tryToMove($userSchedules, $i, $j, $strict = false) {
		$userScheduleA = $userSchedules[$i];
		$userScheduleB = $userSchedules[$j];
		$attributes = [
			'competition_id'=>$userScheduleA->schedule->competition_id,
			'day'=>$userScheduleA->schedule->day,
			'event'=>$userScheduleA->schedule->event,
		];
		$groupSchedules = GroupSchedule::model()->findAllByAttributes($attributes);
		foreach ($groupSchedules as $schedule) {
			// ignore itself
			if ($schedule->id == $userScheduleA->group_id) {
				continue;
			}
			// ignore any conflict groups
			if ($this->isConflict($userScheduleB->schedule, $schedule, $strict)) {
				continue;
			}
			if (!in_array($userScheduleB->schedule->event, $this->_fixedScheduleEvents)) {
				$conflict = false;
				// check all user schedules
				foreach ($userSchedules as $userSchedule) {
					if ($this->isConflict($userSchedule->schedule, $schedule, $strict)) {
						$conflict = true;
						break;
					}
				}
				if ($conflict) {
					continue;
				}
			}
			echo sprintf("Move %s to group %s.\n", $userScheduleA->schedule->event, $schedule->group);
			$oldSchedule = $userScheduleA->schedule;
			$userScheduleA->schedule = $schedule;
			$userScheduleA->group_id = $schedule->id;
			$userScheduleA->save();
			// move another one back
			$this->moveGroup($schedule, $oldSchedule, 1, $strict);
			return true;
		}
		return false;
	}

	private function hasConflict($groupSchedule, $userSchedules, $strict = false) {
		foreach ($userSchedules as $userSchedule) {
			if ($userSchedule->schedule === null) {
				var_dump($userSchedule->attributes);
			}
			if ($this->isConflict($groupSchedule, $userSchedule->schedule, $strict)) {
				return true;
			}
		}
		return false;
	}

	private function isConflict($scheduleA, $scheduleB, $strict = false) {
		if ($scheduleA->event === 'submission' || $scheduleB->event === 'submission') {
			return false;
		}
		if ($scheduleA->id == $scheduleB->id) {
			return false;
		}
		if ($scheduleA->day != $scheduleB->day) {
			return false;
		}
		if ($strict) {
			return !(
				$scheduleA->end_time < $scheduleB->start_time ||
				$scheduleB->end_time < $scheduleA->start_time
			);
		}
		if ($scheduleA->start_time == $scheduleB->end_time) {
			return false;
		}
		if ($scheduleA->end_time == $scheduleB->start_time) {
			return false;
		}
		return $scheduleA->start_time >= $scheduleB->start_time && $scheduleA->start_time <= $scheduleB->end_time
			|| $scheduleA->end_time >= $scheduleB->start_time && $scheduleA->end_time <= $scheduleB->end_time
			|| $scheduleA->end_time >= $scheduleB->end_time && $scheduleA->start_time <= $scheduleB->start_time;
	}

	private function makeRoundMessage($schedule, $competitors, $proposedGroup) {
		return sprintf("[%s - %s] Competitors: %d Time: %d (%d - %d):",
			Events::getFullEventName($schedule->event), RoundTypes::getFullRoundName($schedule->round), $competitors,
			($schedule->end_time - $schedule->start_time) / 60,
			$proposedGroup[0], $proposedGroup[1]
		);
	}

	private function getProposedGroup($schedule, $competitors) {
		if (in_array($schedule->event, $this->_fixedScheduleEvents)) {
			return 1;
		}
		$stations = $this->getStations($schedule->stage);
		$totalTime = ($schedule->end_time - $schedule->start_time) / 60;
		return [ceil($competitors / $stations), floor($totalTime / ($this->_proposedTime[$schedule->event] ?? 10))];
	}

	private function getStations($stage) {
		if (!isset($this->_stations[$stage])) {
			while (($this->_stations[$stage] = $this->prompt("Stations for stage: {$stage}?")) <= 0) {

			}
		}
		return $this->_stations[$stage];
	}
}
