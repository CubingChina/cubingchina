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
			foreach ($associatedEvents as $event=>$value) {
				$sheet->setCellValue($col . $row, $event);
				$col++;
				$sheet->setCellValue($col . $row, '开始时间');
				$col++;
				$sheet->setCellValue($col . $row, '结束时间');
				$col++;
			}
			$row++;
			foreach ($registrations as $registration) {
				$sheet->setCellValue('A' . $row, $registration->number)
					->setCellValue('B' . $row, $registration->user->name_zh ?: $registration->user->name)
					->setCellValue('C' . $row, $this->isStaff($registration) ? 1 : '');
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
						$sheet->setCellValue($col . $row, date('H:i', $userSchedule->schedule->start_time));
						$col++;
						$sheet->setCellValue($col . $row, date('H:i', $userSchedule->schedule->end_time));
						$col++;
					} else {
						$col++;
						$col++;
						$col++;
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
			foreach ($listableSchedules as $day=>$stages) {
				foreach ($stages as $stage=>$schedules) {
					foreach ($schedules as $schedule) {
						$schedule = $schedule['schedule'];
						if ($schedule->wcaRound === null || isset($grouped[$schedule->event])) {
							continue;
						}
						$groupNum = $this->getProposedGroup($schedule, $competitors);
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
						$group = $groupNum > 1 ? 'A' : '';
						for ($i = 0; $i < $groupNum; $i++) {
							$groupSchedule = new GroupSchedule();
							$groupSchedule->attributes = $schedule->attributes;
							$groupSchedule->start_time = $schedule->start_time + $i * $groupTime;
							$groupSchedule->end_time = $groupSchedule->start_time + $groupTime;
							$groupSchedule->group = $group;
							$groupSchedule->save();
							$group++;
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
			$eventRegistrations = [];
			foreach ($registrations as $registration) {
				foreach ($registration->getAcceptedEvents() as $registrationEvent) {
					$event = $registrationEvent->event;
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
							$userSchedule = new UserSchedule();
							$userSchedule->schedule = $schedule;
							$userSchedule->group_id = $schedule->id;
							$userSchedule->competition_id = $schedule->competition_id;
							$userSchedule->user_id = $registration->user_id;
							$userSchedule->save();
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
					$isStaffA = $this->isStaff($rA);
					$isStaffB = $this->isStaff($rB);
					if ($isStaffA && !$isStaffB) {
						return -1;
					}
					if ($isStaffB && !$isStaffA) {
						return 1;
					}
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
					$userSchedule = new UserSchedule();
					$userSchedule->schedule = $schedule;
					$userSchedule->group_id = $schedule->id;
					$userSchedule->competition_id = $schedule->competition_id;
					$userSchedule->user_id = $registration->user_id;
					$userSchedule->save();
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

	public function isStaff($registration) {
		$staffs = self::getStaffs($registration->competition_id);
		$user = $registration->user;
		if (!isset($staffs[$user->name_zh])) {
			return false;
		}
		$staff = $staffs[$user->name_zh];
		$isStaff = $staff['mobile'] == $user->mobile || $staff['passport'] == $user->passport_number || $staff['email'] == $user->email;
		return $isStaff;
	}

	public static function getStaffs($competitionId) {
		if (self::$_staffs === null) {
			self::$_staffs = [];
			if (is_file(BASE_PATH . '/data/staffs' . $competitionId . '.tsv')) {
				foreach (file(BASE_PATH . '/data/staffs' . $competitionId . '.tsv') as $line) {
					if (!trim($line)) {
						continue;
					}
					list($name, $mobile, $passport, $email) = explode("\t", $line);
					foreach (['name', 'mobile', 'passport', 'email'] as $var) {
						$$var = trim($$var);
					}
					self::$_staffs[$name] = compact('name', 'mobile', 'passport', 'email');
				}
			}
		}
		return self::$_staffs;
	}

	public function actionSolveConflict($id, $solve = 0) {
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
						if ($this->isConflict($userSchedules[$i]->schedule, $userSchedules[$j]->schedule)) {
							//@todo to be completed
							$conflict = sprintf('No.%d %d %s: [%s - %s]', $registration->number, $registration->user_id, $registration->user->getCompetitionName(), $userSchedules[$i]->schedule->event, $userSchedules[$j]->schedule->event);
							$conflicts[$userSchedules[$i]->schedule->event] = ($conflicts[$userSchedules[$i]->schedule->event] ?? 0) + 1;
							$conflicts[$userSchedules[$j]->schedule->event] = ($conflicts[$userSchedules[$j]->schedule->event] ?? 0) + 1;
							echo $conflict, PHP_EOL;
							if ($solve) {
								$events = [$userSchedules[$i]->schedule->event, $userSchedules[$j]->schedule->event];
								sort($events);
								if ($events === ['333oh', '555bf'] && $registration->hasRegistered('333fm')) {
									$toMoveSchedule = $userSchedules[$i]->schedule->event === '333oh' ? $userSchedules[$i] : $userSchedules[$j];
									if ($toMoveSchedule->schedule->group != 'D') {
										$groupD = GroupSchedule::model()->findByAttributes([
											'competition_id'=>$competition->id,
											'day'=>$toMoveSchedule->schedule->day,
											'event'=>$toMoveSchedule->schedule->event,
											'group'=>'D',
										]);
										$toMoveSchedule->schedule = $groupD;
										$toMoveSchedule->group_id = $groupD->id;
										$toMoveSchedule->save();
										$this->moveGroup($groupD, $userSchedules[$i]->schedule->event === '333oh' ? $userSchedules[$i]->schedule : $userSchedules[$j]->schedule, 1);
									}
									continue;
								}
								// try to move userSchedules[$j]->schedule first
								if (!$this->tryToMove($userSchedules, $i, $j)) {
									$this->tryToMove($userSchedules, $j, $i);
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
			foreach ($groupSchedules as $groupSchedule) {
				$distribution[$groupSchedule->group ?: 'A'][$groupSchedule->event] = count($groupSchedule->users);
				if ($groupSchedule->group > $maxGroup) {
					$maxGroup = $groupSchedule->group;
				}
			}
			echo "Group\t";
			echo implode("\t", array_keys($competition->associatedEvents));
			echo "\n";
			for ($group = 'A'; $group <= $maxGroup; $group++) {
				echo "{$group}\t";
				foreach ($competition->associatedEvents as $event=>$value) {
					echo $distribution[$group][$event] ?? '';
					echo "\t";
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
			if ($this->moveGroup($fromSchedule, $groupScheduleB, $num)) {
				echo "{$count} moved\n";
			} else {
				echo "less thant {$count} moves\n";
			}
		}
	}

	private function moveGroup($fromSchedule, $toSchedule, $num) {
		$userSchedules = UserSchedule::model()->findAllByAttributes([
			'group_id'=>$fromSchedule->id,
		]);
		$count = 0;
		foreach ($userSchedules as $userSchedule) {
			//fetch all
			$schedules = UserSchedule::model()->findAllByAttributes([
				'competition_id'=>$fromSchedule->competition_id,
				'user_id'=>$userSchedule->user_id,
			], [
				'condition'=>'id != ' . $userSchedule->id,
			]);
			$conflict = false;
			foreach ($schedules as $schedule) {
				if ($this->isConflict($toSchedule, $schedule->schedule)) {
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

	private function tryToMove($userSchedules, $i, $j) {
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
			if ($this->isConflict($userScheduleB->schedule, $schedule)) {
				continue;
			}
			if (!in_array($userScheduleB->schedule->event, $this->_fixedScheduleEvents)) {
				$conflict = false;
				// check all user schedules
				foreach ($userSchedules as $userSchedule) {
					if ($this->isConflict($userSchedule->schedule, $schedule)) {
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
			$this->moveGroup($schedule, $oldSchedule, 1);
			return true;
		}
		return false;
	}

	private function dumpSchedule($schedule) {
		echo implode("\n", [
			"Event: {$schedule->event}",
			"Group: {$schedule->group}",
			"Day: {$schedule->day}",
			"Start Time: " . date('H:i', $schedule->start_time),
			"End Time: " . date('H:i', $schedule->end_time),
			"",
		]);
	}

	private function isConflict($scheduleA, $scheduleB) {
		if ($scheduleA->id == $scheduleB->id) {
			return false;
		}
		if ($scheduleA->day != $scheduleB->day) {
			return false;
		}
		if ($scheduleA->end_time == $scheduleB->start_time) {
			return false;
		}
		if ($scheduleA->start_time == $scheduleB->end_time) {
			return false;
		}
		return $scheduleA->start_time >= $scheduleB->start_time && $scheduleA->start_time <= $scheduleB->end_time
			|| $scheduleA->end_time >= $scheduleB->start_time && $scheduleA->end_time <= $scheduleB->end_time
			|| $scheduleA->end_time >= $scheduleB->end_time && $scheduleA->start_time <= $scheduleB->start_time;
	}

	private function makeRoundMessage($schedule, $competitors, $proposedGroup) {
		return sprintf("[%s - %s] Competitors: %d Time: %d (%d - %d):",
			$schedule->wcaEvent->name, $schedule->wcaRound->name, $competitors[$schedule->event] ?? '?',
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
		$competitors = $competitors[$schedule->event] ?? 1;
		return [ceil($competitors / $stations), floor($totalTime / $this->_proposedTime[$schedule->event])];
	}

	private function getStations($stage) {
		if (!isset($this->_stations[$stage])) {
			while (($this->_stations[$stage] = $this->prompt("Stations for stage: {$stage}?")) <= 0) {

			}
		}
		return $this->_stations[$stage];
	}
}
