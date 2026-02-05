<?php

/**
 * This is the model class for table "live_h2h_point".
 */
class LiveH2HPoint extends ActiveRecord {

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_h2h_point';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('set_id, match_id, point_number', 'required'),
			array('point_number', 'numerical', 'integerOnly'=>true),
			array('competitor1_result, competitor2_result', 'numerical'),
			array('set_id, match_id, competitor1_id, competitor2_id, winner_id, operator_id, create_time, update_time', 'length', 'max'=>10),
			array('id, set_id, match_id, point_number, competitor1_id, competitor2_id, competitor1_result, competitor2_result, winner_id, operator_id, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array(
			'set'=>array(self::BELONGS_TO, 'LiveH2HSet', 'set_id'),
			'match'=>array(self::BELONGS_TO, 'LiveH2HMatch', 'match_id'),
			'competitor1'=>array(self::BELONGS_TO, 'User', 'competitor1_id'),
			'competitor2'=>array(self::BELONGS_TO, 'User', 'competitor2_id'),
			'winner'=>array(self::BELONGS_TO, 'User', 'winner_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'set_id' => 'Set',
			'match_id' => 'Match',
			'point_number' => 'Point Number',
			'competitor1_id' => 'Competitor 1',
			'competitor2_id' => 'Competitor 2',
			'competitor1_result' => 'Competitor 1 Result',
			'competitor2_result' => 'Competitor 2 Result',
			'winner_id' => 'Winner',
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
			's'=>$this->set_id,
			'm'=>$this->match_id,
			'pn'=>$this->point_number,
			'c1'=>array(
				'id'=>$this->competitor1_id,
				'r'=>$this->competitor1_result,
			),
			'c2'=>array(
				'id'=>$this->competitor2_id,
				'r'=>$this->competitor2_result,
			),
			'w'=>$this->winner_id,
		);
	}

	/**
	 * Determine winner of the point based on results
	 * Returns competitor_id of winner, or 0 if tie
	 */
	public function determineWinner() {
		$r1 = $this->competitor1_result;
		$r2 = $this->competitor2_result;

		// DNF and DNS are worst possible results
		if ($r1 == -1 || $r1 == -2) {
			// competitor1 has DNF/DNS
			if ($r2 > 0) {
				return $this->competitor2_id; // competitor2 wins
			}
			return 0; // Both have DNF/DNS, no winner
		}
		if ($r2 == -1 || $r2 == -2) {
			// competitor2 has DNF/DNS
			if ($r1 > 0) {
				return $this->competitor1_id; // competitor1 wins
			}
			return 0; // Both have DNF/DNS, no winner
		}

		// Both have valid times
		if ($r1 > 0 && $r2 > 0) {
			if ($r1 < $r2) {
				return $this->competitor1_id;
			} elseif ($r2 < $r1) {
				return $this->competitor2_id;
			} else {
				return 0; // Tie
			}
		}

		return 0; // No winner
	}

	/**
	 * Update point winner and set points
	 */
	public function updatePointWinner() {
		$winnerId = $this->determineWinner();
		$this->winner_id = $winnerId;

		if ($this->save()) {
			// Update set points
			$set = $this->set;
			if ($set) {
				if ($winnerId == $this->competitor1_id) {
					$set->competitor1_points++;
				} elseif ($winnerId == $this->competitor2_id) {
					$set->competitor2_points++;
				}
				$set->save();

				// Check if set is finished
				$set->checkSetFinished();
			}
		}
	}
}
