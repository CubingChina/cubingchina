<?php

/**
 * Command to populate live_result table with WCA data for testing H2H functionality
 *
 * Usage:
 *   php protected/yiic LiveResult populate --id=123 --event=333 --round=f
 *   php protected/yiic LiveResult fillRandom --id=123 --event=333 --round=f
 *   php protected/yiic LiveResult exportToLive --id=123 --event=333 --round=f --roundId=129186 [--roundInfo=live/roundinfo.json]
 *   php protected/yiic LiveResult exportToNewLive --id=123 --event=333fm --round=1 [--liveRoundId=333fm-r1]
 *
 * exportToLive: Export round results to WCA Live. Requires live/wca_live_key file with the auth key.
 * exportToNewLive: Export round results to the new WCA Live API. Requires live/wca_live_key with a bearer token.
 */
class LiveResultCommand extends CConsoleCommand {
	public function actionCheckPR($id) {
		$competition = Competition::model()->findByPk($id);
		if ($competition === null) {
			$this->log("Error: Competition not found");
			return;
		}
		$registrations = Registration::getRegistrations($competition);
		$pbs = [];
		foreach ($registrations as $registration) {
			$number = $registration->number;
			$user = $registration->user;
			$wcaid = $user->wcaid;
			$pbs[$number] = [
				'number'=>$number,
				'user'=>$user,
				'pb'=>0,
			];
			$results = LiveResult::model()->findAllByAttributes(array(
				'competition_id'=>$competition->id,
				'number'=>$number,
			));
			usort($results, function($resA, $resB) {
				if ($resA->wcaEvent === null) {
					return 1;
				}
				if ($resB->wcaEvent === null) {
					return -1;
				}
				$temp = $resA->wcaEvent->rank - $resB->wcaEvent->rank;
				if ($temp == 0) {
					$temp = $resA->wcaRound->rank - $resB->wcaRound->rank;
				}
				return $temp;
			});
			$temp = array();
			foreach ($results as $result) {
				if ($result->best == 0) {
					continue;
				}
				if (!isset($temp[$result->event])) {
					$temp[$result->event] = array(
						'event'=>$result->event,
						'results'=>array(),
					);
				}
				$temp[$result->event]['results'][] = $result->getShowAttributes(true);
			}
			$events = array();
			if ($wcaid != '') {
				$personResults = Persons::getResults($wcaid);
				foreach ($personResults['personRanks'] as $rank) {
					$events[$rank->event_id] = $rank->event_id;
					if (!isset($temp[$rank->event_id])) {
						continue;
					}
					$best = $rank->best;
					$average = $rank->average == null ? PHP_INT_MAX : $rank->average->best;
					foreach ($temp[$rank->event_id]['results'] as $key=>$result) {
						if ($result['b'] > 0 && $result['b'] <= $best) {
							$temp[$rank->event_id]['results'][$key]['nb'] = true;
							$pbs[$number]['pb']++;
							$best = $result['b'];
						}
						if ($result['a'] > 0 && $result['a'] <= $average) {
							$temp[$rank->event_id]['results'][$key]['na'] = true;
							$pbs[$number]['pb']++;
							$average = $result['a'];
						}
					}
				}
			}
			foreach ($temp as $event=>$results) {
				//event didn't attend before
				if (!isset($events[$event])) {
					$best = $average = PHP_INT_MAX;
					foreach ($results['results'] as $key=>$result) {
						if ($result['b'] > 0 && $result['b'] <= $best) {
							$results['results'][$key]['nb'] = true;
							$pbs[$number]['pb']++;
							$best = $result['b'];
						}
						if ($result['a'] > 0 && $result['a'] <= $average) {
							$results['results'][$key]['na'] = true;
							$pbs[$number]['pb']++;
							$average = $result['a'];
						}
					}
				}
				$temp[$event]['results'] = array_reverse($results['results']);
			}
			echo "No.{$number}: {$user->getCompetitionName()} {$user->wcaid} {$pbs[$number]['pb']} PB\n";
		}
		// sort by pbs desc
		usort($pbs, function($a, $b) {
			return $b['pb'] - $a['pb'];
		});
		echo "Top 20:\n";
		foreach (array_slice($pbs, 0, 20) as $pb) {
			echo "No.{$pb['number']}: {$pb['user']->getCompetitionName()} ({$pb['user']->wcaid}): {$pb['pb']} PB\n";
		}
	}

