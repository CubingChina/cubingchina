<?php

/**
 * This is the model class for table "live_h2h_set".
 */
class LiveH2HSet extends ActiveRecord {

	const STATUS_NOT_STARTED = 0;
	const STATUS_IN_PROGRESS = 1;
	const STATUS_FINISHED = 2;

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_h2h_set';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('match_id, set_number', 'required'),
			array('set_number, competitor1_points, competitor2_points, status', 'numerical', 'integerOnly'=>true),
			array('match_id, winner_id, operator_id, create_time, update_time', 'length', 'max'=>10),
			array('id, match_id, set_number, competitor1_points, competitor2_points, winner_id, status, operator_id, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array(
			'match'=>array(self::BELONGS_TO, 'LiveH2HMatch', 'match_id'),
			'winner'=>array(self::BELONGS_TO, 'User', 'winner_id'),
			'points'=>array(self::HAS_MANY, 'LiveH2HPoint', 'set_id', 'order'=>'point_number ASC'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'match_id' => 'Match',
			'set_number' => 'Set Number',
			'competitor1_points' => 'Competitor 1 Points',
			'competitor2_points' => 'Competitor 2 Points',
			'winner_id' => 'Winner',
			'status' => 'Status',
			'operator_id' => 'Operator',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
		);
	}

	/**
	 * Returns the static model of the specified AR class.
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function getBroadcastAttributes() {
		return array(
			'i'=>$this->id,
			'm'=>$this->match_id,
			'sn'=>$this->set_number,
			'p1'=>$this->competitor1_points,
			'p2'=>$this->competitor2_points,
			'w'=>$this->winner_id,
			'st'=>$this->status,
		);
	}

	/**
	 * Recalculate set points from all points (for editing support)
	 */
	public function recalculatePoints() {
		$match = $this->match;
		if (!$match) {
			return;
		}
		$oldWinnerId = $this->winner_id;
		$oldStatus = $this->status;

		$p1 = 0;
		$p2 = 0;
		$points = LiveH2HPoint::model()->findAllByAttributes(array('set_id' => $this->id), array('order' => 'point_number ASC'));
		foreach ($points as $point) {
			$winnerId = $point->determineWinner();
			if ($winnerId == $match->competitor1_id) {
				$p1++;
			} elseif ($winnerId == $match->competitor2_id) {
				$p2++;
			}
		}

		$this->competitor1_points = $p1;
		$this->competitor2_points = $p2;
		$this->winner_id = null;
		$this->status = self::STATUS_IN_PROGRESS;

		if ($oldStatus == self::STATUS_FINISHED && $oldWinnerId) {
			if ($oldWinnerId == $match->competitor1_id) {
				$match->competitor1_sets_won = max(0, $match->competitor1_sets_won - 1);
			} elseif ($oldWinnerId == $match->competitor2_id) {
				$match->competitor2_sets_won = max(0, $match->competitor2_sets_won - 1);
			}
			$match->save();
		}

		$this->save();
		$this->checkSetFinished();
	}

	public function checkSetFinished() {
		$match = $this->match;
		if (!$match) {
			return false;
		}
		$h2hRound = $match->h2hRound;
		if (!$h2hRound) {
			return false;
		}
		$pointsToWin = $h2hRound->points_to_win_set;

		// Check if someone has won 3 points
		if ($this->competitor1_points >= $pointsToWin) {
			$this->winner_id = $match->competitor1_id;
			$this->status = self::STATUS_FINISHED;
			// Update match sets won
			$match->competitor1_sets_won++;
			$match->save();
			return true;
		}
		if ($this->competitor2_points >= $pointsToWin) {
			$this->winner_id = $match->competitor2_id;
			$this->status = self::STATUS_FINISHED;
			// Update match sets won
			$match->competitor2_sets_won++;
			$match->save();
			return true;
		}

		// Check for 7 points tiebreaker
		$totalPoints = $this->competitor1_points + $this->competitor2_points;
		if ($totalPoints >= 7) {
			// Determine winner based on tiebreaker rules
			if ($this->competitor1_points > $this->competitor2_points) {
				$this->winner_id = $match->competitor1_id;
				$this->status = self::STATUS_FINISHED;
				$match->competitor1_sets_won++;
				$match->save();
				return true;
			} elseif ($this->competitor2_points > $this->competitor1_points) {
				$this->winner_id = $match->competitor2_id;
				$this->status = self::STATUS_FINISHED;
				$match->competitor2_sets_won++;
				$match->save();
				return true;
			} else {
				// Tie at 7 points - need to check best single result
				// This logic should be implemented based on I3e regulation
				// For now, we'll mark as finished and let the operator decide
			}
		}

		return false;
	}
}
