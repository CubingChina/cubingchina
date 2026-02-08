<?php

/**
 * Command to populate live_result table with WCA data for testing H2H functionality
 *
 * Usage:
 *   php protected/yiic LiveResult populate --id=123 --event=333 --round=f
 *   php protected/yiic LiveResult exportToLive --id=123 --event=333 --round=f --roundId=129186 [--roundInfo=live/roundinfo.json]
 *
 * exportToLive: Export round results to WCA Live. Requires live/wca_live_key file with the auth key.
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
		foreach (array_slice($pbs, 0, 20) as $number=>$pb) {
			$this->log("{$pb['user']->name} ({$pb['user']->wcaid}): {$pb['pb']} PB");
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