	/**
	 * Populate live_result with WCA data for a specific round
	 *
	 * @param int $id Competition ID
	 * @param string $event Event ID (required)
	 * @param string $round Round ID (required)
	 */
	public function actionPopulate($id, $event, $round) {
		$this->log("Starting to populate live_result for competition ID: {$id}, event: {$event}, round: {$round}");

		// Get competition
		$competition = Competition::model()->findByPk($id);
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
			'competition_id' => $id,
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
	 * Fill random results for competitors in a round that have no data yet.
	 *
	 * For 333fm singles random move counts in [20, 40] are generated; for any
	 * other event random centiseconds in [500, 3000] are generated. Each attempt
	 * has a 1/3 chance of being a DNF. The number of attempts, the best and the
	 * average are derived from the round format.
	 *
	 * @param int $id Competition ID
	 * @param string $event Event ID (required)
	 * @param string $round Round ID (required)
	 */
	public function actionFillRandom($id, $event, $round) {
		$this->log("Filling random results for competition ID: {$id}, event: {$event}, round: {$round}");

		$competition = Competition::model()->findByPk($id);
		if ($competition === null) {
			$this->log("Error: Competition not found");
			return;
		}

		$eventRound = LiveEventRound::model()->findByAttributes([
			'competition_id' => $id,
			'event' => $event,
			'round' => $round,
		]);
		if ($eventRound === null) {
			$this->log("Error: Event round not found");
			return;
		}

		$results = LiveResult::model()->findAllByAttributes([
			'competition_id' => $id,
			'event' => $event,
			'round' => $round,
		]);
		if (empty($results)) {
			$this->log("No live_result records found for this round");
			return;
		}

		$this->log("Found " . count($results) . " live_result records, format: {$eventRound->format}");

		$filled = 0;
		foreach ($results as $liveResult) {
			$values = $this->generateRandomValues($event, $eventRound->format);
			for ($i = 1; $i <= 5; $i++) {
				$liveResult->{'value' . $i} = isset($values[$i - 1]) ? $values[$i - 1] : 0;
			}
			$liveResult->best = $this->calcBest($values);
			$liveResult->average = $this->calcAverage($values, $event, $eventRound->format);
			$liveResult->regional_single_record = '';
			$liveResult->regional_average_record = '';
			$liveResult->update_time = time();
			$liveResult->save();
			$filled++;
		}

		$this->log("Filled: {$filled}");
	}

	/**
	 * Number of attempts implied by a round format.
	 */
	private function getAttemptCount($format) {
		switch ($format) {
			case '1':
			case '2':
			case '3':
				return intval($format);
			case 'm':
				return 3;
			case 'a':
			default:
				return 5;
		}
	}

	/**
	 * Generate random attempt values for one competitor. Each attempt has a 1/3
	 * chance of being a DNF (-1).
	 *
	 * @return int[] list of attempt values
	 */
	private function generateRandomValues($event, $format) {
		$count = $this->getAttemptCount($format);
		$values = [];
		for ($i = 0; $i < $count; $i++) {
			if (mt_rand(1, 3) === 1) {
				$values[] = -1; // DNF
				continue;
			}
			$values[] = $event === '333fm' ? mt_rand(20, 40) : mt_rand(500, 3000);
		}
		return $values;
	}

	/**
	 * Best (single) is the minimum of the successful attempts, or DNF (-1) when
	 * every attempt is a DNF.
	 */
	private function calcBest($values) {
		$valid = array_filter($values, function($v) { return $v > 0; });
		return empty($valid) ? -1 : min($valid);
	}

	/**
	 * Compute the average/mean from attempts following WCA rules. DNF attempts
	 * sort to the end; a mean is DNF (-1) once too many attempts are DNFs.
	 * Best-of-N formats have no average. For 333fm the mean is stored as the
	 * mean of moves multiplied by 100.
	 */
	private function calcAverage($values, $event, $format) {
		// Sort ascending with DNF (-1) treated as the worst result.
		$sorted = $values;
		usort($sorted, function($a, $b) {
			if ($a <= 0 && $b <= 0) {
				return 0;
			}
			if ($a <= 0) {
				return 1;
			}
			if ($b <= 0) {
				return -1;
			}
			return $a - $b;
		});

		if ($format === 'a') {
			// Average of 5: drop best and worst, mean of the middle three.
			// One DNF is tolerated (dropped as worst); two or more is DNF.
			$middle = array_slice($sorted, 1, 3);
			foreach ($middle as $v) {
				if ($v <= 0) {
					return -1;
				}
			}
			$mean = array_sum($middle) / count($middle);
			return (int) round($mean);
		}
		if ($format === 'm') {
			// Mean of 3: every attempt counts, any DNF makes the mean DNF.
			foreach ($values as $v) {
				if ($v <= 0) {
					return -1;
				}
			}
			$mean = array_sum($values) / count($values);
			if ($event === '333fm') {
				return (int) round($mean * 100);
			}
			return (int) round($mean);
		}
		// Best-of-N formats have no average.
		return 0;
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
			$this->log("No existing live_result records found for this round");
			return;
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

	/**
	 * Export round results to WCA Live
	 *
	 * @param int $id Competition ID
	 * @param string $event Event ID
	 * @param string $round Round ID
	 * @param string $roundId WCA Live round ID (e.g. 129186)
	 * @param string $roundInfo Optional path to roundinfo.json (skips API fetch)
	 */
	public function actionExportToLive($id, $event, $round, $roundId, $roundInfo = '') {
		$this->log("Exporting to WCA Live: competition={$id}, event={$event}, round={$round}, roundId={$roundId}");

		$keyFile = Yii::getPathOfAlias('application') . '/../live/wca_live_key';
		if (!file_exists($keyFile)) {
			$this->log("Error: wca_live_key file not found at live/wca_live_key");
			return;
		}
		$wcaLiveKey = trim(file_get_contents($keyFile));
		if ($wcaLiveKey === '') {
			$this->log("Error: wca_live_key is empty");
			return;
		}

		$competition = Competition::model()->findByPk($id);
		if (!$this->confirm($competition->name_zh)) {
			return;
		}

		$eventRound = LiveEventRound::model()->findByAttributes([
			'competition_id' => $id,
			'event' => $event,
			'round' => $round,
		]);
		if ($eventRound === null) {
			$this->log("Error: Event round not found");
			return;
		}

		$roundData = $roundInfo !== '' && file_exists($roundInfo)
			? $this->loadRoundInfoFromFile($roundInfo)
			: $this->fetchRoundInfo($roundId);
		if ($roundData === null) {
			$this->log("Error: Failed to get round info");
			return;
		}

		$registrantIdToResultId = $this->buildRegistrantIdToResultIdMap($roundData);
		$this->log("Round has " . count($registrantIdToResultId) . " results from WCA Live");

		$liveResults = LiveResult::model()->findAllByAttributes([
			'competition_id' => $id,
			'event' => $event,
			'round' => $round,
		], ['condition' => 'best != 0']);

		$resultsPayload = [];
		$enteredAt = gmdate('Y-m-d\TH:i:s.v\Z');
		$maxAtttempts = 5;
		switch ($eventRound->format) {
			case '1':
			case '2':
			case '3':
				$maxAtttempts = intval($maxAtttempts);
				break;
			case 'm':
				$maxAtttempts = 3;
		}
		foreach ($liveResults as $lr) {
			$registrantId = (int) $lr->number;
			$wcaLiveResultId = isset($registrantIdToResultId[$registrantId]) ? $registrantIdToResultId[$registrantId] : null;
			if ($wcaLiveResultId === null) {
				continue;
			}
			$attempts = [];
			$values = [$lr->value1, $lr->value2, $lr->value3, $lr->value4, $lr->value5];
			$values = array_slice($values, 0, $maxAtttempts);
			if (array_filter($values, function($v) { return $v != 0; }) === []) {
				continue;
			}
			if (array_filter($values, function($v) { return $v != -2; }) === []) {
				continue;
			}
			foreach ($values as $v) {
				if ($v == 0) {
					break;
				}
				$attempts[] = ['result' => (int) $v];
			}
			$resultsPayload[] = [
				'id' => $wcaLiveResultId,
				'attempts' => $attempts,
				'enteredAt' => $enteredAt,
			];
		}

		if (empty($resultsPayload)) {
			$this->log("No results to export (no matching registrantId/number or no completed results)");
			return;
		}

		$this->log("Exporting " . count($resultsPayload) . " results to WCA Live");
		$success = $this->enterResults($roundId, $resultsPayload, $wcaLiveKey);
		$this->log($success ? "Export completed successfully" : "Export failed");
	}

	/**
	 * Export round results to the new WCA Live API.
	 *
	 * The new endpoint accepts one competitor's result per PATCH request.
	 *
	 * @param int $id Competition ID
	 * @param string $event Event ID
	 * @param string $round CubingChina round ID
	 * @param string $liveRoundId Optional WCA Live round ID in the REST URL (e.g. 333fm-r1)
	 */
	public function actionExportToNewLive($id, $event, $round, $liveRoundId = '') {
		$this->log("Exporting to new WCA Live: competition={$id}, event={$event}, round={$round}");

		$keyFile = Yii::getPathOfAlias('application') . '/../live/wca_live_key';
		if (!file_exists($keyFile)) {
			$this->log("Error: wca_live_key file not found at live/wca_live_key");
			return;
		}
		$wcaLiveKey = trim(file_get_contents($keyFile));
		if ($wcaLiveKey === '') {
			$this->log("Error: wca_live_key is empty");
			return;
		}

		$competition = Competition::model()->findByPk($id);
		if ($competition === null) {
			$this->log("Error: Competition not found");
			return;
		}
		if ($competition->wca_competition_id == '') {
			$this->log("Error: Competition does not have WCA competition ID");
			return;
		}
		if (!$this->confirm($competition->name_zh)) {
			return;
		}

		$eventRound = LiveEventRound::model()->findByAttributes([
			'competition_id' => $id,
			'event' => $event,
			'round' => $round,
		]);
		if ($eventRound === null) {
			$this->log("Error: Event round not found");
			return;
		}
		if ($liveRoundId === '') {
			$liveRoundId = $this->buildNewLiveRoundId($eventRound);
		}
		if ($liveRoundId === null) {
			$this->log("Error: Failed to build WCA Live round ID");
			return;
		}
		$this->log("WCA Live round ID: {$liveRoundId}");

		$registrantIdToRegistrationId = $this->fetchNewLiveRegistrationMap($competition->wca_competition_id);
		if ($registrantIdToRegistrationId === null) {
			$this->log("Error: Failed to fetch registrations from WCA API");
			return;
		}
		$this->log("Fetched " . count($registrantIdToRegistrationId) . " registrations from WCA API");

		$liveResults = LiveResult::model()->findAllByAttributes([
			'competition_id' => $id,
			'event' => $event,
			'round' => $round,
		], ['condition' => 'best != 0']);
		if (empty($liveResults)) {
			$this->log("No live_result records found for this round");
			return;
		}

		$attemptCount = $this->getAttemptCount($eventRound->format);
		$exported = 0;
		$failed = 0;
		$skipped = 0;
		foreach ($liveResults as $lr) {
			$registrantId = (int) $lr->number;
			$registrationId = isset($registrantIdToRegistrationId[$registrantId]) ? $registrantIdToRegistrationId[$registrantId] : null;
			if ($registrationId === null) {
				$this->log("Skip No.{$registrantId}: registration_id not found");
				$skipped++;
				continue;
			}
			$attempts = $this->buildNewLiveAttempts($lr, $attemptCount);
			if (empty($attempts)) {
				$this->log("Skip No.{$registrantId}: no attempts to export");
				$skipped++;
				continue;
			}
			$success = $this->patchNewLiveResult($competition->wca_competition_id, $liveRoundId, $registrationId, $attempts, $wcaLiveKey);
			if ($success) {
				$this->log("Exported No.{$registrantId} as registration_id={$registrationId}");
				$exported++;
			} else {
				$this->log("Failed No.{$registrantId} as registration_id={$registrationId}");
				$failed++;
			}
		}

		$this->log("Export finished: exported={$exported}, skipped={$skipped}, failed={$failed}");
	}

	private function buildNewLiveRoundId($eventRound) {
		$roundIndex = $eventRound->roundIndex;
		if ($roundIndex < 0) {
			return null;
		}
		return $eventRound->event . '-r' . ($roundIndex + 1);
	}

	private function fetchNewLiveRegistrationMap($competitionId) {
		$url = 'https://www.worldcubeassociation.org/api/v1/competitions/' . rawurlencode($competitionId) . '/registrations';
		$ctx = stream_context_create([
			'http' => [
				'method' => 'GET',
				'header' => "Accept: application/json\r\n",
				'ignore_errors' => true,
			],
		]);
		$resp = @file_get_contents($url, false, $ctx);
		if ($resp === false) {
			return null;
		}
		$data = json_decode($resp, true);
		if (!is_array($data)) {
			return null;
		}
		$registrations = isset($data['registrations']) && is_array($data['registrations']) ? $data['registrations'] : $data;
		$map = [];
		foreach ($registrations as $registration) {
			if (!is_array($registration) || !isset($registration['registrant_id'], $registration['id'])) {
				continue;
			}
			$map[(int) $registration['registrant_id']] = (int) $registration['id'];
		}
		return $map;
	}

	private function buildNewLiveAttempts($liveResult, $attemptCount) {
		$attempts = [];
		for ($i = 1; $i <= $attemptCount; $i++) {
			$value = (int) $liveResult->{'value' . $i};
			if ($value === 0) {
				break;
			}
			$attempts[] = [
				'value' => $value,
				'attempt_number' => $i,
			];
		}
		if (empty($attempts)) {
			return [];
		}
		$hasNonDns = false;
		foreach ($attempts as $attempt) {
			if ($attempt['value'] !== -2) {
				$hasNonDns = true;
				break;
			}
		}
		return $hasNonDns ? $attempts : [];
	}

	private function patchNewLiveResult($competitionId, $liveRoundId, $registrationId, $attempts, $wcaLiveKey) {
		$url = 'https://www.worldcubeassociation.org/api/v1/competitions/' . rawurlencode($competitionId) . '/live/rounds/' . rawurlencode($liveRoundId);
		$payload = json_encode([
			'attempts' => $attempts,
			'registration_id' => (int) $registrationId,
		]);
		$ctx = stream_context_create([
			'http' => [
				'method' => 'PATCH',
				'header' => "Authorization: Bearer {$wcaLiveKey}\r\nContent-Type: application/json\r\nAccept: application/json\r\n",
				'content' => $payload,
				'ignore_errors' => true,
			],
		]);
		$resp = @file_get_contents($url, false, $ctx);
		$status = $this->getHttpStatusCode(isset($http_response_header) ? $http_response_header : []);
		if ($resp === false || $status < 200 || $status >= 300) {
			$this->log("New WCA Live API error: status={$status}, response={$resp}");
			return false;
		}
		return true;
	}

	private function getHttpStatusCode($headers) {
		foreach ($headers as $header) {
			if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $matches)) {
				return (int) $matches[1];
			}
		}
		return 0;
	}

