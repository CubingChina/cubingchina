<?php
use Ramsey\Uuid\Uuid;

/**
 * This is the model class for table "registration".
 *
 * The followings are the available columns in table 'registration':
 * @property string $id
 * @property string $competition_id
 * @property string $user_id
 * @property string $events
 * @property integer $f
 * @property string $comments
 * @property string $date
 * @property integer $status
 */
class Registration extends ActiveRecord {
	public $number;
	public $best = -1;
	public $pos = -1;
	public $isStaff = false;
	public $repeatPassportNumber;
	public $coefficients = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
	public $codes = array(1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2);

	private $_location;
	private $_events;

	public static $sortByUserAttribute = false;
	public static $sortByEvent = false;
	public static $sortAttribute = 'number';
	public static $sortDesc = false;
	public static $showPending = false;

	const UNPAID = 0;
	const PAID = 1;

	const AVATAR_TYPE_SUBMMITED = 0;
	const AVATAR_TYPE_NOW = 1;

	const STATUS_PENDING = 0;
	const STATUS_ACCEPTED = 1;
	const STATUS_CANCELLED = 2;
	const STATUS_CANCELLED_TIME_END = 3;
	const STATUS_DISQUALIFIED = 4;
	const STATUS_WAITING = 5;

	const T_SHIRT_SIZE_NONE = 0;
	const T_SHIRT_SIZE_XS = 1;
	const T_SHIRT_SIZE_S = 2;
	const T_SHIRT_SIZE_M = 3;
	const T_SHIRT_SIZE_L = 4;
	const T_SHIRT_SIZE_XL = 5;
	const T_SHIRT_SIZE_XXL = 6;
	const T_SHIRT_SIZE_XXXL = 7;

	const STAFF_TYPE_NONE = 0;
	const STAFF_TYPE_JUDGE = 1;
	const STAFF_TYPE_SCRAMBLER = 2;
	const STAFF_TYPE_SCORE_TAKER = 3;
	const STAFF_TYPE_OTHER = 4;

	public static function getDailyRegistration() {
		$data = Yii::app()->db->createCommand()
			->select('FROM_UNIXTIME(MIN(r.date), "%Y-%m-%d") as day, COUNT(1) AS registration')
			->from('registration r')
			->leftJoin('user u', 'r.user_id=u.id')
			->where('u.status=' . User::STATUS_NORMAL . ' AND r.date>=' . strtotime('today 180 days ago'))
			->group('FROM_UNIXTIME(r.date, "%Y-%m-%d")')
			->queryAll();
		return $data;
	}

	public static function getHourlyRegistration() {
		$data = Yii::app()->db->createCommand()
			->select('FROM_UNIXTIME(MIN(r.date), "%k") as hour, COUNT(1) AS registration')
			->from('registration r')
			->leftJoin('user u', 'r.user_id=u.id')
			->where('u.status=' . User::STATUS_NORMAL)
			->group('FROM_UNIXTIME(r.date, "%k")')
			->queryAll();
		return $data;
	}

	public static function getAvatarTypes($competition) {
		switch ($competition->require_avatar) {
			case Competition::REQUIRE_AVATAR_ACA:
				return array(
					self::AVATAR_TYPE_SUBMMITED=>Yii::t('Registration', 'I have submitted my photo to ACA before and I do not need to change my photo.'),
					self::AVATAR_TYPE_NOW=>Yii::t('Registration', 'I have submitted my photo to ACA before and now I want to change it. / I have not submitted my photo before.'),
				);
			default:
				return array();
		}
	}

	public static function getTShirtSizes() {
		return [
			// self::T_SHIRT_SIZE_NONE=>'NONE',
			// self::T_SHIRT_SIZE_XS=>'XS',
			self::T_SHIRT_SIZE_S=>'S',
			self::T_SHIRT_SIZE_M=>'M',
			self::T_SHIRT_SIZE_L=>'L',
			self::T_SHIRT_SIZE_XL=>'XL',
			self::T_SHIRT_SIZE_XXL=>'XXL',
			self::T_SHIRT_SIZE_XXXL=>'XXXL',
		];
	}

	public static function getStaffTypes() {
		return [
			self::STAFF_TYPE_NONE=>Yii::t('common', 'I want to focus on competition.'),
			self::STAFF_TYPE_JUDGE=>Yii::t('common', 'Judge'),
			self::STAFF_TYPE_SCRAMBLER=>Yii::t('common', 'Scrambler'),
			self::STAFF_TYPE_SCORE_TAKER=>Yii::t('common', 'Score Taker'),
			self::STAFF_TYPE_OTHER=>Yii::t('common', 'Other'),
		];
	}

	public static function getAllStatus() {
		return array(
			self::STATUS_PENDING=>Yii::t('common', 'Pending'),
			self::STATUS_ACCEPTED=>Yii::t('common', 'Accepted'),
			self::STATUS_CANCELLED=>Yii::t('common', 'Cancelled'),
			self::STATUS_CANCELLED_TIME_END=>Yii::t('common', 'Cancelled'),
			self::STATUS_DISQUALIFIED=>Yii::t('common', 'Disqualified'),
			self::STATUS_WAITING=>Yii::t('common', 'Waiting'),
		);
	}

	public static function getUserRegistration($competition_id, $userId) {
		return self::model()->findByAttributes(array(
			'competition_id'=>$competition_id,
			'user_id'=>$userId,
		));
	}

	public static function getRegistrations($competition, $all = false, $order = 'date') {
		$attributes = array(
			'competition_id'=>$competition->id,
		);
		if (!$all) {
			$attributes['status'] = self::STATUS_ACCEPTED;
		}
		if (!in_array($order, array('date', 'user.name'))) {
			$order = 'date';
		}
		$registrations = self::model()->with(array(
			'user'=>array(
				'condition'=>'user.status=' . User::STATUS_NORMAL,
			),
			'user.country',
		))->findAllByAttributes($attributes, array(
			'order'=>'t.accept_time>0 DESC, t.accept_time, t.id',
		));
		$registrations = array_filter($registrations, function($registration) {
			return $registration->location->status == CompetitionLocation::YES;
		});
		//计算序号
		$number = 1;
		foreach ($registrations as $registration) {
			if ($registration->isAccepted()) {
				$registration->number = $number++;
			}
		}
		usort($registrations, function ($rA, $rB) use ($order) {
			if ($rA->number === $rB->number || ($rA->number !== null && $rB->number !== null)) {
				switch ($order) {
					case 'user.name':
						$temp = strcmp($rA->user->getCompetitionName(), $rB->user->getCompetitionName());
					case 'date':
					default:
						if ($rA->number === null) {
							$temp = $rA->id - $rB->id;
						} else {
							$temp = $rA->accept_time - $rB->accept_time;
						}
				}
				if ($temp == 0) {
					$temp = $rA->id - $rB->id;
				}
				return $temp;
			}
			if ($rA->number === null) {
				return 1;
			}
			if ($rB->number === null) {
				return -1;
			}
			return $rA->id - $rB->id;
		});
		return $registrations;
	}

	public function getStatusText() {
		$status = self::getAllStatus();
		return isset($status[$this->status]) ? $status[$this->status] : $this->status;
	}

	public function getSigninStatusText() {
		$status = self::getYesOrNo();
		return isset($status[$this->signed_in]) ? $status[$this->signed_in] : $this->signed_in;
	}

	public function getPassportTypeText() {
		$types = User::getPassportTypes();
		$text = $types[$this->entourage_passport_type] ?? '';
		if ($this->entourage_passport_type == User::PASSPORT_TYPE_OTHER) {
			$text .= "($this->entourage_passport_name)";
		}
		return $text;
	}

	public function getTShirtSizeText() {
		$sizes = self::getTShirtSizes();
		return $sizes[$this->t_shirt_size] ?? '';
	}

	public function getStaffTypeText() {
		$types = self::getStaffTypes();
		return $types[$this->staff_type] ?? '';
	}

