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
	public $repeatPassportNumber;
	public $coefficients = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
	public $codes = array(1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2);

	public static $sortByUserAttribute = false;
	public static $sortByEvent = false;
	public static $sortAttribute = 'number';
	public static $sortDesc = false;

	const UNPAID = 0;
	const PAID = 1;

	const AVATAR_TYPE_SUBMMITED = 0;
	const AVATAR_TYPE_NOW = 1;

	const STATUS_PENDING = 0;
	const STATUS_ACCEPTED = 1;
	const STATUS_CANCELLED = 2;
	const STATUS_CANCELLED_TIME_END = 3;
	const STATUS_CANCELLED_QUALIFYING_TIME = 4;
	const STATUS_WAITING = 5;

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

	public static function getAllStatus() {
		return array(
			self::STATUS_PENDING=>Yii::t('common', 'Pending'),
			self::STATUS_ACCEPTED=>Yii::t('common', 'Accepted'),
			self::STATUS_CANCELLED=>Yii::t('common', 'Cancelled'),
			self::STATUS_CANCELLED_TIME_END=>Yii::t('common', 'Cancelled'),
			self::STATUS_CANCELLED_QUALIFYING_TIME=>Yii::t('common', 'Cancelled'),
			self::STATUS_WAITING=>Yii::t('common', 'Waiting'),
		);
	}

	public static function getUserRegistration($competitionId, $userId) {
		return self::model()->findByAttributes(array(
			'competition_id'=>$competitionId,
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
		return $this->status == self::STATUS_CANCELLED_QUALIFYING_TIME;
	}

	public function isCancellable() {
		$competition = $this->competition;
		return time() < $competition->cancellation_end_time && $this->isAccepted() || $this->isWaiting();
	}

	public function isWaiting() {
		return $this->status == self::STATUS_WAITING;
	}

	public function isPaid() {
		return $this->paid == self::PAID;
	}

	public function accept($forceAccept = false) {
		if ($this->isCancelled()) {
			return false;
		}
		$this->formatEvents();
		$this->status = Registration::STATUS_ACCEPTED;
		if ($this->accept_time == 0) {
			$this->accept_time = time();
		}
		if ($this->competition->isRegistrationFull()) {
			if (!$forceAccept) {
				$this->status = self::STATUS_WAITING;
			}
			if (!$this->competition->has_been_full) {
				$this->competition->has_been_full = Competition::YES;
				$this->competition->formatDate();
				$this->competition->save();
			}
		}
		$this->save();
		if ($this->isAccepted() && $this->competition->show_qrcode) {
			Yii::app()->mailer->sendRegistrationAcception($this);
		}
	}

	public function acceptNext() {
		$nextRegistration = self::model()->findByAttributes([
			'competition_id'=>$this->competition_id,
			'status'=>self::STATUS_WAITING,
		], [
			'order'=>'accept_time ASC',
		]);
		if ($nextRegistration) {
			$nextRegistration->accept();
		}
	}

	public function cancel($status = Registration::STATUS_CANCELLED) {
		$this->formatEvents();
		$this->status = $status;
		$this->cancel_time = time();
		if ($this->save()) {
			if ($this->isPaid() && $this->pay != null && $this->pay->isPaid()) {
				$this->pay->refund($this->getRefundFee());
			}
			Yii::app()->mailer->sendRegistrationCancellation($this);
			if (!$this->competition->isRegistrationFull()) {
				$this->acceptNext();
			}
			return true;
		}
		return false;
	}

	public function disqualify() {
		$this->formatEvents();
		$this->status = self::STATUS_CANCELLED_QUALIFYING_TIME;
		$this->cancel_time = time();
		if ($this->save()) {
			Yii::app()->mailer->sendRegistrationDisqualified($this);
			return true;
		}
		return false;
	}

	public function getUnmetEvents() {
		$competition = $this->competition;
		$user = $this->user;
		$results = Results::model()->with('competition')->findAllByAttributes([
			'personId'=>$user->wcaid,
		], [
			'condition'=>'competition.year<:year
				OR (competition.year=:year AND competition.endMonth<:month)
				OR (competition.year=:year AND competition.endMonth=:month AND competition.endDay<=:day)',
			'select'=>[
				'eventId',
				'min(CASE WHEN best>0 THEN best ELSE 9999999999 END) AS best',
				'min(CASE WHEN average>0 THEN average ELSE 9999999999 END) AS average',
			],
			'group'=>'eventId',
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
			$temp[$result->eventId] = $rank;
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
					$sum += $this->entourage_passport_number{$i} * $this->coefficients[$i];
				}
				$mod = $sum % 11;
				if (strtoupper($this->entourage_passport_number{17}) != $this->codes[$mod]) {
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

	public function getEventsString($event) {
		$str = '';
		if (in_array($event, $this->events)) {
			$str = '<span class="fa fa-check"></span>';
			if ($this->best > 0 && self::$sortAttribute === $event && self::$sortDesc !== true) {
				$str = self::$sortDesc === true ? '' : '[' . $this->pos . ']' . $str;
				$str .= Results::formatTime($this->best, $event);
			}
		}
		return $str;
	}

	public function getRegistrationEvents() {
		$competitionEvents = $this->competition->getRegistrationEvents();
		$events = array();
		foreach ($this->events as $event) {
			if (isset($competitionEvents[$event])) {
				$events[] = Yii::t('event', $competitionEvents[$event]);
			}
		}
		return implode(Yii::t('common', ', '), $events);
	}

	public function getRegistrationFee() {
		$fee = $this->getTotalFee();
		if ($this->isPaid() && $fee > 0) {
			$fee .= Yii::t('common', ' (paid)');
		}
		return $fee;
	}

	public function getTotalFee($recalculate = false) {
		if (empty($this->events)) {
			return 0;
		}
		if (($this->isAccepted() || $this->paid) && !$recalculate) {
			return $this->total_fee;
		}
		$competition = $this->competition;
		$competitionEvents = $competition->associatedEvents;
		$fees = array();
		$multiple = $competition->second_stage_date <= time() && $competition->second_stage_all;
		foreach ($this->events as $event) {
			if (isset($competitionEvents[$event])) {
				$fees[] = $competition->getEventFee($event);
			}
		}
		$entourageFee = $this->has_entourage ? $competition->entourage_fee : 0;
		return $competition->getEventFee('entry') + $entourageFee + array_sum($fees);
	}

	public function getPaidFee() {
		if ($this->isPaid() && $this->pay != null && $this->pay->isPaid()) {
			return number_format($this->pay->paid_amount / 100, 2, '.', '');
		}
		return 0;
	}

	public function getOtherFee() {
		if ($this->isPaid()) {
			$this->getTotalFee() - $this->getPaidFee();
		}
		return 0;
	}

	public function getRefundFee() {
		if ($this->getPaidFee() == 0) {
			return 0;
		}
		//候补列表的直接全额退款
		if ($this->isWaiting() || $this->status == self::STATUS_CANCELLED_TIME_END) {
			return $this->pay->paid_amount;
		}
		//被资格线清掉的，就是0
		if ($this->status == self::STATUS_CANCELLED_QUALIFYING_TIME) {
			return 0;
		}
		switch ($this->competition->refund_type) {
			case Competition::REFUND_TYPE_50_PERCENT:
			case Competition::REFUND_TYPE_100_PERCENT:
				$percent = intval($this->competition->refund_type);
				return $this->pay->paid_amount * $percent / 100;
			default:
				return 0;
		}
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
			$this->formatEvents();
			$this->code = substr(sprintf('registration-%s-%s', Uuid::uuid1(), Uuid::uuid4()), 0, 64);
			$this->save();
		}
		return CHtml::normalizeUrl(array(
			'/qrCode/signin',
			'code'=>$this->code,
		));
	}

	public function getLocation() {
		return CompetitionLocation::model()->with('province', 'city')->findByAttributes(array(
			'competition_id'=>$this->competition_id,
			'location_id'=>$this->location_id,
		));
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
			$columns = array_slice($this->competition->getEventsColumns(true), 1);
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
				'value'=>'$data->getTotalFee() . ($data->isPaid() ? Yii::t("common", " (paid)") : "")',
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
		$canApprove = Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR) || !$this->competition->isWCACompetition() || $this->user->country_id > 1;
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
					$buttons[] = CHtml::tag('button', array(
						'class'=>'btn btn-xs btn-red btn-square toggle',
						'data-id'=>$this->id,
						'data-url'=>CHtml::normalizeUrl(array('/board/registration/toggle')),
						'data-attribute'=>'status',
						'data-value'=>$this->status,
						'data-text'=>'["通过","取消"]',
						'data-name'=>$this->user->getCompetitionName(),
					), '取消');
					break;
				case self::STATUS_WAITING:
					$buttons[] = CHtml::tag('button', [
						'class'=>'btn btn-xs btn-purple btn-square',
					], '候选');
					break;
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
		if (Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR) && $this->status == self::STATUS_ACCEPTED) {
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
		if ($this->isCancelled()) {
			return false;
		}
		$totalFee = $this->getTotalFee();
		if ($this->competition->isOnlinePay() && $totalFee > 0) {
			if ($this->pay === null) {
				$this->pay = $this->createPay();
			}
			if ($this->pay->amount !== $totalFee * 100 && !$this->pay->isPaid()) {
				$this->pay->amount = $totalFee * 100;
				$this->pay->save(false);
			}
		}
		return $this->competition->isOnlinePay() && $totalFee > 0
			&& !$this->isAccepted() && !$this->competition->isRegistrationFull()
			&& !$this->competition->isRegistrationEnded();
	}

	public function createPay() {
		$pay = new Pay();
		$pay->user_id = $this->user_id;
		$pay->type = Pay::TYPE_REGISTRATION;
		$pay->type_id = $this->competition_id;
		$pay->sub_type_id = $this->id;
		$pay->amount = $this->total_fee * 100;
		$pay->order_name = $this->competition->name_zh;
		$r = $pay->save();
		return $pay;
	}

	public function handleEvents() {
		if ($this->events !== null) {
			$this->events = json_encode($this->events);
		}
	}

	public function formatEvents() {
		if (is_array($this->events)) {
			return;
		}
		$temp = json_decode($this->events, true);
		if ($temp === null) {
			$temp = array();
		}
		$this->events = $temp;
	}

	protected function afterFind() {
		$this->formatEvents();
	}

	protected function beforeValidate() {
		$this->handleEvents();
		return parent::beforeValidate();
	}

	protected function afterSave() {
		parent::afterSave();
		Yii::app()->cache->delete('competitors_' . $this->competition_id);
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
			array('events', 'length', 'max'=>512),
			array('comments', 'length', 'max'=>2048),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, competition_id, location_id, user_id, events, total_fee, comments, date, status', 'safe', 'on'=>'search'),
		);
		if ($this->competition_id > 0 && $this->competition->entourage_limit) {
			$rules[] = array('entourage_name', 'checkEntourageName', 'on'=>'register');
			$rules[] = array('entourage_passport_name', 'safe', 'on'=>'register');
			$rules[] = array('entourage_passport_type', 'checkPassportType', 'on'=>'register');
			$rules[] = array('entourage_passport_number', 'checkPassportNumber', 'on'=>'register');
			$rules[] = array('has_entourage', 'required', 'on'=>'register');
			$rules[] = ['repeatPassportNumber, guest_paid', 'safe', 'on'=>'register'];
		}
		if ($this->competition_id > 0 && $this->competition->require_avatar) {
			$rules[] = array('avatar_type', 'checkAvatarType', 'on'=>'register');
			$rules[] = array('avatar_type', 'required', 'on'=>'register');
		}
		if ($this->competition_id > 0 && $this->competition->isMultiLocation()) {
			$rules[] = array('location_id', 'required', 'on'=>'register');
		}
		return $rules;
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'user'=>array(self::BELONGS_TO, 'User', 'user_id'),
			'competition'=>array(self::BELONGS_TO, 'Competition', 'competition_id'),
			'pay'=>array(self::HAS_ONE, 'Pay', 'sub_type_id', 'on'=>'pay.type=' . Pay::TYPE_REGISTRATION),
			'avatar'=>array(self::BELONGS_TO, 'UserAvatar', 'avatar_id'),
			// 'location'=>array(self::HAS_ONE, 'CompetitionLocation', [
			// 	'competition_id'=>'competition_id',
			// 	'location_id'=>'location_id',
			// ]),
		);
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
			$temp = in_array($attribute, $rB->events) - in_array($attribute, $rA->events);
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
	public function search(&$columns = array(), $enableCache = true, $pagination = false) {
		// @todo Please modify the following code to remove attributes that should not be searched.
		$cacheKey = 'competitors_' . $this->competition_id;
		$cache = Yii::app()->cache;
		if (!$enableCache || ($registrations = $cache->get($cacheKey)) === false) {
			$criteria = new CDbCriteria;
			$criteria->order = 't.accept_time>0 DESC, t.accept_time, t.id';
			$criteria->with = array('user', 'user.country', 'user.province', 'user.city', 'competition');

			$criteria->compare('t.id', $this->id,true);
			$criteria->compare('t.competition_id', $this->competition_id);
			$criteria->compare('t.user_id', $this->user_id);
			$criteria->compare('t.events', $this->events,true);
			$criteria->compare('t.comments', $this->comments,true);
			$criteria->compare('t.date', $this->date,true);
			$criteria->compare('t.status', $this->status);
			$criteria->compare('user.status', User::STATUS_NORMAL);
			$registrations = $this->findAll($criteria);
			if ($enableCache) {
				$cache->set($cacheKey, $registrations, 86400 * 7);
			}
		}
		$number = 1;
		$localType = $this->competition ? $this->competition->local_type : Competition::LOCAL_TYPE_NONE;
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
		$wcaIds = array();
		foreach ($registrations as $registration) {
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
			foreach ($registration->events as $event) {
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
				'eventId'=>$sort,
				'personId'=>array_keys($wcaIds),
			));
			foreach ($results as $result) {
				$wcaIds[$result->personId]->best = $result->best;
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
					$temp[$location->country_id]['statistics'][] = $location->getCityName() . ': ' . $statistics['location'][$location->location_id];
				}
			}
			if ($this->competition->multi_countries) {
				foreach ($temp as $key=>$value) {
					$statistics['location_id'][] = CHtml::tag('b', [], Yii::t('Region', $value['location']->country->getAttributeValue('name')) . ': ');
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
		foreach ($columns as $key=>$column) {
			if (isset($column['name']) && isset($statistics[$column['name']])) {
				$columns[$key]['footer'] = $statistics[$column['name']];
			}
		}
		if ($sort !== '') {
			usort($registrations, array($this, 'sortRegistration'));
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
		return new NonSortArrayDataProvider($registrations, array(
			'sort'=>$this->getSort($columns),
			'pagination'=>$pagination,
		));
	}

	public function searchUser() {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;
		$criteria->order = 't.date DESC';

		$criteria->compare('t.id', $this->id,true);
		$criteria->compare('t.competition_id', $this->competition_id);
		$criteria->compare('t.user_id', $this->user_id);
		$criteria->compare('t.events', $this->events,true);
		$criteria->compare('t.comments', $this->comments,true);
		$criteria->compare('t.date', $this->date,true);
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
}