	private function loadRoundInfoFromFile($path) {
		$content = @file_get_contents($path);
		if ($content === false) {
			return null;
		}
		$data = json_decode($content, true);
		if ($data === null) {
			$data = $this->extractFirstJsonObject($content);
		}
		if ($data === null) {
			return null;
		}
		$round = isset($data['data']['round']) ? $data['data']['round'] : (isset($data['round']) ? $data['round'] : null);
		return $round;
	}

	private function extractFirstJsonObject($str) {
		$start = strpos($str, '{');
		if ($start === false) {
			return null;
		}
		$depth = 0;
		$len = strlen($str);
		for ($i = $start; $i < $len; $i++) {
			$c = $str[$i];
			if ($c === '"' || $c === "'") {
				$q = $c;
				for ($j = $i + 1; $j < $len; $j++) {
					if ($str[$j] === '\\') {
						$j++;
						continue;
					}
					if ($str[$j] === $q) {
						$i = $j;
						break;
					}
				}
				continue;
			}
			if ($c === '{' || $c === '[') {
				$depth++;
			} elseif ($c === '}' || $c === ']') {
				$depth--;
				if ($depth === 0) {
					return json_decode(substr($str, $start, $i - $start + 1), true);
				}
			}
		}
		return null;
	}

	private function fetchRoundInfo($roundId) {
		$query = 'query Round($id: ID!) { round(id: $id) { id name number results { id ...adminRoundResult } } } fragment adminRoundResult on Result { person { id registrantId name wcaId } }';
		$payload = json_encode([
			'operationName' => 'Round',
			'variables' => ['id' => (string) $roundId],
			'query' => $query,
		]);
		$ctx = stream_context_create([
			'http' => [
				'method' => 'POST',
				'header' => "Content-Type: application/json\r\n",
				'content' => $payload,
			],
		]);
		$resp = @file_get_contents('https://live.worldcubeassociation.org/api', false, $ctx);
		if ($resp === false) {
			return null;
		}
		$data = json_decode($resp, true);
		$round = isset($data['data']['round']) ? $data['data']['round'] : null;
		return $round;
	}