	public function getDataForSignin() {
		$events = array_map(function($registrationEvent) {
			return Events::getFullEventName($registrationEvent->event);
		}, array_filter($this->allEvents, function($registrationEvent) {
			return $registrationEvent->isAccepted();
		}));
		return [
			'type'=>'registration',
			'title'=>Yii::t('Competition', 'Competitor'),
			'id'=>$this->id,
			'number'=>$this->getUserNumber(),
			'passport'=>$this->user->passport_number,
			'user'=>[
				'name'=>$this->user->getCompetitionName(),
				'wcaid'=>$this->user->wcaid,
				'country'=>$this->user->country ? Yii::t('Region', $this->user->country->name) : '',
				'birthday'=>$this->user->birthday ? date('Y-m-d', $this->user->birthday) : '',
				'gender'=>$this->user->getGenderText(),
			],
			'events'=>implode('、', $events),
			'fee'=>$this->getTotalFee(),
			'paid'=>!!$this->paid,
			'signed_in'=>!!$this->signed_in,
			'signed_date'=>date('Y-m-d H:i:s', $this->signed_date),
			'has_entourage'=>!!$this->has_entourage,
			'entourage_name'=>$this->entourage_name,
			'entourage_passport_type_text'=>$this->getPassportTypeText(),
			'entourage_passport_number'=>$this->entourage_passport_number,
			't_shirt_size'=>$this->getTShirtSizeText(),
			'staff_type'=>$this->competition->staff ? $this->getStaffTypeText() : '',
		];
	}

	public function isPending() {
		return $this->status == self::STATUS_PENDING;
	}

	public function isAccepted() {
		return $this->status == self::STATUS_ACCEPTED;
	}

	public function isCancelled() {
		return $this->status == self::STATUS_CANCELLED
			|| $this->status == self::STATUS_CANCELLED_TIME_END;
	}

	public function isDisqualified() {
		return $this->status == self::STATUS_DISQUALIFIED;
	}

	public function isCancellable() {
		$competition = $this->competition;
		return time() < $competition->cancellation_end_time && $this->isAccepted() || $this->isWaiting();
	}

	public function isEditable() {
		$competition = $this->competition;
		$payment = $this->getUnpaidPayment();
		return !$this->isCancelled() && !$competition->isRegistrationEnded() && $competition->allow_change_event
			&& ($payment === null || !$payment->isLocked());
	}

	public function isWaiting() {
		return $this->status == self::STATUS_WAITING;
	}

	public function isPaid() {
		return $this->paid == self::PAID;
	}

	public function isLocked() {
		$payment = $this->getUnpaidPayment();
		return $payment !== null && $payment->isLocked();
	}

	public function isAcceptedOrWaiting() {
		return $this->isAccepted() || $this->isWaiting();
	}

	public function accept($pay = null, $forceAccept = false) {
		if ($this->isCancelled()) {
			return false;
		}
		$hasBeenAccepted = $this->isAccepted();
		$this->status = self::STATUS_ACCEPTED;
		if ($this->accept_time == 0) {
			$this->accept_time = time();
		}
		if (!$hasBeenAccepted && !$forceAccept) {
			if ($this->competition->isRegistrationFull()) {
				$this->status = self::STATUS_WAITING;
			}
			if ($this->competition->isLimitByEvent()) {
				// set the status to waiting
				$this->status = self::STATUS_WAITING;
				foreach ($this->allEvents as $registrationEvent) {
					if ($registrationEvent->isCancelled()) {
						continue;
					}
					// if any event is not full, the status should be accepted
					if (!$this->competition->isEventRegistrationFull($this->competition->associatedEvents[$registrationEvent->event])) {
						$this->status = self::STATUS_ACCEPTED;
						break;
					}
				}
			}
			if ($this->competition->series) {
				$otherRegistration = $this->user->getOtherSeriesRegistration($this->competition);
				if ($otherRegistration) {
					$this->status = self::STATUS_WAITING;
				}
			}
			// check for multi location competitions
			if (count($this->competition->location) > 1) {
				$location = $this->location;
				if ($location->competitor_limit > 0 && $location->competitor_limit <= $this->getLocationAcceptedCount()) {
					$this->status = self::STATUS_WAITING;
				}
			}
			// check for newcomer comps
			if ($this->competition->newcomer) {
				$this->status = self::STATUS_WAITING;
			}
		}
		$this->save();
		if ($this->competition->isRegistrationFull()) {
			if (!$this->competition->has_been_full) {
				$this->competition->has_been_full = Competition::YES;
				$this->competition->formatDate();
				$this->competition->save();
			}
		}
		if ($this->isAccepted()) {
			$this->updateEventsStatus($pay);
			if ($this->competition->show_qrcode && !$hasBeenAccepted) {
				Yii::app()->mailer->sendRegistrationAcception($this);
			}
		} elseif ($this->isWaiting()) {
			$this->updateEventsStatus($pay);
		}
		if ($pay === null && $this->getUnpaidPayment() !== null) {
			$payment = $this->getUnpaidPayment();
			$payment->cancel();
		}
	}

	public function acceptForNewcomer() {
		// record the accept time in case of wrongly accepted
		$this->cancel_time = $this->accept_time;
		$this->accept_time = 0;
		// force accept the registration
		$this->accept(null, true);
	}

	public function cancelForNewcomer() {
		$this->accept_time = $this->cancel_time;
		$this->status = self::STATUS_CANCELLED;
		$this->save();
	}

	public function updateEventsStatus($pay = null) {
		$registrationEventIds = $pay === null ? [] : array_map(function($payEvent) {
			return $payEvent->registration_event_id;
		}, $pay->events);
		$isAccepted = $this->isAccepted();
		$competition = $this->competition;
		foreach ($this->allEvents as $registrationEvent) {
			if ($registrationEvent->isCancelled()) {
				continue;
			}
			if ($pay === null || in_array($registrationEvent->id, $registrationEventIds)) {
				$registrationEvent->paid = $this->paid;
				if ($isAccepted) {
					if ($competition->isLimitByEvent()) {
						if ($competition->isEventRegistrationFull($competition->associatedEvents[$registrationEvent->event])) {
							$registrationEvent->status = RegistrationEvent::STATUS_WAITING;
							if ($registrationEvent->accept_time == 0) {
								$registrationEvent->accept_time = time();
							}
							$registrationEvent->save();
						} else {
							$registrationEvent->accept();
						}
					} else {
						$registrationEvent->accept();
					}
				} elseif ($this->isWaiting()) {
					$registrationEvent->status = RegistrationEvent::STATUS_WAITING;
					if ($registrationEvent->accept_time == 0) {
						$registrationEvent->accept_time = time();
					}
					$registrationEvent->save();
				} else {
					$registrationEvent->status == RegistrationEvent::STATUS_PENDING;
					$registrationEvent->save();
				}
			}
		}
	}

