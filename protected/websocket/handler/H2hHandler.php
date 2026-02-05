<?php

class H2hHandler extends MsgHandler {

	private static $whiteListActions = ['fetch', 'rounds', 'initialize', 'convertToNormal'];

	public function process() {
		if ($this->competition == null) {
			return;
		}
		$action = $this->getAction();
		if ($action !== '') {
			if (!in_array($action, self::$whiteListActions) && !$this->checkAccess()) {
				return;
			}
			$method = 'action' . ucfirst($action);
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}
	}

	/**
	 * Fetch H2H round data
	 */
	public function actionFetch() {
		$h2hRound = LiveH2HRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>"{$this->msg->params->event}",
			'round'=>"{$this->msg->params->round}",
		));
		if ($h2hRound === null) {
			return;
		}
		$matches = LiveH2HMatch::model()->with('competitor1', 'competitor2', 'sets.points')->findAllByAttributes(array(
			'h2h_round_id'=>$h2hRound->id,
		), array(
			'order'=>'match_number ASC',
		));
		$data = array(
			'round'=>$h2hRound->getBroadcastAttributes(),
			'matches'=>array(),
		);
		foreach ($matches as $match) {
			$matchData = $match->getBroadcastAttributes();
			$matchData['sets'] = array();
			foreach ($match->sets as $set) {
				$setData = $set->getBroadcastAttributes();
				$setData['points'] = array();
				foreach ($set->points as $point) {
					$setData['points'][] = $point->getBroadcastAttributes();
				}
				$matchData['sets'][] = $setData;
			}
			$data['matches'][] = $matchData;
		}
		$this->success('h2h.fetch', $data, $this->competition);
	}

	/**
	 * Get all H2H rounds
	 */
	public function actionRounds() {
		$h2hRounds = LiveH2HRound::model()->findAllByAttributes(array(
			'competition_id'=>$this->competition->id,
		));
		$this->success('h2h.rounds', array_map(function($round) {
			return $round->getBroadcastAttributes();
		}, $h2hRounds), $this->competition);
	}

	/**
	 * Update point result
	 */
	public function actionUpdatePoint() {
		$data = $this->msg->point;
		if (!isset($data->id)) {
			return;
		}
		$point = LiveH2HPoint::model()->findByPk($data->id);
		if ($point === null) {
			return;
		}
		if (isset($data->competitor1_result)) {
			$point->competitor1_result = $data->competitor1_result;
		}
		if (isset($data->competitor2_result)) {
			$point->competitor2_result = $data->competitor2_result;
		}
		$point->operator_id = $this->user->id;
		$point->update_time = time();
		$point->updatePointWinner();
		$point->save();

		// Broadcast updates
		$this->broadcastSuccess('h2h.point.update', $point->getBroadcastAttributes(), $this->competition);

		// Update set
		$set = $point->set;
		if ($set) {
			$this->broadcastSuccess('h2h.set.update', $set->getBroadcastAttributes(), $this->competition);

			// Update match
			$match = $set->match;
			if ($match) {
				$match->checkMatchFinished();
				$match->save();
				$this->broadcastSuccess('h2h.match.update', $match->getBroadcastAttributes(), $this->competition);
			}
		}
	}


	/**
	 * Initialize H2H round from previous round results
	 */
	public function actionInitialize() {
		$data = $this->msg->round;
		if (!isset($data->event) || !isset($data->round)) {
			return;
		}
		$eventRound = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>$data->event,
			'round'=>$data->round,
		));
		if ($eventRound === null) {
			return;
		}

		// Only allow initialization for the last round
		if (!$eventRound->isLastRound()) {
			return;
		}

		// Get previous round results for seeding
		$lastRound = $eventRound->getLastRound();
		$competitors = array();
		if ($lastRound !== null) {
			// Get results from previous round, sorted by rank
			$results = $lastRound->getResults();
			foreach ($results as $index => $result) {
				$competitors[] = array(
					'user_id' => $result->user_id,
					'seed' => $index + 1,
					'number' => $result->number,
				);
			}
		} else {
			// No previous round, get from registrations
			$registrations = Registration::getRegistrations($this->competition);
			foreach ($registrations as $registration) {
				if (in_array($data->event, $registration->events)) {
					$competitors[] = array(
						'user_id' => $registration->user_id,
						'seed' => count($competitors) + 1,
						'number' => $registration->number,
					);
				}
			}
		}

		// Validate places
		$places = isset($data->places) ? intval($data->places) : count($competitors);
		if (!in_array($places, array(4, 8, 12, 16))) {
			$places = min(16, max(4, pow(2, ceil(log(count($competitors), 2)))));
		}
		if (count($competitors) < $places) {
			return; // Not enough competitors
		}
		$competitors = array_slice($competitors, 0, $places);

		// Create or update H2H round
		$h2hRound = LiveH2HRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>$data->event,
			'round'=>$data->round,
		));
		if ($h2hRound === null) {
			$h2hRound = new LiveH2HRound();
			$h2hRound->competition_id = $this->competition->id;
			$h2hRound->event = $data->event;
			$h2hRound->round = $data->round;
			$h2hRound->create_time = time();
		}
		$h2hRound->places = $places;
		$h2hRound->stage = $this->getFirstStage($places);
		$h2hRound->sets_to_win = intval($data->sets_to_win);
		// points_to_win_set is always 3, set by server
		$h2hRound->points_to_win_set = 3;
		$h2hRound->operator_id = $this->user->id;
		$h2hRound->update_time = time();
		$h2hRound->save();

		// Mark event round as H2H by setting format to 'h'
		$eventRound->format = 'h';
		$eventRound->is_h2h = 1;
		$eventRound->save();

		// Delete existing matches if reinitializing
		$existingMatches = LiveH2HMatch::model()->findAllByAttributes(array(
			'h2h_round_id'=>$h2hRound->id,
		));
		foreach ($existingMatches as $match) {
			LiveH2HSet::model()->deleteAllByAttributes(array('match_id'=>$match->id));
			LiveH2HPoint::model()->deleteAllByAttributes(array('match_id'=>$match->id));
			$match->delete();
		}

		// Create matches for first stage based on seeding
		// Highest seed vs lowest seed, second highest vs second lowest, etc.
		// Exception: for 12 places, top 4 seeded competitors skip Stage of 12
		$matchNumber = 1;
		$stage = $h2hRound->stage;
		$competitorsForStage = $competitors;

		if ($places == 12) {
			// Top 4 skip Stage of 12, only create matches for seeds 5-12
			$competitorsForStage = array_slice($competitors, 4);
		}

		$numMatches = count($competitorsForStage) / 2;
		for ($i = 0; $i < $numMatches; $i++) {
			$competitor1 = $competitorsForStage[$i];
			$competitor2 = $competitorsForStage[count($competitorsForStage) - 1 - $i];

			$match = new LiveH2HMatch();
			$match->h2h_round_id = $h2hRound->id;
			$match->competition_id = $this->competition->id;
			$match->event = $data->event;
			$match->round = $data->round;
			$match->stage = $stage;
			$match->match_number = $matchNumber++;
			$match->competitor1_id = $competitor1['user_id'];
			$match->competitor1_seed = $competitor1['seed'];
			$match->competitor2_id = $competitor2['user_id'];
			$match->competitor2_seed = $competitor2['seed'];
			// sets_to_win: if not set (0), will use h2h round's sets_to_win
			// For final stage, can be set to a higher value
			$match->sets_to_win = 0; // Default: use round's sets_to_win
			$match->operator_id = $this->user->id;
			$match->create_time = time();
			$match->update_time = time();
			$match->save();

			// Create first set for the match
			$set = new LiveH2HSet();
			$set->match_id = $match->id;
			$set->set_number = 1;
			$set->operator_id = $this->user->id;
			$set->create_time = time();
			$set->update_time = time();
			$set->save();

			// Create first point for the set
			$point = new LiveH2HPoint();
			$point->set_id = $set->id;
			$point->match_id = $match->id;
			$point->competitor1_id = $match->competitor1_id;
			$point->competitor2_id = $match->competitor2_id;
			$point->point_number = 1;
			$point->operator_id = $this->user->id;
			$point->create_time = time();
			$point->update_time = time();
			$point->save();
		}

		$this->broadcastSuccess('h2h.round.update', $h2hRound->getBroadcastAttributes(), $this->competition);
		$this->actionFetch(); // Return full data
	}

	/**
	 * Get first stage name based on places
	 */
	private function getFirstStage($places) {
		switch ($places) {
			case 4:
				return 'Semifinal';
			case 8:
				return 'Quarterfinal';
			case 12:
				return 'Stage of 12';
			case 16:
				return 'Stage of 16';
			default:
				return 'First Stage';
		}
	}

	/**
	 * Create or update H2H round
	 */
	public function actionCreateRound() {
		$data = $this->msg->round;
		if (!isset($data->competition_id) || !isset($data->event) || !isset($data->round)) {
			return;
		}
		$h2hRound = LiveH2HRound::model()->findByAttributes(array(
			'competition_id'=>$data->competition_id,
			'event'=>$data->event,
			'round'=>$data->round,
		));
		if ($h2hRound === null) {
			$h2hRound = new LiveH2HRound();
			$h2hRound->competition_id = $data->competition_id;
			$h2hRound->event = $data->event;
			$h2hRound->round = $data->round;
			$h2hRound->create_time = time();
		}
		if (isset($data->places)) {
			$h2hRound->places = $data->places;
		}
		if (isset($data->stage)) {
			$h2hRound->stage = $data->stage;
		}
		if (isset($data->sets_to_win)) {
			$h2hRound->sets_to_win = $data->sets_to_win;
		} else {
			// Default: final round (round == 'f') uses 2, others use 1
			// Only set default if not already set
			if ($h2hRound->sets_to_win == 0) {
				$h2hRound->sets_to_win = ($data->round === 'f') ? 2 : 1;
			}
		}
		// points_to_win_set is always 3, fixed by server
		$h2hRound->points_to_win_set = 3;
		$h2hRound->operator_id = $this->user->id;
		$h2hRound->update_time = time();
		$h2hRound->save();

		// Mark event round as H2H by setting format to 'h'
		$eventRound = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$h2hRound->competition_id,
			'event'=>$h2hRound->event,
			'round'=>$h2hRound->round,
		));
		if ($eventRound) {
			$eventRound->format = 'h';
			$eventRound->save();
		}

		$this->broadcastSuccess('h2h.round.update', $h2hRound->getBroadcastAttributes(), $this->competition);
	}

	/**
	 * Create or update match
	 */
	public function actionCreateMatch() {
		$data = $this->msg->match;
		if (!isset($data->h2h_round_id)) {
			return;
		}
		$h2hRound = LiveH2HRound::model()->findByPk($data->h2h_round_id);
		if ($h2hRound === null) {
			return;
		}
		$match = null;
		if (isset($data->id)) {
			$match = LiveH2HMatch::model()->findByPk($data->id);
		}
		if ($match === null) {
			$match = new LiveH2HMatch();
			$match->h2h_round_id = $data->h2h_round_id;
			$match->competition_id = $h2hRound->competition_id;
			$match->event = $h2hRound->event;
			$match->round = $h2hRound->round;
			$match->create_time = time();
		}
		if (isset($data->stage)) {
			$match->stage = $data->stage;
		}
		if (isset($data->match_number)) {
			$match->match_number = $data->match_number;
		}
		if (isset($data->competitor1_id)) {
			$match->competitor1_id = $data->competitor1_id;
		}
		if (isset($data->competitor1_seed)) {
			$match->competitor1_seed = $data->competitor1_seed;
		}
		if (isset($data->competitor2_id)) {
			$match->competitor2_id = $data->competitor2_id;
		}
		if (isset($data->competitor2_seed)) {
			$match->competitor2_seed = $data->competitor2_seed;
		}
		if (isset($data->sets_to_win)) {
			$match->sets_to_win = intval($data->sets_to_win);
		}
		$match->operator_id = $this->user->id;
		$match->update_time = time();
		$match->save();

		$this->broadcastSuccess('h2h.match.update', $match->getBroadcastAttributes(), $this->competition);
	}

	/**
	 * Create set
	 */
	public function actionCreateSet() {
		$data = $this->msg->set;
		if (!isset($data->match_id)) {
			return;
		}
		$match = LiveH2HMatch::model()->findByPk($data->match_id);
		if ($match === null) {
			return;
		}
		$set = new LiveH2HSet();
		$set->match_id = $data->match_id;
		if (isset($data->set_number)) {
			$set->set_number = $data->set_number;
		} else {
			// Auto-increment set number
			$lastSet = LiveH2HSet::model()->findByAttributes(array(
				'match_id'=>$data->match_id,
			), array(
				'order'=>'set_number DESC',
			));
			$set->set_number = $lastSet ? $lastSet->set_number + 1 : 1;
		}
		$set->operator_id = $this->user->id;
		$set->create_time = time();
		$set->update_time = time();
		$set->save();

		$this->broadcastSuccess('h2h.set.update', $set->getBroadcastAttributes(), $this->competition);
	}

	/**
	 * Create point
	 */
	public function actionCreatePoint() {
		$data = $this->msg->point;
		if (!isset($data->set_id) || !isset($data->match_id)) {
			return;
		}
		$set = LiveH2HSet::model()->findByPk($data->set_id);
		$match = LiveH2HMatch::model()->findByPk($data->match_id);
		if ($set === null || $match === null) {
			return;
		}
		$point = new LiveH2HPoint();
		$point->set_id = $data->set_id;
		$point->match_id = $data->match_id;
		$point->competitor1_id = $match->competitor1_id;
		$point->competitor2_id = $match->competitor2_id;
		if (isset($data->point_number)) {
			$point->point_number = $data->point_number;
		} else {
			// Auto-increment point number
			$lastPoint = LiveH2HPoint::model()->findByAttributes(array(
				'set_id'=>$data->set_id,
			), array(
				'order'=>'point_number DESC',
			));
			$point->point_number = $lastPoint ? $lastPoint->point_number + 1 : 1;
		}
		$point->operator_id = $this->user->id;
		$point->create_time = time();
		$point->update_time = time();
		$point->save();

		$this->broadcastSuccess('h2h.point.update', $point->getBroadcastAttributes(), $this->competition);
	}

	/**
	 * Convert H2H round back to normal round (with confirmation)
	 */
	public function actionConvertToNormal() {
		$data = $this->msg->round;
		if (!isset($data->event) || !isset($data->round)) {
			return;
		}
		$eventRound = LiveEventRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>$data->event,
			'round'=>$data->round,
		));
		if ($eventRound === null || $eventRound->format !== 'h') {
			return;
		}

		// Find H2H round and delete all related data
		$h2hRound = LiveH2HRound::model()->findByAttributes(array(
			'competition_id'=>$this->competition->id,
			'event'=>$data->event,
			'round'=>$data->round,
		));
		if ($h2hRound !== null) {
			// Delete all matches, sets, and points
			$matches = LiveH2HMatch::model()->findAllByAttributes(array(
				'h2h_round_id'=>$h2hRound->id,
			));
			foreach ($matches as $match) {
				LiveH2HSet::model()->deleteAllByAttributes(array('match_id'=>$match->id));
				LiveH2HPoint::model()->deleteAllByAttributes(array('match_id'=>$match->id));
				$match->delete();
			}
			$h2hRound->delete();
		}

		// Mark event round as normal by setting format to empty or default
		$eventRound->format = '';
		$eventRound->save();

		// Broadcast update
		$this->broadcastSuccess('round.update', $eventRound->getBroadcastAttributes(), $this->competition);
	}
}
