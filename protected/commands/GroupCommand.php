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
						$groupTime = floor($groupTime / 5) * 5;
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

	public function actionUser($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition !== null && $this->confirm($competition->name_zh)) {
			$registrations = Registration::getRegistrations($competition);

		}
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