	private function buildRegistrantIdToResultIdMap($roundData) {
		$map = [];
		$results = isset($roundData['results']) ? $roundData['results'] : [];
		foreach ($results as $r) {
			$registrantId = isset($r['person']['registrantId']) ? (int) $r['person']['registrantId'] : null;
			if ($registrantId !== null && isset($r['id'])) {
				$map[$registrantId] = $r['id'];
			}
		}
		return $map;
	}

	private function enterResults($roundId, $resultsPayload, $wcaLiveKey) {
		$query = 'mutation EnterResults($input: EnterResultsInput!) { enterResults(input: $input) { round { id number results { id attempts { result } } } } }';
		$payload = json_encode([
			'operationName' => 'EnterResults',
			'variables' => [
				'input' => [
					'id' => (string) $roundId,
					'results' => $resultsPayload,
				],
			],
			'query' => $query,
		]);
		$ctx = stream_context_create([
			'http' => [
				'method' => 'POST',
				'header' => "Content-Type: application/json\r\nCookie: _wca_live_key=" . urlencode($wcaLiveKey) . "\r\n",
				'content' => $payload,
			],
		]);
		$resp = @file_get_contents('https://live.worldcubeassociation.org/api', false, $ctx);
		if ($resp === false) {
			$this->log("HTTP request failed");
			return false;
		}
		$data = json_decode($resp, true);
		if (isset($data['errors'])) {
			$this->log("API errors: " . json_encode($data['errors']));
			return false;
		}
		return isset($data['data']['enterResults']);
	}

	private function log() {
		printf("[%s] %s\n", date('Y-m-d H:i:s'), implode(' ', func_get_args()));
	}
}
