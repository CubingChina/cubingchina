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
				->setCellValue('B1', 'Name');
			$col = 'C';
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
					->setCellValue('B' . $row, $registration->user->name_zh ?: $registration->user->name);
				$col = 'C';
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

	public function actionSchedule($id) {
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
				foreach ($registration->events as $event) {
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

	public function actionAutoUser($id) {
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
				foreach ($registration->events as $event) {
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
					if ($groupCount > $groupNum) {
						$groupCount = 0;
						$groupKey++;
					}
				}
			}
		}
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
				for ($i = 0; $i < $count; $i++) {
					$scheduleA = $userSchedules[$i]->schedule;
					if (in_array($scheduleA->event, $this->_specialEvents)) {
						// continue;
					}
					for ($j = 0; $j < $count; $j++) {
						if ($i == $j) {
							continue;
						}
						$scheduleB = $userSchedules[$j]->schedule;
						if (in_array($scheduleB->event, ["333fm", "444bf", "555bf", "333mbf"])) {
							// continue;
						}
						if (in_array($scheduleA->event, ["444bf", "555bf", "333mbf"])) {
							// continue;
						}
						if ($scheduleA->day != $scheduleB->day) {
							continue;
						}
						if ($this->isConflict($scheduleA, $scheduleB)) {
							//@todo to be completed
							$conflict = sprintf('No.%d %d %s: [%s - %s]', $registration->number, $registration->user_id, $registration->user->getCompetitionName(), $scheduleA->event, $scheduleB->event);
							$conflicts[$scheduleA->event] = ($conflicts[$scheduleA->event] ?? 0) + 1;
							$conflicts[$scheduleB->event] = ($conflicts[$scheduleB->event] ?? 0) + 1;
							echo $conflict, PHP_EOL;
							if ($solve) {
								$attributes = [
									'competition_id'=>$competition->id,
									'day'=>$scheduleA->day,
									'event'=>$scheduleB->event,
								];
								$groupSchedules = GroupSchedule::model()->findAllByAttributes($attributes);
								foreach ($groupSchedules as $schedule) {
									if (!$this->isConflict($scheduleA, $schedule)) {
										echo sprintf("Move %s to group %s.\n", $scheduleB->event, $schedule->group);
										$userSchedules[$j]->group_id = $schedule->id;
										$userSchedules[$j]->save();
										break;
									}
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

	public function actionMoveGroup($id, $event, $from, $to, $num) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm(sprintf('%s %s: %s - %s', $competition->name_zh, $event, $from, $to))) {
			$groupScheduleA = GroupSchedule::model()->findByAttributes([
				'competition_id'=>$competition->id,
				'event'=>$event,
				'group'=>$from,
			]);
			if ($groupScheduleA == null) {
				return;
			}
			$groupScheduleB = GroupSchedule::model()->findByAttributes([
				'competition_id'=>$competition->id,
				'event'=>$event,
				'group'=>$to,
			]);
			if ($groupScheduleB == null) {
				return;
			}
			$userSchedules = UserSchedule::model()->findAllByAttributes([
				'group_id'=>$groupScheduleA->id,
			]);
			$count = 0;
			foreach ($userSchedules as $userSchedule) {
				//fetch all
				$schedules = UserSchedule::model()->findAllByAttributes([
					'competition_id'=>$competition->id,
					'user_id'=>$userSchedule->user_id,
				], [
					'condition'=>'id != ' . $userSchedule->id,
				]);
				$conflict = false;
				foreach ($schedules as $schedule) {
					if ($this->isConflict($groupScheduleB, $schedule->schedule)) {
						$conflict = true;
						break;
					}
				}
				if (!$conflict) {
					$userSchedule->group_id = $groupScheduleB->id;
					$userSchedule->save();
					$count++;
				}
				if ($count == $num) {
					break;
				}
			}
			echo $count, " moved\n";
		}
	}

	private function isConflict($scheduleA, $scheduleB) {
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
		if (in_array($schedule->event, ["333fm", "444bf", "555bf", "333mbf"])) {
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
