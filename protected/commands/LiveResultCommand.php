<?php

/**
 * Command to populate live_result table with WCA data for testing H2H functionality
 *
 * Usage: php protected/yiic LiveResult populate --competitionId=123 --event=333 --round=f
 */
class LiveResultCommand extends CConsoleCommand {

	/**
	 * Populate live_result with WCA data for a specific round
	 *
	 * @param int $competitionId Competition ID
	 * @param string $event Event ID (required)
	 * @param string $round Round ID (required)
	 */
	public function actionPopulate($competitionId, $event, $round) {
		$this->log("Starting to populate live_result for competition ID: {$competitionId}, event: {$event}, round: {$round}");

		// Get competition
		$competition = Competition::model()->findByPk($competitionId);
		if ($competition === null) {
			$this->log("Error: Competition not found");
			return;
		}

		if ($competition->wca_competition_id == '') {
			$this->log("Error: Competition does not have WCA competition ID");
			return;
		}

		$this->log("Competition: {$competition->name} (WCA ID: {$competition->wca_competition_id})");

		// Get event round
		$eventRound = LiveEventRound::model()->findByAttributes([
			'competition_id' => $competitionId,
			'event' => $event,
			'round' => $round,
		]);

		if ($eventRound === null) {
			$this->log("Error: Event round not found");
			return;
		}

		$this->populateRound($competition, $eventRound);
		$this->log("Finished populating live_result");
	}

	/**
	 * Populate a single round with WCA data using personal bests from RanksAverage
	 */
	private function populateRound($competition, $eventRound) {
		$roundName = $eventRound->wcaRound ? $eventRound->wcaRound->name : $eventRound->round;
		$this->log("Round: {$eventRound->round} ({$roundName})");

		// Get users from existing live_result records for this round
		$existingResults = LiveResult::model()->with('user')->findAllByAttributes([
			'competition_id' => $competition->id,
			'event' => $eventRound->event,
			'round' => $eventRound->round,
		]);

		if (empty($existingResults)) {
			$this->log("No existing live_result records found for this round, seeding from previous round/registrations");
			$seeded = $this->seedLiveResultsForRound($competition, $eventRound);
			$this->log("Seeded {$seeded} live_result records");

			$existingResults = LiveResult::model()->with('user')->findAllByAttributes([
				'competition_id' => $competition->id,
				'event' => $eventRound->event,
				'round' => $eventRound->round,
			]);
			if (empty($existingResults)) {
				$this->log("Still no live_result records found after seeding, aborting");
				return;
			}
		}

		$this->log("Found " . count($existingResults) . " existing live_result records");

		$updated = 0;
		$skipped = 0;

		foreach ($existingResults as $liveResult) {
			$user = $liveResult->user;
			if ($user === null || $user->wcaid == '') {
				$skipped++;
				continue;
			}

			$wcaId = $user->wcaid;

			// Get personal best average from RanksAverage
			$rankAverage = RanksAverage::model()->findByAttributes([
				'person_id' => $wcaId,
				'event_id' => $eventRound->event,
			]);

			if ($rankAverage === null || $rankAverage->best <= 0) {
				$skipped++;
				continue;
			}

			$average = $rankAverage->best; // Note: RanksAverage->best is actually the average

			// Find the WCA result record that matches this average
			$wcaResult = $this->getWcaResultByAverage($wcaId, $eventRound->event, $average);
			if ($wcaResult === null) {
				$skipped++;
				continue;
			}

			// Use best from the result record
			$best = $wcaResult['best'];

			// Update live result with WCA data
			$liveResult->best = $best;
			$liveResult->average = $average;
			$liveResult->regional_single_record = '';
			$liveResult->regional_average_record = '';
			$liveResult->update_time = time();

			// Get attempt values from the WCA result record
			$attempts = $this->getWcaAttempts($wcaResult['id']);
			$liveResult->value1 = isset($attempts[1]) ? $attempts[1] : 0;
			$liveResult->value2 = isset($attempts[2]) ? $attempts[2] : 0;
			$liveResult->value3 = isset($attempts[3]) ? $attempts[3] : 0;
			$liveResult->value4 = isset($attempts[4]) ? $attempts[4] : 0;
			$liveResult->value5 = isset($attempts[5]) ? $attempts[5] : 0;

			$liveResult->save();
			$updated++;
		}

		$this->log("Updated: {$updated}, Skipped: {$skipped}");
	}