	public function acceptNext() {
		$competition = $this->competition;
		// check for event limit competitions
		if ($competition->isLimitByEvent()) {
			foreach ($this->getAcceptedEvents() as $event) {
				$nextRegistrationEvent = RegistrationEvent::model()->with('registration')->findByAttributes([
					'event'=>$event->event,
					'status'=>RegistrationEvent::STATUS_WAITING,
				], [
					'condition'=>'registration.competition_id=:competition_id AND registration.status IN (:statusAccepted, :statusWaiting)',
					'params'=>[
						':competition_id'=>$this->competition_id,
						':statusAccepted'=>self::STATUS_ACCEPTED,
						':statusWaiting'=>self::STATUS_WAITING,
					],
					'order'=>'t.accept_time ASC, t.id ASC',
				]);
				if ($nextRegistrationEvent) {
					$nextRegistrationEvent->accept();
					if (!$nextRegistrationEvent->registration->isAccepted()) {
						$nextRegistrationEvent->registration->status = self::STATUS_ACCEPTED;
						$nextRegistrationEvent->registration->save();
					}
				}
			}
			return;
		}
		$waitingRegistrations = self::model()->findAllByAttributes([
			'competition_id'=>$this->competition_id,
			'status'=>self::STATUS_WAITING,
		], [
			'order'=>'accept_time ASC, id ASC',
		]);
		// check for multiple location competitions
		if (count($this->competition->location) > 1) {
			$waitingRegistrations = self::model()->findAllByAttributes([
				'competition_id'=>$this->competition_id,
				'location_id'=>$this->location_id,
				'status'=>self::STATUS_WAITING,
			], [
				'order'=>'accept_time ASC, id ASC',
			]);
		}
		$nextRegistration = null;
		if ($competition->series) {
			// check for series competitions
			foreach ($waitingRegistrations as $nextRegistration) {
				$otherRegistration = $nextRegistration->user->getOtherSeriesRegistration($competition);
				if (!$otherRegistration) {
					break;
				}
				$nextRegistration = null;
			}
		} else {
			$nextRegistration = $waitingRegistrations[0];
		}
		if ($nextRegistration) {
			$nextRegistration->accept();
		}
	}

	public function cancel($status = Registration::STATUS_CANCELLED) {
		//calculate refund fee before change status
		$remainedRefundAmount = $this->getRefundAmount();
		if ($this->isWaiting()) {
			$status = self::STATUS_CANCELLED_TIME_END;
		}
		$isAccepted = $this->isAccepted();
		$this->status = $status;
		$this->cancel_time = time();
		if ($this->save()) {
			if ($remainedRefundAmount > 0) {
				$payments = $this->payments;
				usort($payments, function($a, $b) {
					return $a->paid_time - $b->paid_time;
				});
				foreach ($payments as $payment) {
					if ($payment->isPaid()) {
						$paidAmount = $payment->paid_amount;
						$refundAmount = min($paidAmount, $remainedRefundAmount);
						$payment->refund($refundAmount);
						$remainedRefundAmount -= $refundAmount;
						if ($remainedRefundAmount <= 0) {
							break;
						}
					}
				}
			}
			Yii::app()->mailer->sendRegistrationCancellation($this);
			$competition = $this->competition;
			if (!$competition->isRegistrationFull() || $competition->isLimitByEvent()) {
				$this->acceptNext();
			}
			// check for other series competitions' registrations
			if ($isAccepted && $competition->series) {
				$registrations = self::model()->with('competition')->findAllByAttributes([
					'user_id'=>$this->user_id,
					'competition_id'=>array_values(CHtml::listData($competition->series->list, 'competition_id', 'competition_id')),
					'status'=>self::STATUS_WAITING,
				]);
				foreach ($registrations as $registration) {
					if (!$registration->competition->isRegistrationFull()) {
						$registration->accept();
						break;
					}
				}
			}
			return true;
		}
		return false;
	}

	public function resetPayment() {
		$payment = $this->getUnpaidPayment();
		if ($payment === null) {
			return false;
		}
		return $payment->resetOrder();
	}

	public function disqualify() {
		$this->status = self::STATUS_DISQUALIFIED;
		$this->cancel_time = time();
		if ($this->save(false)) {
			Yii::app()->mailer->sendRegistrationDisqualified($this);
			return true;
		}
		return false;
	}

	public function getUnmetEvents() {
		$competition = $this->competition;
		$user = $this->user;
		$results = Results::model()->with('competition')->findAllByAttributes([
			'person_id'=>$user->wcaid,
		], [
			'condition'=>'competition.year<:year
				OR (competition.year=:year AND competition.end_month<:month)
				OR (competition.year=:year AND competition.end_month=:month AND competition.end_day<=:day)',
			'select'=>[
				'event_id',
				'min(CASE WHEN best>0 THEN best ELSE 9999999999 END) AS best',
				'min(CASE WHEN average>0 THEN average ELSE 9999999999 END) AS average',
			],
			'group'=>'event_id',
			'params'=>[
				':year'=>intval(date('Y', $competition->qualifying_end_time)),
				':month'=>intval(date('n', $competition->qualifying_end_time)),
				':day'=>intval(date('j', $competition->qualifying_end_time)),
			],
		]);
		foreach ($results as $result) {
			if ($result->best == 9999999999) {
				continue;
			}
			$rank = new RanksSingle();
			$rank->best = $result->best;
			$rank->average = new RanksAverage();
			$rank->average->best = $result->average;
			$temp[$result->event_id] = $rank;
		}
		$unmetEvents = [];
		foreach ($competition->allEvents as $event) {
			if (!$event->check($temp[$event->event] ?? null)) {
				$unmetEvents[] = $event->event;
			}
		}
		return $unmetEvents;
	}

	public function checkEntourageName() {
		if ($this->has_entourage && empty($this->entourage_name)) {
			$this->addError('entourage_name', Yii::t('yii','{attribute} cannot be blank.', array(
				'{attribute}'=>$this->getAttributeLabel('entourage_name'),
			)));
		}
		if ($this->has_entourage && empty($this->entourage_passport_type)) {
			$this->addError('entourage_passport_type', Yii::t('yii','{attribute} cannot be blank.', array(
				'{attribute}'=>$this->getAttributeLabel('entourage_passport_type'),
			)));
		}
		if ($this->has_entourage && empty($this->entourage_passport_number)) {
			$this->addError('entourage_passport_number', Yii::t('yii','{attribute} cannot be blank.', array(
				'{attribute}'=>$this->getAttributeLabel('entourage_passport_number'),
			)));
		}
	}

	public function checkPassportType() {
		if (!$this->has_entourage) {
			return;
		}
		if ($this->entourage_passport_type == User::PASSPORT_TYPE_OTHER && empty($this->entourage_passport_name)) {
			$this->addError('entourage_passport_name', Yii::t('yii','{attribute} cannot be blank.', array(
				'{attribute}'=>$this->getAttributeLabel('entourage_passport_name'),
			)));
		}
	}

