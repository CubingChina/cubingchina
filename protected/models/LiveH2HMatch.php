<?php

/**
 * This is the model class for table "live_h2h_match".
 */
class LiveH2HMatch extends ActiveRecord {

	const STATUS_NOT_STARTED = 0;
	const STATUS_IN_PROGRESS = 1;
	const STATUS_FINISHED = 2;

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'live_h2h_match';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		return array(
			array('h2h_round_id, competition_id, event, round', 'required'),
			array('match_number, competitor1_seed, competitor2_seed, competitor1_sets_won, competitor2_sets_won, sets_to_win, status', 'numerical', 'integerOnly'=>true),
			array('sets_to_win', 'in', 'range'=>array(0, 1, 2, 3)),
			array('competition_id, competitor1_id, competitor2_id, winner_id, operator_id, create_time, update_time', 'length', 'max'=>10),
			array('event', 'length', 'max'=>32),
			array('round', 'length', 'max'=>1),
			array('stage', 'length', 'max'=>20),
			array('id, h2h_round_id, competition_id, event, round, stage, match_number, competitor1_id, competitor1_seed, competitor2_id, competitor2_seed, competitor1_sets_won, competitor2_sets_won, sets_to_win, winner_id, status, operator_id, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return array(
			'h2hRound'=>array(self::BELONGS_TO, 'LiveH2HRound', 'h2h_round_id'),
			'competition'=>array(self::BELONGS_TO, 'Competition', 'competition_id'),
			'competitor1'=>array(self::BELONGS_TO, 'User', 'competitor1_id'),
			'competitor2'=>array(self::BELONGS_TO, 'User', 'competitor2_id'),
			'winner'=>array(self::BELONGS_TO, 'User', 'winner_id'),
			'sets'=>array(self::HAS_MANY, 'LiveH2HSet', 'match_id', 'order'=>'set_number ASC'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => 'ID',
			'h2h_round_id' => 'H2H Round',
			'competition_id' => 'Competition',
			'event' => 'Event',
			'round' => 'Round',
			'stage' => 'Stage',
			'match_number' => 'Match Number',
			'competitor1_id' => 'Competitor 1',
			'competitor1_seed' => 'Competitor 1 Seed',
			'competitor2_id' => 'Competitor 2',
			'competitor2_seed' => 'Competitor 2 Seed',
			'competitor1_sets_won' => 'Competitor 1 Sets Won',
			'competitor2_sets_won' => 'Competitor 2 Sets Won',
			'sets_to_win' => 'Sets To Win',
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
		$c1 = array(
			'id'=>$this->competitor1_id,
			'seed'=>$this->competitor1_seed,
		);
		$c2 = array(
			'id'=>$this->competitor2_id,
			'seed'=>$this->competitor2_seed,
		);
		// Add user info if competitor exists
		if ($this->competitor1 !== null) {
			$c1['name'] = $this->competitor1->getCompetitionName();
		}
		if ($this->competitor2 !== null) {
			$c2['name'] = $this->competitor2->getCompetitionName();
		}
		return array(
			'i'=>$this->id,
			'hr'=>$this->h2h_round_id,
			'c'=>$this->competition_id,
			'e'=>$this->event,
			'r'=>$this->round,
			's'=>$this->stage,
			'mn'=>$this->match_number,
			'c1'=>$c1,
			'c2'=>$c2,
			's1'=>$this->competitor1_sets_won,
			's2'=>$this->competitor2_sets_won,
			'stw'=>$this->sets_to_win,
			'w'=>$this->winner_id,
			'st'=>$this->status,
		);
	}

	public function checkMatchFinished() {
		$h2hRound = $this->h2hRound;
		if (!$h2hRound) {
			return false;
		}
		// Use match's sets_to_win if set (non-zero), otherwise use round's sets_to_win
		$setsToWin = ($this->sets_to_win > 0) ? $this->sets_to_win : $h2hRound->sets_to_win;
		if ($this->competitor1_sets_won >= $setsToWin) {
			$this->winner_id = $this->competitor1_id;
			$this->status = self::STATUS_FINISHED;
			return true;
		}
		if ($this->competitor2_sets_won >= $setsToWin) {
			$this->winner_id = $this->competitor2_id;
			$this->status = self::STATUS_FINISHED;
			return true;
		}
		return false;
	}
}