	/**
	 * Seed live_result rows for a round when they don't exist yet.
	 * Uses previous round's advanced competitors (top N), or registrations for first rounds.
	 *
	 * Mirrors ResultHandler::actionRefresh() behavior.
	 *
	 * @return int number of seeded/updated rows
	 */
	private function seedLiveResultsForRound($competition, $eventRound) {
		$seeded = 0;

		$lastRound = $eventRound->lastRound;
		if ($lastRound !== null) {
			$limit = intval($eventRound->number);
			$sourceResults = $lastRound->results; // best>0, ordered by rank
			if ($limit <= 0) {
				$limit = count($sourceResults);
			}
			foreach (array_slice($sourceResults, 0, $limit) as $source) {
				if ($this->createOrUpdateSeededResult($source, $eventRound)) {
					$seeded++;
				}
			}
			return $seeded;
		}

		// First round: seed from registrations
		$registrations = Registration::getRegistrations($competition);
		foreach ($registrations as $registration) {
			if (!in_array($eventRound->event, $registration->events)) {
				continue;
			}
			if ($this->createOrUpdateSeededResult($registration, $eventRound)) {
				$seeded++;
			}
		}
		return $seeded;
	}

	/**
	 * @param object $source LiveResult from last round OR Registration
	 * @return bool true if row was created/updated
	 */
	private function createOrUpdateSeededResult($source, $eventRound) {
		if (!isset($source->number) || !isset($source->user_id)) {
			return false;
		}

		$model = LiveResult::model()->findByAttributes([
			'competition_id' => $eventRound->competition_id,
			'event' => $eventRound->event,
			'round' => $eventRound->round,
			'number' => $source->number,
		]);

		if ($model === null) {
			$model = new LiveResult();
			$model->competition_id = $eventRound->competition_id;
			$model->user_id = $source->user_id;
			$model->number = $source->number;
			$model->event = $eventRound->event;
			$model->round = $eventRound->round;
			$model->format = $eventRound->format;
			return (bool)$model->save();
		}

		$changed = false;
		if ($model->user_id != $source->user_id) {
			$model->user_id = $source->user_id;
			$changed = true;
		}
		if ($model->format != $eventRound->format) {
			$model->format = $eventRound->format;
			$changed = true;
		}
		return $changed ? (bool)$model->save() : false;
	}

	/**
	 * Get WCA result record that matches the given average value
	 */
	private function getWcaResultByAverage($personId, $eventId, $average) {
		$command = Yii::app()->wcaDb->createCommand()
			->select(['r.id', 'r.best', 'r.average'])
			->from('results r')
			->where('r.person_id=:person_id', [
				':person_id' => $personId,
			])
			->andWhere('r.event_id=:event_id', [
				':event_id' => $eventId,
			])
			->andWhere('r.average=:average', [
				':average' => $average,
			])
			->order('r.id ASC')
			->limit(1);

		return $command->queryRow();
	}

	/**
	 * Get attempt values for a WCA result
	 */
	private function getWcaAttempts($wcaResultId) {
		$command = Yii::app()->wcaDb->createCommand()
			->select(['attempt_number', 'value'])
			->from('result_attempts')
			->where('result_id=:result_id', [
				':result_id' => $wcaResultId,
			])
			->order('attempt_number ASC');

		$attempts = [];
		foreach ($command->queryAll() as $row) {
			$attempts[$row['attempt_number']] = $row['value'];
		}

		return $attempts;
	}

	private function log() {
		printf("[%s] %s\n", date('Y-m-d H:i:s'), implode(' ', func_get_args()));
	}
}