	public function checkPassportNumber() {
		if (!$this->has_entourage) {
			return;
		}
		switch ($this->entourage_passport_type) {
			case User::PASSPORT_TYPE_ID:
				if (!preg_match('|^\d{6}(\d{8})(\d{3})[\dX]$|i', $this->entourage_passport_number, $matches)) {
					$this->addError('entourage_passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				$sum = 0;
				for ($i = 0; $i < 17; $i++) {
					$sum += $this->entourage_passport_number[$i] * $this->coefficients[$i];
				}
				$mod = $sum % 11;
				if (strtoupper($this->entourage_passport_number[17]) != $this->codes[$mod]) {
					$this->addError('entourage_passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				break;
			case User::PASSPORT_TYPE_PASSPORT:
				if (!preg_match('|^\w+$|i', $this->entourage_passport_number, $matches)) {
					$this->addError('entourage_passport_number', Yii::t('common', 'Invalid identity number.'));
					return false;
				}
				break;
		}
		if (!empty($this->entourage_passport_number) && $this->entourage_passport_number != $this->repeatPassportNumber) {
			$this->addError('repeatPassportNumber', Yii::t('common', 'Repeat identity number must be the same as identity number.'));
		}
	}

	public function checkAvatarType() {
		switch ($this->competition->require_avatar) {
			case Competition::REQUIRE_AVATAR_ACA:
				if ($this->avatar_type == self::AVATAR_TYPE_NOW) {
					if ($this->user->avatar == null) {
						$this->avatar_type = null;
						$this->addError('avatar_type', '');
					} else {
						$this->avatar_id = $this->user->avatar_id;
					}
				}
				break;
		}
	}

	public function checkStaffStatement() {
		if ($this->staff_type != self::STAFF_TYPE_NONE && empty($this->staff_statement)) {
			$this->addError('staff_statement', Yii::t('yii','{attribute} cannot be blank.', array(
				'{attribute}'=>$this->getAttributeLabel('staff_statement'),
			)));
		}
	}

	public function getEventString($event, $showPending = false) {
		$registrationEvent = $this->getRegistrationEvent($event);
		if ($registrationEvent === null) {
			return '';
		}
		if ($showPending === false && !$registrationEvent->isAccepted()) {
			return '';
		}
		$str = Events::getEventIcon($event);
		if ($this->best > 0 && self::$sortAttribute === $event && self::$sortDesc !== true) {
			$str = self::$sortDesc === true ? '' : '[' . $this->pos . ']' . $str;
			$str .= Results::formatTime($this->best, $event);
		}
		return $str;
	}

	public function getAcceptedEvents() {
		$competitionEvents = $this->competition->getRegistrationEvents();
		$events = array();
		foreach ($this->allEvents as $registrationEvent) {
			$event = $registrationEvent->event;
			if ($registrationEvent->isAccepted() && isset($competitionEvents[$event])) {
				$events[] = $registrationEvent;
			}
		}
		return $events;
	}

	public function getWaitingEvents() {
		$competitionEvents = $this->competition->getRegistrationEvents();
		$events = array();
		foreach ($this->allEvents as $registrationEvent) {
			$event = $registrationEvent->event;
			if ($registrationEvent->isWaiting() && isset($competitionEvents[$event])) {
				$events[] = $registrationEvent;
			}
		}
		return $events;
	}

	public function getPendingEvents() {
		$competitionEvents = $this->competition->getRegistrationEvents();
		$events = array();
		foreach ($this->allEvents as $registrationEvent) {
			$event = $registrationEvent->event;
			if ($registrationEvent->isPending() && isset($competitionEvents[$event])) {
				$events[] = $registrationEvent;
			}
		}
		return $events;
	}

	public function getDisqualifiedEvents() {
		$competitionEvents = $this->competition->getRegistrationEvents();
		$events = array();
		foreach ($this->allEvents as $registrationEvent) {
			$event = $registrationEvent->event;
			if ($registrationEvent->isDisqualified() && isset($competitionEvents[$event])) {
				$events[] = $registrationEvent;
			}
		}
		return $events;
	}

	/**
	 * Accepted or Pending or Disqualified
	 */
	public function getAPDEvents() {
		switch (true) {
			case $this->isPending():
				return $this->getPendingEvents();
			case $this->isAccepted():
				return $this->getAcceptedEvents();
			case $this->isDisqualified():
				return $this->getDisqualifiedEvents();
		}
	}

	public function getEditableEvents() {
		$events = $this->competition->getRegistrationEvents();
		foreach ($this->allEvents as $registrationEvent) {
			if ($registrationEvent->isAccepted() || $registrationEvent->isWaiting()) {
				unset($events[$registrationEvent->event]);
			}
		}
		return $events;
	}

	public function getRegistrationFee() {
		$fee = $this->getFeeInfo();
		if ($this->isPaid() && $fee > 0) {
			$fee .= Yii::t('common', ' (paid)');
		}
		return $fee;
	}

	public function getTotalFee($recalculate = false) {
		// if (empty($this->events)) {
		// 	return 0;
		// }
		if (($this->isAccepted() || $this->isWaiting() || $this->paid) && !$recalculate) {
			return $this->total_fee;
		}
		$competition = $this->competition;
		if ($competition->multi_countries && $this->location->country_id == 1 && $this->location->fee > 0) {
			return $this->location->fee;
		}
		$competitionEvents = $competition->associatedEvents;
		$fees = array();
		$multiple = $competition->second_stage_date <= time() && $competition->second_stage_all;
		foreach ($this->allEvents as $registrationEvent) {
			$event = $registrationEvent->event;
			if (isset($competitionEvents[$event]) && !$registrationEvent->isCancelled()) {
				$fees[] = $competition->getEventFee($event, $competition->calculateStage($registrationEvent->accept_time));
			}
		}
		$entourageFee = $this->has_entourage ? $competition->entourage_fee : 0;
		return $competition->getEventFee(Competition::EVENT_FEE_ENTRY, null, $this->location->fee)  + $entourageFee + array_sum($fees);
	}

	public function getFeeInfo() {
		if ($this->location && $this->location->country_id > 1) {
			return $this->location->fee;
		}
		return Html::fontAwesome('rmb') . $this->getTotalFee();
	}

	public function getPendingAmount() {
		$fee = 0;
		foreach ($this->allEvents AS $registrationEvent) {
			if ($registrationEvent->isPending() && !$registrationEvent->isPaid()) {
				$fee += $registrationEvent->fee;
			}
		}
		if (!$this->isAcceptedOrWaiting()) {
			$competition = $this->competition;
			$entryFee = $competition->entry_fee;
			if ($competition->multi_countries) {
				$entryFee = $this->location->fee;
			}
			$fee += $competition->getEventFee(Competition::EVENT_FEE_ENTRY, null, $entryFee);
		}
		return $fee * 100;
	}

	public function getPendingFee() {
		return Html::fontAwesome('rmb') . number_format($this->getPendingAmount() / 100, 2, '.', '');
	}

	public function getPaidAmount() {
		$amount = 0;
		foreach ($this->payments as $payment) {
			if ($payment->isPaid()) {
				$amount += $payment->paid_amount;
			}
		}
		return $amount;
	}

	public function getPaidFee() {
		return number_format($this->getPaidAmount() / 100, 2, '.', '');
	}

	public function getRefundPercent() {
		//候补列表的直接全额退款
		if ($this->isWaiting() || $this->status == self::STATUS_CANCELLED_TIME_END) {
			return 1;
		}
		//被资格线清掉的，就是0
		if ($this->status == self::STATUS_DISQUALIFIED) {
			return 0;
		}
		switch ($this->competition->refund_type) {
			case Competition::REFUND_TYPE_50_PERCENT:
			case Competition::REFUND_TYPE_100_PERCENT:
				$percent = intval($this->competition->refund_type);
				return $percent / 100;
			default:
				return 0;
		}
	}

	public function getRefundAmount() {
		$competition = $this->competition;
		$refundPercent = $this->getRefundPercent();
		$refundAmount = $this->getPaidAmount() * $refundPercent;
		// check if any waiting events
		if ($competition->isLimitByEvent()) {
			foreach ($this->getWaitingEvents() as $registrationEvent) {
				$refundAmount += (1 - $refundPercent) * $registrationEvent->fee * 100;
			}
		}
		return $refundAmount;
	}

	public function getRefundFee() {
		return number_format($this->getRefundAmount() / 100, 2, '.', '');
	}

	public function getPayButton($checkOnlinePay = true) {
		$totalFee = $this->getTotalFee();
		if ($totalFee > 0 && $this->isPaid()) {
			return CHtml::tag('button', array(
				'class'=>'btn btn-xs btn-disabled',
			), Yii::t('common', 'Paid'));
		}
		if ($this->payable) {
			return CHtml::link(Yii::t('common', 'Pay'), $this->getPayUrl(), array(
				'class'=>'btn btn-xs btn-theme',
			));
		}
		return '';
	}

	public function getPayUrl() {
		return $this->competition->getUrl('registration');
	}

	public function getQRCodeUrl() {
		if ($this->code == '') {
			$this->code = substr(sprintf('registration-%s-%s', Uuid::uuid1(), Uuid::uuid4()), 0, 64);
			$this->save();
		}
		return CHtml::normalizeUrl(array(
			'/qrCode/signin',
			'code'=>$this->code,
		));
	}

	public function getLocation() {
		if ($this->_location === null) {
			$this->_location = CompetitionLocation::model()->with('province', 'city')->findByAttributes(array(
				'competition_id'=>$this->competition_id,
				'location_id'=>intval($this->location_id),
			));
		}
		return $this->_location;
	}

	public function getLocationAcceptedCount() {
		$location = $this->location;
		$acceptedCount = self::model()->countByAttributes(array(
			'competition_id'=>$this->competition_id,
			'location_id'=>$location->location_id,
			'status'=>self::STATUS_ACCEPTED,
		));
		return $acceptedCount;
	}

	public function getEvents() {
		if ($this->_events === null) {
			$this->_events = array_map(function($registrationEvent) {
				return $registrationEvent->event;
			}, array_filter($this->allEvents, function($registrationEvent) {
				return !$registrationEvent->isCancelled();
			}));
		}
		return $this->_events;
	}

	public function setEvents($events) {
		$this->_events = $events;
	}

	public function getRegistrationEvent($event) {
		foreach ($this->allEvents as $registrationEvent) {
			if ("$event" === $registrationEvent->event && !$registrationEvent->isCancelled()) {
				return $registrationEvent;
			}
		}
	}

	public function hasRegistered($event, $showPending = false) {
		$registrationEvent = $this->getRegistrationEvent($event);
		return $registrationEvent !== null && ($showPending || $registrationEvent->isAccepted());
	}

	public function hasRegisteredOneOf($events, $showPending = false) {
		foreach ($events as $event) {
			if ($this->hasRegistered($event)) {
				return true;
			}
		}
		return false;
	}

	public function getNoticeColumns($model) {
		if ($this->competition === null) {
			$columns = array();
		} else {
			$columns = $this->competition->getEventsColumns(true);
		}
		$modelName = get_class($model);
		$userLink = Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR)
			? 'CHtml::link($data->user->getCompetitionName(), array("/board/user/edit", "id"=>$data->user_id))'
			: '$data->user->getWcaLink()';
		$columns = array_merge(array(
			array(
				'name'=>'email',
				'header'=>Yii::t('common', 'Email'),
				'headerHtmlOptions'=>array(
					'class'=>'header-email',
				),
				'type'=>'raw',
				'value'=>"CHtml::label(CHtml::checkBox('{$modelName}[competitors][]', \$data->isAccepted(), array(
					'class'=>implode(' ', array_map(function(\$a) {
						return 'event-' . \$a;
					}, \$data->events)) . ' competitor',
					'value'=>\$data->user->email,
					'data-accepted'=>intval(\$data->isAccepted()),
					'data-country-id'=>\$data->user->country_id,
					'data-staff'=>\$data->staff_type,
				)) . ' ' . \$data->user->email, false, array(
					'class'=>'checkbox',
				))",
			),
		), $columns);
		return $columns;
	}

	public function getAdminColumns() {
		if ($this->competition === null) {
			$columns = array();
		} else {
			$columns = array_slice($this->competition->getEventsColumns(true, true), 0);
			array_splice($columns, 4, 0, array(
				array(
					'name'=>'birthday',
					'header'=>Yii::t('common', 'Birthday'),
					'headerHtmlOptions'=>array(
						'class'=>'header-birthday',
					),
					'type'=>'raw',
					'value'=>'date("Y-m-d", $data->user->birthday)',
				),
			));
			if ($this->competition->require_avatar != Competition::REQUIRE_AVATAR_NONE) {
				array_splice($columns, 5, 0, array(
					array(
						'name'=>'avatar_type',
						'header'=>Yii::t('common', 'Photo'),
						'type'=>'raw',
						'value'=>'$data->getRegistrationAvatar()',
					)
				));
			}
		}
		$isAdmin = Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR);
		$userLink = $isAdmin
			? 'CHtml::link($data->user->getCompetitionName(), array("/board/user/edit", "id"=>$data->user_id))'
			: '$data->user->getWcaLink()';
		$ipColumn = $isAdmin ? array(
			array(
				'name'=>'ip',
				'type'=>'raw',
				'value'=>'$data->getRegIpDisplay()',
			),
		) : array();
		$columns = array_merge(array(
			array(
				'header'=>'操作',
				'headerHtmlOptions'=>array(
					'class'=>'header-operation',
				),
				'type'=>'raw',
				'value'=>'$data->operationButton',
			),
			array(
				'name'=>'user_id',
				'header'=>'用户ID',
				'value'=>'$data->user_id',
			),
		), $columns, array(
			array(
				'name'=>'email',
				'header'=>Yii::t('common', 'Email'),
				'headerHtmlOptions'=>array(
					'class'=>'header-email',
				),
				'type'=>'raw',
				'value'=>'$data->user->getEmailLink()',
			),
			array(
				'name'=>'mobile',
				'header'=>Yii::t('common', 'Mobile Number'),
				'headerHtmlOptions'=>array(
					'class'=>'header-mobile',
				),
				'type'=>'raw',
				'value'=>'$data->user->mobile',
			),
			array(
				'name'=>'fee',
				'header'=>Yii::t('common', 'Fee'),
				'type'=>'raw',
				'value'=>'$data->getFeeInfo() . ($data->isPaid() ? Yii::t("common", " (paid)") : "")',
			),
			array(
				'name'=>'comment',
				'headerHtmlOptions'=>array(
					'class'=>'header-comments',
				),
				'filter'=>false,
				'type'=>'raw',
				'value'=>'$data->getCommentsButton()',
			),
			array(
				'name'=>'date',
				'header'=>Yii::t('Registration', 'Registration Time'),
				'type'=>'raw',
				'value'=>'date("Y-m-d H:i:s", $data->date)',
			),
			array(
				'name'=>'accept_time',
				'header'=>Yii::t('Registration', 'Acception Time'),
				'type'=>'raw',
				'value'=>'$data->accept_time > 0 ? date("Y-m-d H:i:s", $data->accept_time) : "-"',
			),
		), $ipColumn);
		if ($this->competition && $this->competition->fill_passport) {
			$columns = array_merge($columns, [
				[
					'name'=>'passport_type',
					'header'=>Yii::t('Registration', 'Type of Identity'),
					'type'=>'raw',
					'value'=>'$data->user->getPassportTypeText()',
				],
				[
					'name'=>'passport_number',
					'header'=>Yii::t('Registration', 'Identity Number'),
					'type'=>'raw',
					'value'=>'$data->user->passport_number',
				],
			]);
		}
		if ($this->competition && $this->competition->entourage_limit) {
			$columns = array_merge($columns, [
				[
					'name'=>'entourage_name',
					'header'=>'陪同人',
				],
				[
					'name'=>'entourage_passport_type',
					'header'=>'陪同证件类型',
					'value'=>'$data->getPassportTypeText()',
				],
				[
					'name'=>'entourage_passport_number',
					'header'=>'陪同证件号',
				],
			]);
		}
		return $columns;
	}

	public function getRegistrationAvatar() {
		switch ($this->avatar_type) {
			case self::AVATAR_TYPE_SUBMMITED:
				return Yii::t('common', 'Submitted');
			case self::AVATAR_TYPE_NOW:
				return $this->avatar->img;
		}
	}

	public function getCommentsButton() {
		if ($this->comments !== '') {
			return CHtml::tag('button', array(
				'class'=>'btn btn-xs btn-square btn-purple view-comments',
				'data-comments'=>$this->comments,
				'data-toggle'=>'modal',
				'data-target'=>'#comments-modal',
			), '查看');
		}
	}

	public function getOperationButton() {
		$buttons = array();
		$buttons[] = CHtml::link('编辑', array('/board/registration/edit', 'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		$canApprove = Yii::app()->user->checkPermission('caqa') || !$this->competition->isWCACompetition() || $this->user->country_id > 1;
		if ($canApprove) {
			switch ($this->status) {
				case self::STATUS_PENDING:
					$buttons[] = CHtml::tag('button', array(
						'class'=>'btn btn-xs btn-green btn-square toggle',
						'data-id'=>$this->id,
						'data-url'=>CHtml::normalizeUrl(array('/board/registration/toggle')),
						'data-attribute'=>'status',
						'data-value'=>$this->status,
						'data-text'=>'["通过","取消"]',
						'data-name'=>$this->user->getCompetitionName(),
					), '通过');
					break;
				case self::STATUS_ACCEPTED:
					if (!$this->competition->newcomer) {
						$buttons[] = CHtml::tag('button', array(
							'class'=>'btn btn-xs btn-red btn-square toggle',
							'data-id'=>$this->id,
							'data-url'=>CHtml::normalizeUrl(array('/board/registration/toggle')),
							'data-attribute'=>'status',
							'data-value'=>$this->status,
							'data-text'=>'["通过","取消"]',
							'data-name'=>$this->user->getCompetitionName(),
						), '取消');
					}
					break;
			}
		}
		if ($this->status == self::STATUS_WAITING) {
			if ($this->competition->newcomer) {
				$buttons[] = CHtml::tag('button', array(
					'class'=>'btn btn-xs btn-green btn-square toggle',
					'data-id'=>$this->id,
					'data-url'=>CHtml::normalizeUrl(array('/board/registration/acceptNewcomer')),
					'data-attribute'=>'status',
					'data-value'=>$this->status,
					'data-text'=>'["通过","取消"]',
					'data-name'=>$this->user->getCompetitionName(),
				), '通过');
			} else {
				$buttons[] = CHtml::tag('button', [
					'class'=>'btn btn-xs btn-purple btn-square',
				], '候选');
			}
		}
		$buttons[] = CHtml::checkBox('paid', $this->paid == self::PAID, array(
			'class'=>'tips' . ($canApprove ? ' toggle' : ''),
			'disabled'=>!$canApprove,
			'data-toggle'=>'tooltip',
			'data-placement'=>'top',
			'title'=>'是否支付报名费',
			'data-id'=>$this->id,
			'data-url'=>CHtml::normalizeUrl(array('/board/registration/toggle')),
			'data-attribute'=>'paid',
			'data-value'=>$this->paid,
			'data-name'=>$this->user->getCompetitionName(),
		));
		if (Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR) && $this->isCancellable()) {
			$buttons[] = CHtml::link('退赛', ['/board/registration/cancel', 'id'=>$this->id], [
				'class'=>'btn btn-xs btn-orange btn-square',
			]);
		}
		return implode(' ', $buttons);
	}

	public function getSigninOperationButton() {
		$buttons = array();
		switch ($this->signed_in) {
			case self::NO:
				$buttons[] = CHtml::tag('button', array(
					'class'=>'btn btn-xs btn-green btn-square toggle',
					'data-id'=>$this->id,
					'data-url'=>CHtml::normalizeUrl(array('/board/registration/toggle')),
					'data-attribute'=>'signed_in',
					'data-value'=>$this->signed_in,
					'data-text'=>'["签到","取消"]',
					'data-name'=>$this->user->getCompetitionName(),
				), '签到');
				break;
			case self::YES:
				$buttons[] = CHtml::tag('button', array(
					'class'=>'btn btn-xs btn-red btn-square toggle',
					'data-id'=>$this->id,
					'data-url'=>CHtml::normalizeUrl(array('/board/registration/toggle')),
					'data-attribute'=>'signed_in',
					'data-value'=>$this->signed_in,
					'data-text'=>'["签到","取消"]',
					'data-name'=>$this->user->getCompetitionName(),
				), '取消');
				break;
		}
		return implode(' ', $buttons);
	}

	public function getUserNumber() {
		if ($this->isAccepted()) {
			return self::model()->countByAttributes(array(
				'competition_id'=>$this->competition_id,
				'status'=>self::STATUS_ACCEPTED,
			), array(
				'condition'=>'accept_time<:accept_time OR (accept_time=:accept_time AND id<=:id)',
				'params'=>array(
					':accept_time'=>$this->accept_time,
					':id'=>$this->id,
				),
			));
		} else {
			return '-';
		}
	}

	public function getWaitingNumber() {
		if ($this->isWaiting()) {
			return self::model()->countByAttributes(array(
				'competition_id'=>$this->competition_id,
				'status'=>self::STATUS_WAITING,
			), array(
				'condition'=>'accept_time<:accept_time OR (accept_time=:accept_time AND id<:id)',
				'params'=>array(
					':accept_time'=>$this->accept_time,
					':id'=>$this->id,
				),
			));
		} else {
			return '-';
		}
	}

	public function getPayable() {
		if ($this->isCancelled() || $this->location && $this->location->country_id > 1) {
			return false;
		}
		$allPaid = $this->payments === [] ? false : true;
		foreach ($this->payments as $payment) {
			if (!$payment->isPaid()) {
				$allPaid = false;
				break;
			}
		}
		if ($allPaid) {
			return false;
		}
		$totalFee = $this->getTotalFee();
		return $this->competition->isOnlinePay() && !$this->competition->isRegistrationEnded() && $totalFee > 0
			&& (!$this->competition->isRegistrationFull() || $this->isAcceptedOrWaiting())
			&& $this->getPendingAmount() > 0;
	}

	public function getUnpaidPayment() {
		foreach ($this->payments as $payment) {
			if ($payment->isUnpaid()) {
				return $payment;
			}
		}
	}

	public function createPayment() {
		// check if any unpaid payment
		if (($payment = $this->getUnpaidPayment()) === null) {
			$payment = new Pay();
			$payment->user_id = $this->user_id;
			$payment->type = Pay::TYPE_REGISTRATION;
			$payment->type_id = $this->competition_id;
			$payment->sub_type_id = $this->id;
			$payment->order_name = $this->competition->name_zh;
			$payment->save();
		}
		$this->payments = [$payment];
		foreach ($this->allEvents as $registrationEvent) {
			if ($registrationEvent->isPending()) {
				if (($payEvent = $registrationEvent->payEvent) === null) {
					$payEvent = new PayEvent();
				}
				$payEvent->pay_id = $payment->primaryKey;
				$payEvent->registration_event_id = $registrationEvent->id;
				$payEvent->save();
			}
		}
		$payment->reviseAmount();
		return $payment;
	}

	protected function beforeSave() {
		if ($this->old_events == null) {
			$this->old_events = '';
		}
		return parent::beforeSave();
	}

	protected function afterSave() {
		parent::afterSave();
		Yii::app()->cache->delete('competitors_' . $this->competition_id);
	}

	public function updateEvents($events) {
		$allEvents = $this->allEvents;
		foreach ($allEvents as $index => $registrationEvent) {
			if (!in_array($registrationEvent->event, $events)) {
				if (!$registrationEvent->isAccepted() && !$registrationEvent->isWaiting()) {
					$this->removeEvent($registrationEvent);
				}
			} else {
				// set status to pending if it's cancelled
				if ($registrationEvent->isCancelled() && !$registrationEvent->isDisqualified()) {
					$registrationEvent->status = RegistrationEvent::STATUS_PENDING;
					$registrationEvent->save();
				}
				unset($events[array_search($registrationEvent->event, $events)]);
			}
		}
		foreach ($events as $event) {
			$allEvents[] = $this->addEvent($event);
		}
		$this->allEvents = array_values($allEvents);
		$this->_events = null;
		$payment = $this->createPayment();
		if ($this->isAccepted() && $this->getPendingAmount() == 0 && $this->getPendingEvents() != []) {
			$this->accept($payment);
		}
		return true;
	}

	public function addEvent($event, $attributes = []) {
		$registrationEvent = new RegistrationEvent();
		$registrationEvent->registration_id = $this->id;
		$registrationEvent->event = $event;
		$registrationEvent->fee = $attributes['fee'] ?? $this->competition->getEventFee($event);
		$registrationEvent->paid = $attributes['paid'] ?? self::UNPAID;
		$registrationEvent->status = RegistrationEvent::STATUS_PENDING;
		$registrationEvent->accept_time = $attributes['accept_time'] ?? 0;
		$registrationEvent->save();
		return $registrationEvent;
	}

	public function removeEvent($event) {
		if (is_string($event)) {
			$event = $this->associatedEvents[$event] ?? null;
		}
		if ($event === null) {
			return;
		}
		return $event->cancel();
	}

	public function __toJson($full) {
		return [
			'number'=>$this->number,
			'competitor'=>$this->user,
			'events'=>$this->getAcceptedEvents(),
		];
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'registration';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		$rules = array(
			array('competition_id, user_id, events, date', 'required'),
			array('location_id, total_fee, entourage_passport_type, status', 'numerical', 'integerOnly'=>true, 'min'=>0),
			array('competition_id, user_id, date, entourage_passport_number', 'length', 'max'=>20),
			array('events', 'safe'),
			array('comments', 'length', 'max'=>2048),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, location_id, user_id, events, total_fee, comments, date, status', 'safe', 'on'=>'search'),
		);
		if ($this->competition_id > 0) {
			$competition = $this->competition;
			if ($competition->entourage_limit) {
				$rules[] = array('entourage_name', 'checkEntourageName', 'on'=>'register');
				$rules[] = array('entourage_passport_name', 'safe', 'on'=>'register');
				$rules[] = array('entourage_passport_type', 'checkPassportType', 'on'=>'register');
				$rules[] = array('entourage_passport_number', 'checkPassportNumber', 'on'=>'register');
				$rules[] = array('has_entourage', 'required', 'on'=>'register');
				$rules[] = ['repeatPassportNumber, guest_paid', 'safe', 'on'=>'register'];
			}
			if ($competition->require_avatar) {
				$rules[] = array('avatar_type', 'checkAvatarType', 'on'=>'register');
				$rules[] = array('avatar_type', 'required', 'on'=>'register');
			}
			if ($competition->isMultiLocation()) {
				$rules[] = array('location_id', 'required', 'on'=>'register');
			}
			if ($competition->t_shirt) {
				$rules[] = ['t_shirt_size', 'required', 'on'=>'register'];
			}
			if ($competition->staff) {
				$rules[] = ['staff_type', 'required', 'on'=>'register'];
				$rules[] = ['staff_statement', 'checkStaffStatement', 'on'=>'register'];
			}
		}
		return $rules;
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		return [
			'user'=>[self::BELONGS_TO, 'User', 'user_id'],
			'competition'=>[self::BELONGS_TO, 'Competition', 'competition_id'],
			'payments'=>[self::HAS_MANY, 'Pay', 'sub_type_id', 'on'=>'payments.type=' . Pay::TYPE_REGISTRATION],
			'avatar'=>[self::BELONGS_TO, 'UserAvatar', 'avatar_id'],
			'allEvents'=>[self::HAS_MANY, 'RegistrationEvent', 'registration_id', 'order'=>'allEvents.id'],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Registration', 'ID'),
			'competition_id' => Yii::t('Registration', 'Competition'),
			'location_id' => Yii::t('common', 'Competition Site'),
			'user_id' => Yii::t('Registration', 'User'),
			'events' => Yii::t('Registration', 'Events'),
			'comments' => Yii::t('Registration', 'Additional Comments'),
			'total_fee' => Yii::t('Registration', 'Total Fee'),
			'ip' => 'IP',
			'has_entourage' => Yii::t('Registration', 'Do you have any guests joining you at the competition?'),
			'entourage_name' => Yii::t('Registration', 'Name'),
			'entourage_passport_type' => Yii::t('Registration', 'Type of Identity'),
			'entourage_passport_name' => Yii::t('Registration', 'Name of Identity'),
			'entourage_passport_number' => Yii::t('Registration', 'Identity Number'),
			'repeatPassportNumber' => Yii::t('Registration', 'Repeat Identity Number'),
			'date' => Yii::t('Registration', 'Registration Time'),
			'accept_time' => Yii::t('Registration', 'Acception Time'),
			'status' => Yii::t('Registration', 'Status'),
			'fee' => Yii::t('Registration', 'Fee'),
			't_shirt_size' => Yii::t('Registration', 'T-shirt Size'),
			'staff_type' => Yii::t('Registration', 'Staff Type'),
			'staff_statement' => Yii::t('Registration', 'Self Introduction'),
		);
	}

	public function getSort($columns = array()) {
		$sort = array(
			'attributes'=>array(),
			'sortVar'=>'sort',
		);
		foreach ($columns as $column) {
			if (isset($column['name'])) {
				$sort['attributes'][$column['name']] = $column['name'];
			}
		}
		foreach ($this->attributes as $attribute=>$value) {
			$sort['attributes'][$attribute] = $attribute;
		}
		return $sort;
	}

	private function sortRegistration($rA, $rB) {
		$attribute = self::$sortAttribute;
		$temp = 0;
		if ($attribute === 'number') {
			if ($rA->number > 0 && $rB->number === null) {
				$temp = -1;
			} elseif ($rA->number === null && $rB->number > 0) {
				$temp = 1;
			}
		} elseif ($attribute === 'country_id') {
			$temp = $rA->user->country_id - $rB->user->country_id;
			if ($temp == 0) {
				$temp = $rA->user->province_id - $rB->user->province_id;
			}
			if ($temp == 0) {
				$temp = $rA->user->city_id - $rB->user->city_id;
			}
		} elseif (self::$sortByUserAttribute === true) {
			if (is_numeric($rA->user->$attribute)) {
				$temp = $rA->user->$attribute - $rB->user->$attribute;
			} else {
				$temp = strcmp($rA->user->$attribute, $rB->user->$attribute);
			}
		} elseif (self::$sortByEvent === true) {
			$temp = $rB->hasRegistered($attribute, self::$showPending) - $rA->hasRegistered($attribute, self::$showPending);
			if ($temp == 0) {
				if ($rA->best > 0 && $rB->best > 0) {
					$temp = $rA->best - $rB->best;
					if (self::$sortDesc === true) {
						$temp = -$temp;
					}
				} elseif ($rA->best > 0) {
					$temp = -1;
				} elseif ($rB->best > 0) {
					$temp = 1;
				} else {
					$temp = 0;
				}
			}
		} else {
			$temp = $rA->$attribute - $rB->$attribute;
		}
		if ($temp == 0) {
			$temp = $rA->number - $rB->number;
		}
		if ($temp == 0) {
			if ($rA->accept_time > 0 && $rB->accept_time == 0) {
				$temp = -1;
			} elseif ($rA->accept_time == 0 && $rB->accept_time > 0) {
				$temp = 1;
			} elseif ($rA->accept_time > 0 && $rB->accept_time > 0) {
				$temp = $rA->accept_time - $rB->accept_time;
			}
		}
		if ($temp == 0) {
			$temp = $rA->date - $rB->date;
		}
		if ($temp == 0) {
			$temp = $rA->id - $rB->id;
		}
		return $temp;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search(&$columns = array(), $enableCache = true, $pagination = false, $showPending = false) {
		// check if registration started
		$competition = $this->competition;
		if ($competition && !$competition->isRegistrationStarted() && !$showPending) {
			$registrations = [];
		} else {
			// @todo Please modify the following code to remove attributes that should not be searched.
			$cacheKey = 'competitors_' . $this->competition_id;
			$cache = Yii::app()->cache;
			if (!$enableCache || ($registrations = $cache->get($cacheKey)) === false) {
				$criteria = new CDbCriteria;
				$criteria->order = 't.accept_time>0 DESC, t.accept_time, t.id';
				$criteria->with = array('user', 'user.country', 'user.province', 'user.city');
				if (!$competition) {
					array_push($criteria->with, 'competition');
				}

				$criteria->compare('t.id', $this->id,true);
				$criteria->compare('t.competition_id', $this->competition_id);
				$criteria->compare('t.user_id', $this->user_id);
				$criteria->compare('t.comments', $this->comments, true);
				$criteria->compare('t.date', $this->date,true);
				$criteria->compare('t.status', $this->status);
				$criteria->compare('user.status', User::STATUS_NORMAL);
				$registrations = $this->findAll($criteria);
				if ($competition) {
					foreach ($registrations as $registration) {
						$registration->competition = $competition;
					}
				}
				if ($enableCache) {
					$cache->set($cacheKey, $registrations, 86400 * 7);
				}
			}
		}
		$number = 1;
		$localType = $competition ? $competition->local_type : Competition::LOCAL_TYPE_NONE;
		if (isset($competition->location[1])) {
			$localType = Competition::LOCAL_TYPE_NONE;
		}
		$statistics = array();
		$statistics['number'] = 0;
		$statistics['new'] = 0;
		$statistics['paid'] = 0;
		$statistics['unpaid'] = 0;
		$statistics['local'] = 0;
		$statistics['nonlocal'] = 0;
		$statistics[User::GENDER_MALE] = 0;
		$statistics[User::GENDER_FEMALE] = 0;

		//detect sort attribute
		$sort = Yii::app()->controller->sGet('sort');
		$sort = explode('.', $sort);
		if (isset($sort[1]) && $sort[1] === 'desc') {
			self::$sortDesc = true;
		}
		$sort = $sort[0];
		if ($sort !== '') {
			switch ($sort) {
				case 'name':
				case 'gender':
				case 'country_id':
				case 'birthday':
				case 'email':
				case 'mobile':
					self::$sortByUserAttribute = true;
				case 'number':
				case 'user_id':
				case 'location_id':
				case 'signed_in':
				case 'signed_date':
					self::$sortAttribute = $sort;
					break;
				default:
					self::$sortByEvent = true;
					self::$sortAttribute = $sort;
					break;
			}
		}
		self::$showPending = $showPending;
		$wcaIds = array();
		foreach ($registrations as $key=>$registration) {
			if ($enableCache && $registration->location->status == CompetitionLocation::NO) {
				unset($registrations[$key]);
				continue;
			}
			if ($registration->isAccepted()) {
				$registration->number = $number++;
			}
			$statistics['number']++;
			$statistics[$registration->user->gender]++;
			if (!isset($statistics['location'][$registration->location_id])) {
				$statistics['location'][$registration->location_id] = 0;
			}
			$statistics['location'][$registration->location_id]++;
			if ($registration->user->wcaid === '') {
				$statistics['new']++;
			}
			if ($localType == Competition::LOCAL_TYPE_PROVINCE && $registration->user->province_id == $this->competition->location[0]->province_id
				|| $localType == Competition::LOCAL_TYPE_CITY && $registration->user->city_id == $this->competition->location[0]->city_id
				|| $localType == Competition::LOCAL_TYPE_MAINLAND && $registration->user->country_id == 1
			) {
				$statistics['local']++;
			} else {
				$statistics['nonlocal']++;
			}
			foreach ($registration->allEvents as $registrationEvent) {
				if ($registrationEvent->isCancelled()) {
					continue;
				}
				if (!$showPending && !$registrationEvent->isAccepted()) {
					continue;
				}
				$event = $registrationEvent->event;
				if (!isset($statistics[$event])) {
					$statistics[$event] = 0;
				}
				$statistics[$event]++;
			}
			$fee = $registration->getTotalFee();
			if ($registration->isPaid()) {
				$statistics['paid'] += $fee;
			} else {
				$statistics['unpaid'] += $fee;
			}
			//store wcaids
			if ($registration->user->wcaid) {
				$wcaIds[$registration->user->wcaid] = $registration;
			}
		}
		if (self::$sortByEvent === true && !empty($wcaIds)) {
			switch ($sort) {
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
			$results = $modelName::model()->cache(86400)->findAllByAttributes(array(
				'event_id'=>$sort,
				'person_id'=>array_keys($wcaIds),
			));
			foreach ($results as $result) {
				$wcaIds[$result->person_id]->best = $result->best;
			}
		}
		$statistics['gender'] = $statistics[User::GENDER_MALE] . '/' . $statistics[User::GENDER_FEMALE];
		$statistics['old'] = $statistics['number'] - $statistics['new'];
		$statistics['name'] = $statistics['new'] . '/' . $statistics['old'];
		$statistics['fee'] = $statistics['paid'] . '/' . $statistics['unpaid'];
		$statistics['location_id'] = [];
		if ($this->competition && $this->competition->isMultiLocation()) {
			$temp = [];
			foreach ($this->competition->sortedLocations as $location) {
				if (isset($statistics['location'][$location->location_id])) {
					if (!isset($temp[$location->country_id])) {
						$temp[$location->country_id] = [
							'location'=>$location,
							'statistics'=>[],
						];
					}
					$locationStatistic = $location->getCityName() . ': ' . $statistics['location'][$location->location_id];
					if ($location->competitor_limit > 0) {
						$locationStatistic .= '/' . $location->competitor_limit;
					}
					$temp[$location->country_id]['statistics'][] = $locationStatistic;
				}
			}
			if ($this->competition->multi_countries) {
				foreach ($temp as $key=>$value) {
					if ($this->competition->isMultiRegions) {
						$statistics['location_id'][] = CHtml::tag('b', [], Yii::t('Region', $value['location']->country->getAttributeValue('name')) . ': ');
					}
					$statistics['location_id'][] = implode('<br>', $value['statistics']);
				}
			} elseif (isset($temp[0])) {
				$statistics['location_id'] = $temp[0]['statistics'];
			}
		}
		$statistics['location_id'] = implode('<br>', $statistics['location_id']);
		if ($localType != Competition::LOCAL_TYPE_NONE) {
			$statistics['country_id'] =  $statistics['local'] . '/' . $statistics['nonlocal'];
		}
		$sortByEventColumnIndex = 0;
		foreach ($columns as $key=>$column) {
			if (isset($column['name']) && isset($statistics[$column['name']])) {
				$columns[$key]['footer'] = $statistics[$column['name']];
			}
			if (self::$sortByEvent && ($column['name'] ?? '') == self::$sortAttribute) {
				$sortByEventColumnIndex = $key;
			}
		}
		if ($sortByEventColumnIndex > 0) {
			if (count($columns) - count($this->competition->associatedEvents) == 4) {
				$firstEventColumnIndex = 4;
			} else {
				$firstEventColumnIndex = 7;
			}
			$column = array_splice($columns, $sortByEventColumnIndex, 1);
			array_splice($columns, $firstEventColumnIndex, 0, $column);
		}
		usort($registrations, array($this, 'sortRegistration'));
		if ($sort !== '') {
			if (count($registrations) > 0 && self::$sortByEvent === true && self::$sortDesc !== true) {
				$best = $registrations[0]->best;
				$pos = 1;
				foreach ($registrations as $i=>$registration) {
					if ($registration->best < 0) {
						break;
					}
					if ($registration->best > $best) {
						$best = $registration->best;
						$pos = $i + 1;
					}
					$registration->pos = $pos;
				}
			}
			if (self::$sortDesc === true && self::$sortByEvent !== true) {
				$registrations = array_reverse($registrations);
			}
		}
		if ($pagination !== false) {
			$pagination = array(
				'pageSize'=>200,
				'pageVar'=>'page',
			);
		}
		return new NonSortArrayDataProvider(array_values($registrations), array(
			'sort'=>$this->getSort($columns),
			'pagination'=>$pagination,
		));
	}

	public function searchUser() {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;
		$criteria->with = 'competition';
		$criteria->order = 'competition.date DESC, competition.end_date DESC';

		$criteria->compare('t.id', $this->id, true);
		$criteria->compare('t.competition_id', $this->competition_id);
		$criteria->compare('t.user_id', $this->user_id);
		$criteria->compare('t.comments', $this->comments, true);
		$criteria->compare('t.date', $this->date, true);
		$criteria->compare('t.status', $this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>100,
			),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Registration the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function __call($name, $parameters) {
		if (strtolower(substr($name, -6)) === 'string') {
			$method = substr($name, 0, -6);
			if (method_exists($this, $method)) {
				$events = (array)call_user_func_array([$this, $method], $parameters);
				return implode(Yii::t('common', ', '), array_map(function($event) {
					if (is_object($event) && isset($event->event)) {
						$event = $event->event;
					}
					return Events::getFullEventName($event);
				}, $events));
			}
		}
		return parent::__call($name, $parameters);
	}
}
