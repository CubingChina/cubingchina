<?php

/**
 * This is the model class for table "competition".
 *
 * The followings are the available columns in table 'competition':
 * @property string $id
 * @property string $type
 * @property string $wca_competition_id
 * @property string $name
 * @property string $name_zh
 * @property string $date
 * @property string $end_date
 * @property string $reg_end
 * @property string $events
 * @property integer $entry_fee
 * @property integer $online_pay
 * @property string $information
 * @property string $information_zh
 * @property string $travel
 * @property string $travel_zh
 * @property integer $person_num
 * @property integer $status
 */
class Competition extends ActiveRecord {
	const TYPE_WCA = 'WCA';
	const TYPE_OTHER = 'other';

	const NOT_ONLINE_PAY = 0;
	const ONLINE_PAY = 1;

	const STATUS_HIDE = 0;
	const STATUS_SHOW = 1;
	const STATUS_DELETE = 2;
	const STATUS_UNCONFIRMED = 3;
	const STATUS_CONFIRMED = 4;
	const STATUS_REJECTED = 5;
	const STATUS_LOCKED = 6;

	const UNPAID = 0;
	const PAID = 1;

	const STAGE_FIRST = 'first';
	const STAGE_SECOND = 'second';
	const STAGE_THIRD = 'third';

	const LOCAL_TYPE_NONE = 0;
	const LOCAL_TYPE_PROVINCE = 1;
	const LOCAL_TYPE_CITY = 2;
	const LOCAL_TYPE_MAINLAND = 3;

	const REQUIRE_AVATAR_NONE = 0;
	const REQUIRE_AVATAR_ACA = 1;

	const REFUND_TYPE_NONE = 'none';
	const REFUND_TYPE_50_PERCENT = '50';
	const REFUND_TYPE_100_PERCENT = '100';

	const EVENT_FEE_ENTRY = 'entry';
	const EVENT_FEE_WCA_DUES = 'wca_dues';

	const OLD_WCA_DUES_START = 1703347200; // 2023-12-23
	const OLD_WCA_DUES_END = 1706371200; // 2024-01-28

	const WCA_DUES_START = 1706371201; // 2024-01-28
	const WCA_DUES_INCLUDING_LOWEST_START = 1733443200; // 2024-12-06

	const CUBING_FEE_BEFORE_202101 = 1609459200; // 2021-01-01
	const CUBING_FEE_BEFORE_202507 = 1719763200; // 2025-07-01

	const COMPETITOR_LIMIT_BY_COMPETITION = 0;
	const COMPETITOR_LIMIT_BY_EVENT = 1;

	const EXPLANATION_OTHER = 0;
	const EXPLANATION_TRIAL = 1;
	const EXPLANATION_NEWER = 2;
	const EXPLANATION_LARGE = 3;
	const EXPLANATION_RUNNER = 4;
	const EXPLANATION_MAP = [
		Competition::EXPLANATION_TRIAL => 'This is a trial competition, aiming to optimize and experiment with new competition formats. The procedures may differ from regular competitions. Please be aware before you register. Thank you for your understanding.',
		Competition::EXPLANATION_NEWER => 'This competition is held by new organizers who are still gaining experience. The procedures and overall experience may differ from those held by experienced organizers and may get on-site adjustments. Please be aware before you register. Thank you for your understanding.',
		Competition::EXPLANATION_LARGE => 'This is a large-scale competition, held by experienced organizers, aiming to provide the best experience for competitors.',
		Competition::EXPLANATION_OTHER => 'Other',
		Competition::EXPLANATION_RUNNER => 'This is a Runner competition, Runners will lead competitors to available stations for each attempt, after which the competitors will return to the waiting area. Competitors may leave the area after all attempts.'
	];

	private $_organizers;
	private $_organizerTeamMembers;
	private $_scoreTakers;
	private $_delegates;
	private $_locations;
	private $_events;
	private $_schedules;
	private $_scheduledRounds;
	private $_description;
	private $_timezones;
	private $_remainedNumber;
	private $_podiumsEvents;
	private $_explanations;

	public $year;
	public $province;
	public $event;
	public $distance;

	public static function formatTime($second, $event = '') {
		$second = intval($second);
		if ($second <= 0) {
			return '';
		}
		if ($event === '333fm') {
			return Yii::t('common', '{moves} moves', [
				'{moves}'=>$second,
			]);
		}
		if ($second < 60) {
			return sprintf('%d%s', $second, Yii::t('common', ' seconds'));
		}
		$minute = floor($second / 60);
		$second = $second % 60;
		$params = array(
			'{minute}'=>$minute,
			'{second}'=>$second,
		);
		if ($second == 0) {
			if ($minute > 1) {
				return Yii::t('common', '{minute} minutes', $params);
			} else {
				return Yii::t('common', '{minute} minute', $params);
			}
		} else {
			if ($minute > 1) {
				return Yii::t('common', '{minute} minutes {second} seconds', $params);
			} else {
				return Yii::t('common', '{minute} minute {second} seconds', $params);
			}
		}
	}

	public static function getBaseOptions() {
		return [
			// 'show_regulations'=>[
			// 	'label'=>'弹出报名规则提醒',
			// ],
		];
	}

	public static function getOtherOptions() {
		$options = [
			'allow_change_event'=>[
				'label'=>'允许选手自助编辑项目',
				'title'=>'关于报名',
			],
			'fill_passport'=>[
				'label'=>'要求选手填写证件号',
				'title'=>'签到与入场',
			],
			'show_qrcode'=>[
				'label'=>'使用二维码签到',
			],
			'entry_ticket'=>[
				'label'=>'是否有入场凭证',
			],
			'guest_limit'=>[
				'label'=>'是否限制观众入场',
			],
			'name_card_fee'=>[
				'label'=>'参赛证补办费用',
				'type'=>'int',
			],
			'attend_ceremory'=>[
				'title'=>'比赛颁奖',
				'label'=>'要求选手本人到场',
			],
			'podiums_children'=>[
				'label'=>'少儿组',
				'subtitle'=>'角色分组',
			],
			'podiums_females'=>[
				'label'=>'女子组',
			],
			'podiums_new_comers'=>[
				'label'=>'新人组',
			],
			'podiums_greater_china'=>[
				'label'=>'中华组',
			],
		];

		$options['podiums_u3'] = [
			'label'=>'U3组',
			'subtitle'=>'年龄-U组',
			'warning'=>'请注意，当你选择Ux时，少儿组将失效。Ux三组可以自由组合，系统自动匹配年龄。',
		];
		for ($i = 4; $i <= 18; $i++) {
			$options['podiums_u' . $i] = [
				'label'=>'U' . $i . '组',
			];
		}

		$options['podiums_o'] = [
			'label'=>'O组自定义',
			'subtitle'=>'年龄-O组',
			'warning'=>'请注意，使用O组自定义时, 多个自定义年龄用英文 "," 分割输入如： "25,35"。',
			'type'=>'raw',
		];
		$o_ages = [25, 30, 35, 45];
		foreach ($o_ages as $age) {
			$options['podiums_o' . $age] = [
				'label'=>'O' . $age . '组',
			];
		}

		return $options;
	}

	public static function getNullableAttributes() {
		return [
			'organizerTeamMembers',
			'scoreTakers',
		];
	}

	public static function getProtectedAttributes() {
		return array_merge([
			'name',
			'name_zh',
			'auto_accept',
			'type',
			'wca_competition_id',
			'explanations',
			'entry_fee',
			'online_pay',
			'person_num',
			'competitor_limit_type',
			'second_stage_date',
			'second_stage_ratio',
			'second_stage_all',
			'third_stage_date',
			'third_stage_ratio',
			'date',
			'end_date',
			'reg_start',
			'reg_end',
			'delegates',
			'organizers',
			'organizerTeamMembers',
			'locations',
			'qualifying_end_time',
			'refund_type',
			'cancellation_end_time',
			'reg_reopen_time',
			'status',
			'regulations',
			'regulations_zh',
			'information',
			'information_zh',
		], array_keys(self::getBaseOptions()), array_keys(self::getOtherOptions()));
	}

	public static function getAppliedCount($user) {
		$with = [
			'organizer'=>[
				'together'=>true,
				'condition'=>'organizer.organizer_id=' . $user->id,
			],
		];
		$model = self::model()->with($with);
		return $model->countByAttributes([
			'status'=>[self::STATUS_CONFIRMED, self::STATUS_UNCONFIRMED],
		]);

	}

	public static function getUnacceptedCount($user) {
		$with = [
			'organizer'=>[
				'together'=>true,
				'condition'=>'organizer.organizer_id=' . $user->id,
			],
		];
		$model = self::model()->with($with);
		return $model->countByAttributes([
			'status'=>[self::STATUS_CONFIRMED, self::STATUS_UNCONFIRMED]
		]);
	}

	public static function getCurrentMonthCount($user) {
		$with = [
			'organizer'=>[
				'together'=>true,
				'condition'=>'organizer.organizer_id=' . $user->id,
			],
		];
		$model = self::model()->with($with);
		return $model->countByAttributes([
			'status'=>[self::STATUS_HIDE, self::STATUS_SHOW, self::STATUS_REJECTED],
		], [
			'condition'=>'create_time between ' . strtotime('today first day of this month') . ' and ' . strtotime('today first day of next month'),
		]);
	}

	public static function getUpcomingRegistrableCompetitions($limit = 5) {
		return self::model()->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'condition'=>'date>' . time() . ' AND reg_end>' . time(),
			'limit'=>$limit,
			'order'=>'date ASC, type DESC',
		));
	}

	public static function getUpcomingCompetitions($limit = 5) {
		$yesterday = strtotime('yesterday');
		return self::model()->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'condition'=>"date > {$yesterday} OR end_date > {$yesterday}",
			'limit'=>$limit,
			'order'=>'date ASC, type DESC',
		));
	}

	public static function getPublicCompetitions($limit = 5) {
		return self::model()->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'limit'=>$limit,
			'order'=>'date DESC',
		));
	}

	public static function getAllCompetitions() {
		$model = self::model();
		if (Yii::app()->controller->user->isOrganizer()) {
			$model->with(array(
				'organizer'=>array(
					'together'=>true,
					'condition'=>'organizer.organizer_id=' . Yii::app()->user->id,
				),
			));
		}
		return $model->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		));
	}

	public static function getRegistrationCompetitions() {
		if (!Yii::app()->user->checkRole(User::ROLE_ORGANIZER) && !Yii::app()->user->checkPermission('caqa')) {
			return [];
		}
		$with = array();
		if (Yii::app()->controller->user->isOrganizer() && !Yii::app()->user->checkPermission('caqa')) {
			$with = array(
				'organizer'=>array(
					'together'=>true,
					'condition'=>'organizer.organizer_id=' . Yii::app()->user->id,
				),
			);
		}
		$inProgress = self::model()->with($with)->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'order'=>'t.date DESC',
			'condition'=>'t.date>' . time(),
		));
		$ended = self::model()->with($with)->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'order'=>'t.date DESC',
			'condition'=>'t.date<' . time(),
		));
		$competitions = array();
		if ($inProgress !== array()) {
			$competitions['In Progress'] = CHtml::listData($inProgress, 'id', 'name_zh');
		}
		if ($ended !== array()) {
			$competitions['Ended'] = CHtml::listData($ended, 'id', 'name_zh');
		}
		return $competitions;
	}

	public static function getCompetitionByName($name) {
		return self::model()->with('location', 'location.province', 'location.city')->findByAttributes(array(
			'alias'=>$name,
			'status'=>[self::STATUS_SHOW, self::STATUS_HIDE, self::STATUS_LOCKED],
		));
	}

	public static function getRecentCompetitionsForNavibar() {
		$competitions = self::model()->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'condition'=>'date>' . time(),
			'limit'=>3,
			'order'=>'date ASC',
		));
		$attribute = Yii::app()->controller->getAttributeName('name');
		$items = array();
		foreach ($competitions as $competition) {
			$items[] = array(
				'label'=>$competition->$attribute,
				'url'=>$competition->getUrl('detail'),
			);
		}
		return $items;
	}

	public static function getTypes() {
		return array(
			self::TYPE_WCA=>self::TYPE_WCA,
			self::TYPE_OTHER=>Yii::t('common', 'Other'),
		);
	}

	public static function getExplanationLabels() {
		return array(
			self::EXPLANATION_OTHER=>Yii::t('competition', 'Other'),
			self::EXPLANATION_TRIAL=>Yii::t('competition', 'Trial competition'),
			self::EXPLANATION_NEWER=>Yii::t('competition', 'Competition with new organizers'),
			self::EXPLANATION_LARGE=>Yii::t('competition', 'Large-scale competition'),
			self::EXPLANATION_RUNNER=>Yii::t('competition', 'Runner competition'),	
		);
	}

	public static function getRefundTypes() {
		return array(
			self::REFUND_TYPE_NONE=>'不退',
			self::REFUND_TYPE_50_PERCENT=>'50%',
			self::REFUND_TYPE_100_PERCENT=>'100%',
		);
	}

	public static function getLocalTypes() {
		return array(
			self::LOCAL_TYPE_NONE=>'无',
			self::LOCAL_TYPE_PROVINCE=>'省',
			self::LOCAL_TYPE_CITY=>'市',
			self::LOCAL_TYPE_MAINLAND=>'大陆',
		);
	}

	public static function getOnlinePays() {
		return array(
			self::ONLINE_PAY=>'是',
			self::NOT_ONLINE_PAY=>'否',
		);
	}

	public static function getYears() {
		$years = array(
			'current'=>Yii::t('common', 'Current'),
		);
		$lastCompetition = self::model()->findByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'order'=>'date DESC',
			'select'=>'date',
		));
		for ($year = intval(date('Y', $lastCompetition->date)); $year >= 2006; $year--) {
			$years[$year] = $year;
		}
		return $years;
	}

	public static function getPodiumAges() {
		return range(3, 18);
	}

	public function getPodiumOldAges($order = 'sort') {
		$base = [25, 30, 35, 45];
		$ages = [];

		foreach ($base as $b){
			if ($this->{'podiums_o' . $b}){
				$ages[] = $b;
			}
		}

		if ($this->podiums_o != "") {
			$numbers = explode(',', str_replace('，', ',', $this->podiums_o));
			$numbers = array_map('floatval', $numbers);
			foreach ($numbers as $number){
				$ages[] = $number;
			}
		}
		$ages = array_unique($ages);
		// sort an array from largest to smallest.
		// because when you use it later, you need to start counting from the oldest group.
		if ($order == 'sort') {
			sort($ages);
			return $ages;
		}
		rsort($ages);
		return $ages;
	}

	public static function getAllStatus($actionId = 'index') {
		switch ($actionId) {
			case 'application':
				return [
					self::STATUS_UNCONFIRMED=>'未确认',
					self::STATUS_CONFIRMED=>'已确认',
					self::STATUS_REJECTED=>'已拒绝',
				];
			case 'index':
			default:
				return [
					self::STATUS_HIDE=>'待公示',
					self::STATUS_LOCKED=>'已锁定',
					self::STATUS_SHOW=>'已公示',
				];
			case 'all':
				return [
					self::STATUS_UNCONFIRMED=>'未确认',
					self::STATUS_CONFIRMED=>'已确认',
					self::STATUS_REJECTED=>'已拒绝',
					self::STATUS_HIDE=>'待公示',
					self::STATUS_LOCKED=>'已锁定',
					self::STATUS_SHOW=>'已公示',
				];
		}
	}

	public function isWCACompetition() {
		return $this->type == self::TYPE_WCA;
	}

	public function isOnlinePay() {
		return $this->online_pay == self::ONLINE_PAY;
	}

	public function isPublic() {
		return $this->status == self::STATUS_SHOW;
	}

	public function isHide() {
		return $this->status == self::STATUS_HIDE && !$this->isNewRecord;
	}

	public function isLocked() {
		return $this->status == self::STATUS_LOCKED;
	}

	public function isPublicVisible() {
		return $this->isPublic() || $this->isLocked();
	}

	public function isLimitByEvent() {
		return $this->competitor_limit_type == self::COMPETITOR_LIMIT_BY_EVENT;
	}

	public function isRegistrationStarted() {
		return time() >= $this->getTimeInNumber('reg_start');
	}

	public function isRegistrationEnded() {
		return time() > $this->getTimeInNumber('reg_end');
	}

	public function isRegistrationPaused() {
		$cancellationEndTime = $this->getTimeInNumber('cancellation_end_time');
		return $cancellationEndTime > 0 && time() > $cancellationEndTime && time() < $this->getTimeInNumber('reg_reopen_time');
	}

	public function isRegistrationFull() {
		if (!$this->isLimitByEvent()) {
			return $this->person_num > 0 && Registration::model()->with(array(
					'user'=>array(
						'condition'=>'user.status=' . User::STATUS_NORMAL,
					),
				))->countByAttributes(array(
					'competition_id'=>$this->id,
					'status'=>Registration::STATUS_ACCEPTED,
				)) >= $this->person_num;
		}
		foreach ($this->getAssociatedEvents() as $event) {
			if (!$this->isEventRegistrationFull($event)) {
				return false;
			}
		}
		return true;
	}

	public function isEventRegistrationFull($event) {
		$competitors = RegistrationEvent::countByEvent($this->id, $event['event'], RegistrationEvent::STATUS_ACCEPTED);
		if ($competitors < $event['competitor_limit']) {
			return false;
		}
		return true;
	}

	public function canRegister() {
		return !$this->isRegistrationEnded() && !$this->isRegistrationFull();
	}

	public function getRemainedNumber() {
		if ($this->_remainedNumber == null) {
			$this->_remainedNumber = $this->person_num - Registration::model()->with(array(
					'user'=>array(
						'condition'=>'user.status=' . User::STATUS_NORMAL,
					),
				))->countByAttributes(array(
					'competition_id'=>$this->id,
					'status'=>Registration::STATUS_ACCEPTED,
				));
		}
		return max($this->_remainedNumber, 0);
	}

	public function isInProgress() {
		$now = time();
		return $now > $this->date && $now - 86400 < max($this->date, $this->end_date);
	}

	public function isEnded() {
		return time() - 86400 > max($this->date, $this->end_date);
	}

	public function isMultiLocation() {
		return isset($this->location[1]);
	}

	public function isOld() {
		return $this->old_competition_id > 0;
	}

	public function isScheduleFinished() {
		$events = $this->associatedEvents;
		foreach ($this->schedule as $schedule) {
			if (!isset($events[$schedule->event])) {
				continue;
			}
			$events[$schedule->event]['schedule'][$schedule->round] = 1;
		}
		foreach ($events as $event) {
			if ((!isset($event['schedule']['c']) && !isset($event['schedule']['f'])) ||
				$event['round'] > count($event['schedule'])
			) {
				return false;
			}
		}
		return true;
	}

	public function isAccepted() {
		return !$this->isNewRecord &&
			($this->status == self::STATUS_SHOW || $this->status == self::STATUS_HIDE || $this->status == self::STATUS_LOCKED);
	}

	public function isRejected() {
		return $this->status == self::STATUS_REJECTED;
	}

	public function isConfirmed() {
		return $this->status == self::STATUS_CONFIRMED;
	}

	public function isUnconfirmed() {
		return $this->status == self::STATUS_UNCONFIRMED;
	}

	public function getNearbyCompetitions($days = 26, $distance = 200, $isWCA = true) {
		if (!$this->isWCACompetition() && $isWCA) {
			return [];
		}
		if ($this->isMultiLocation()) {
			return [];
		}
		$criteria = new CDbCriteria();
		$criteria->compare('date', '>=' . ($this->date - $days * 86400));
		$criteria->compare('date', '<=' . ($this->date + $days * 86400));
		$criteria->addInCondition('status', [
			self::STATUS_HIDE,
			self::STATUS_SHOW,
			self::STATUS_CONFIRMED,
		]);
		if ($isWCA) {
			$criteria->compare('type', self::TYPE_WCA);
		}
		$criteria->compare('id', '<>' . $this->id);
		$competitions = self::model()->findAll($criteria);
		$city1 = $this->location[0]->city;
		$competitions = array_filter($competitions, function($competition) use ($distance, $city1) {
			if ($competition->isMultiLocation()) {
				return false;
			}
			$city2 = $competition->location[0]->city;
			$competition->distance = Region::getDistance($city1->latitude, $city1->longitude, $city2->latitude, $city2->longitude) / 1000;
			return $competition->distance <= $distance;
		});
		return array_values($competitions);
	}

	public function getLogo() {
		$logo = '';
		switch ($this->type) {
			case self::TYPE_WCA:
				$logo = CHtml::image('/f/images/wca.png', Yii::t('common', 'WCA Competition'), array('class'=>'wca-competition'));
				break;
		}
		return $logo;
	}

	public function getLocationInfo($type) {
		if ($this->tba) {
			return Yii::t('common', 'To be announced');
		} elseif ($this->isMultiLocation()) {
			return Yii::t('common', 'Multiple');
		} else {
			switch ($type) {
				case 'city':
				case 'province':
					return $this->location[0]->$type->getAttributeValue('name');
				default:
					return $this->location[0]->getAttributeValue($type);
			}
		}
	}

	public function getAssociatedEvents() {
		if ($this->_events !== null) {
			return $this->_events;
		}
		$events = [];
		foreach ($this->allEvents as $event) {
			$events[$event->event] = $event->attributes;
		}
		return $this->_events = $events;
	}

	public function getEventsNames() {
		return array_map(function($event) {
			return Events::getFullEventName($event['event']);
		}, $this->associatedEvents);
	}

	public function getShouldDisableUnmetEvents() {
		return $this->has_qualifying_time;
	}

	public function getUserUnmetEvents($user) {
		$ranks = RanksSingle::model()->with('average')->findAllByAttributes([
			'personId'=>$user->wcaid,
		]);
		foreach ($ranks as $rank) {
			$temp[$rank->eventId] = $rank;
		}
		$unmetEvents = [];
		foreach ($this->allEvents as $event) {
			if (!$event->check($temp[$event->event] ?? null)) {
				$unmetEvents[$event->event] = $event->getQualifyTime();
			}
		}
		return $unmetEvents;
	}

	public function getSortedLocations() {
		$locations = array_filter($this->location, function($location) {
			return $location->status == CompetitionLocation::YES;
		});
		if (!$this->multi_countries) {
			return $locations;
		}
		usort($locations, function($locationA, $locationB) {
			$temp = $locationA->country_id - $locationB->country_id;
			if ($temp == 0) {
				$temp = $locationA->province_id - $locationB->province_id;
			}
			if ($temp == 0) {
				$temp = $locationA->city_id - $locationB->city_id;
			}
			if ($temp == 0) {
				$temp = strcmp($locationA->city_name, $locationB->city_name);
			}
			return $temp;
		});
		return $locations;
	}

	public function getDays() {
		if ($this->end_date == 0) {
			return 1;
		}
		return floor(($this->end_date - $this->date) / 86400) + 1;
	}

	public function getRegistrationDoneWeiboText() {
		return sprintf('我已报名 #%s# 时间：%s (%s)', $this->name_zh, date('m月d日', $this->date), $this->venue_zh);
	}

	public function getDescription() {
		if ($this->_description !== null) {
			return $this->_description;
		}
		$description = '{name} is a speedcubing competition held at {venue} on {date}, organized by {organizers}';
		if ($this->delegate !== array()) {
			$description .= ', and is in the charge of{wca} {delegates}';
		}
		$description .= '.';
		$params = array(
			'{name}'=>$this->getAttributeValue('name'),
			'{date}'=>$this->getDisplayDate(),
			'{wca}'=>$this->type == self::TYPE_WCA ? Yii::t('common', ' the WCA delegate') : '',
		);
		if ($this->isMultiLocation()) {
			$venue = '';
			$count = count($this->location);
			foreach ($this->location as $key=>$location) {
				$address = $location->getFullAddress(false);
				if ($key == 0) {
					$venue .= $address;
				} elseif ($key < $count - 1) {
					$venue .= Yii::t('common', ', ') . $address;
				} else {
					$venue .= Yii::t('common', ' and ') . $address;
				}
			}
		} else {
			$venue = $this->location[0]->getFullAddress();
		}
		$params['{venue}'] = $venue;
		$organizers = '';
		if ($this->isOld()) {
			$organizers = strip_tags(OldCompetition::formatInfo($this->old->getAttributeValue('organizer')));
		} else {
			$count = count($this->organizer);
			foreach ($this->organizer as $key=>$organizer) {
				if ($key == 0) {
					$organizers .= $organizer->user->getAttributeValue('name', true);
				} elseif ($key < $count - 1) {
					$organizers .= Yii::t('common', ', ') . $organizer->user->getAttributeValue('name', true);
				} else {
					$organizers .= Yii::t('common', ' and ') . $organizer->user->getAttributeValue('name', true);
				}
			}
		}
		$params['{organizers}'] = $organizers;
		$delegates = '';
		$count = count($this->delegate);
		foreach ($this->delegate as $key=>$delegate) {
			if ($key == 0) {
				$delegates .= $delegate->user->getAttributeValue('name', true);
			} elseif ($key < $count - 1) {
				$delegates .= Yii::t('common', ', ') . $delegate->user->getAttributeValue('name', true);
			} else {
				$delegates .= Yii::t('common', ' and ') . $delegate->user->getAttributeValue('name', true);
			}
		}
		$params['{delegates}'] = $delegates;
		return $this->_description = Yii::t('common', $description, $params);
	}

	public function getStatusText() {
		$status = self::getAllStatus('all');
		return isset($status[$this->status]) ? $status[$this->status] : $this->status;
	}

	public function getTypeText() {
		$types = self::getTypes();
		return isset($types[$this->type]) ? $types[$this->type] : $this->type;
	}

	public function getCompetitionLink() {
		$name = $this->getAttributeValue('name');
		$logo = $this->getLogo();
		if (!$this->canRegister() && $this->live == self::YES && !$this->isEnded() && !$this->hasResults) {
			$type = 'live';
		} else {
			$type = 'detail';
		}
		return CHtml::link($logo . $name, $this->getUrl($type), array('class'=>'comp-type-' . strtolower($this->type)));
	}

	public function getWcaUrl() {
		return Competitions::getWcaUrl($this->wca_competition_id);
	}

	public function getHasResults() {
		return $this->type == self::TYPE_WCA && Results::model()->cache(86400)->countByAttributes(array(
				'competitionId'=>$this->wca_competition_id,
			)) > 0;
	}

	public function getHasGroupSchedules() {
		return GroupSchedule::model()->countByAttributes([
				'competition_id'=>$this->id,
			]) > 0;
	}

	public function getCountdown($type = 'normal') {
		if (!$this->canRegister()) {
			return '';
		}
		$options = [
			'class'=>$type,
		];
		if (!$this->isRegistrationStarted()) {
			return Html::countdown($this->reg_start, $options);
		} else {
			$options['data-total-days'] = $this->reg_start > 0 ? floor(($this->reg_end - $this->reg_start) / 86400) : 30;
			return Html::countdown($this->reg_end, $options);
		}
	}

	public function hasUserResults($wcaid) {
		return $this->type == self::TYPE_WCA && Results::model()->cache(86400)->countByAttributes(array(
				'competitionId'=>$this->wca_competition_id,
				'personId'=>$wcaid,
			)) > 0;
	}

	public function hasSchedule($event) {
		return array_filter($this->schedule, function($item) use ($event) {
				return $item['event'] === $event;
			}) !== [];
	}

	public function getTicketIds() {
		return array_map(function($ticket) {
			return $ticket->id;
		}, $this->tickets);
	}

	public function getMyCertUrl() {
		$user = Yii::app()->controller->user;
		return $this->getUserCertUrl($user);
	}

	public function getUserCertUrl($user) {
		$cert = CompetitionCert::model()->findByAttributes([
			'competition_id'=>$this->id,
			'user_id'=>$user->id,
		]);
		if ($cert === null) {
			$cert = new CompetitionCert();
			$cert->competition_id = $this->id;
			$cert->user_id = $user->id;
			$cert->create_time = time();
			$cert->save();
		}
		$cert->competition = $this;
		$cert->user = $user;
		if (!$cert->hasCert) {
			$cert->generateCert();
		}
		$name = $this->getAttributeValue('name');
		$logo = $this->getLogo();
		return CHtml::link($logo . $name, $cert->getUrl());
	}

	public function getUrl($type = 'detail', $params = array(), $controller = null) {
		$controller = $controller ?? $type === 'live' || $type === 'statistics' ? 'live' : 'competition';
		$url = array(
			"/$controller/$type",
			'alias'=>$this->getUrlName(),
		);
		foreach ($params as $key=>$value) {
			$url[$key] = $value;
		}
		return $url;
	}

	public function getUrlName() {
		return $this->alias;
	}

	public function getDisplayDate() {
		if ($this->tba == self::YES) {
			return Yii::t('common', 'To be announced');
		}
		return Competitions::getDisplayDate($this->date, $this->end_date);
	}

	public function getFirstStage() {
		if ($this->reg_start) {
			$dates[] = $this->reg_start;
		}
		if ($this->second_stage_date) {
			$dates[] = $this->second_stage_date - 1;
		} else {
			$dates[] = $this->reg_end;
		}
		return $this->formatStageDate($dates);
	}

	public function getSecondStage() {
		return $this->formatStageDate(array($this->second_stage_date, $this->third_stage_date > 0 ? $this->third_stage_date - 1 : $this->reg_end));
	}

	public function getThirdStage() {
		return $this->formatStageDate(array($this->third_stage_date, $this->reg_end));
	}

	public function formatStageDate($dates) {
		$dates = array_map(function($date) {
			return date('Y-m-d H:i:s', $date);
		}, $dates);
		if (!isset($dates[1])) {
			array_unshift($dates, Yii::t('Competition', 'Now'));
		}
		return implode('<br> ~ <br>', $dates);
	}

	public function getHasSecondStage() {
		return $this->second_stage_date > 0;
	}

	public function getHasThirdStage() {
		return $this->third_stage_date > 0;
	}

	public function getEventFee($event, $stage = null, $entryFee = 0) {
		$events = $this->associatedEvents;
		$isBasic = $event === self::EVENT_FEE_ENTRY;
		$isWCADues = $event === self::EVENT_FEE_WCA_DUES;
		if ($stage === null) {
			$stage = $this->calculateStage();
		}
		$entryFee = ($this->complex_multi_location || $this->multi_countries) && $entryFee > 0 ? $entryFee : $this->entry_fee;
		if ($isWCADues) {
			if (!$this->isWCACompetition()) {
				return 0;
			}
			$minFee = 0;
			if ($this->date >= self::WCA_DUES_INCLUDING_LOWEST_START) {
				// get minimal fee of each events
				$minFee = PHP_INT_MAX;
				$events = Events::getNormalEvents();
				foreach ($this->associatedEvents as $event) {
					if (!isset($events[$event['event']])) {
						continue;
					}
					$fee = $this->getEventFee($event['event'], self::STAGE_FIRST);
					if ($fee < $minFee) {
						$minFee = $fee;
					}
				}
			}
			return round(($this->entry_fee + $minFee) * 0.15, 2);
		}
		$basicFee = intval($isBasic ? $entryFee : $events[$event]['fee']);
		switch ($stage) {
			case self::STAGE_FIRST:
				return $basicFee;
			case self::STAGE_SECOND:
			case self::STAGE_THIRD:
				$ratio = $this->{$stage . '_stage_ratio'};
				if ($isBasic) {
					return ceil($basicFee * $ratio);
				}
				if (isset($events[$event]['fee_' . $stage]) && $events[$event]['fee_' . $stage] > 0) {
					return intval($events[$event]['fee_' . $stage]);
				}
				return $this->second_stage_all ? ceil($basicFee * $ratio) : $basicFee;
		}
	}

	public function getFeeRatio($stage = null) {
		if ($stage === null) {
			$stage = $this->calculateStage($stage);
		}
		switch ($stage) {
			case self::STAGE_FIRST:
				return 1;
			case self::STAGE_SECOND:
			case self::STAGE_THIRD:
				return $this->{$stage . '_stage_ratio'};
		}
	}

	public function calculateStage($time = null) {
		if (!$time) {
			$time = time();
		}
		if ($time < $this->second_stage_date) {
			$stage = self::STAGE_FIRST;
		} elseif ($time < $this->third_stage_date) {
			$stage = self::STAGE_SECOND;
		} else {
			$stage = self::STAGE_THIRD;
		}
		if (!$this->hasThirdStage && $stage == self::STAGE_THIRD) {
			$stage = self::STAGE_SECOND;
		}
		if (!$this->hasSecondStage && $stage == self::STAGE_SECOND) {
			$stage = self::STAGE_FIRST;
		}
		return $stage;
	}

	public function getPaypalLink($registration) {
		return rtrim($this->paypal_link, '/') . '/' . round($registration->getTotalFee() / Yii::app()->params->exchangeRate['usd'], 2) . 'usd';
	}

	public function getOrganizers() {
		if ($this->_organizers === null) {
			$this->_organizers = CHtml::listData($this->organizer, 'organizer_id', 'organizer_id');
		}
		return $this->_organizers;
	}

	public function setOrganizers($organizers) {
		$this->_organizers = $organizers;
	}

	public function getOrganizerTeamMembers() {
		if ($this->_organizerTeamMembers === null) {
			$this->_organizerTeamMembers = CHtml::listData($this->organizerTeamMember, 'user_id', 'user_id');
		}
		return $this->_organizerTeamMembers;
	}

	public function setOrganizerTeamMembers($organizerTeamMembers) {
		$this->_organizerTeamMembers = $organizerTeamMembers;
	}

	public function getScoreTakers() {
		if ($this->_scoreTakers === null) {
			$this->_scoreTakers = CHtml::listData($this->scoreTaker, 'user_id', 'user_id');
		}
		return $this->_scoreTakers;
	}

	public function setScoreTakers($scoreTakers) {
		$this->_scoreTakers = $scoreTakers;
	}

	public function getOrganizerKeyValues($organizers, $key = 'organizer_id') {
		$data = [];
		foreach ((array)$organizers as $organizer) {
			$user = User::model()->findByPk($organizer);
			if (!$user) {
				continue;
			}
			$data[$user->id] = $user->getCompetitionName();
		}
		return $data;
	}

	public function getOldOrganizer() {
		return $this->old->organizer;
	}

	public function setOldOrganizerZh($organizerZh) {
		$this->old->organizer_zh = $organizerZh;
	}

	public function getOldOrganizerZh() {
		return $this->old->organizer_zh;
	}

	public function setOldOrganizer($organizer) {
		$this->old->organizer = $organizer;
	}

	public function getOldDelegate() {
		return $this->old->delegate;
	}

	public function setOldDelegateZh($delegateZh) {
		$this->old->delegate_zh = $delegateZh;
	}

	public function getOldDelegateZh() {
		return $this->old->delegate_zh;
	}

	public function setOldDelegate($delegate) {
		$this->old->delegate = $delegate;
	}

	public function getDelegates() {
		if ($this->_delegates === null) {
			$this->_delegates = CHtml::listData($this->delegate, 'delegate_id', 'delegate_id');
		}
		return $this->_delegates;
	}

	public function setDelegates($delegates) {
		$this->_delegates = $delegates;
	}

	public function getExplanations() {
		if ($this->_explanations === null) {
			$this->_explanations = CHtml::listData($this->explanation, 'id', 'label');
		}
		return $this->_explanations;
	}

	public function setExplanations($explanations) {
		$this->_explanations = $explanations;
	}

	public function getLocations() {
		if ($this->_locations === null) {
			$this->_locations = array_map(function($location) {
				return $location->attributes;
			}, $this->location);
		}
		return $this->_locations;
	}

	public function setLocations($locations) {
		$this->_locations = $locations;
	}

	public function getSchedules() {
		if ($this->_schedules === null) {
			$this->_schedules = array_map(function($schedule) {
				return $schedule->attributes;
			}, $this->schedule);
			usort($this->_schedules, array($this, 'sortSchedules'));
		}
		return $this->_schedules;
	}

	public function setSchedules($schedules) {
		$this->_schedules = $schedules;
	}

	public function getPodiumsEvents() {
		if ($this->_podiumsEvents === null) {
			$this->_podiumsEvents = json_decode($this->podiums_events);
			if ($this->_podiumsEvents == null) {
				$this->_podiumsEvents = [];
			}
		}
		return $this->_podiumsEvents;
	}

	public function setPodiumsEvents($podiumsEvents) {
		$this->_podiumsEvents = $podiumsEvents;
	}

	public function getUserSchedules($user) {
		$userSchedules = UserSchedule::model()->findAllByAttributes([
			'user_id'=>$user->id,
			'competition_id'=>$this->id,
		]);
		$schedules = CHtml::listData($userSchedules, 'id', 'schedule');
		return $this->formatSchedules($schedules);
	}

	public function getListableSchedules() {
		return $this->formatSchedules($this->schedule);
	}

	public function formatSchedules($schedules) {
		$formatedSchedules = [];
		usort($schedules, [$this, 'sortSchedules']);
		$hasGroup = false;
		$hasCutOff = false;
		$hasTimeLimit = false;
		$hasNumber = false;
		$cumulative = Yii::t('common', 'Cumulative ');
		$specialEvents = [
			'333fm'=>[],
			'333mbf'=>[],
			'submission'=>[],
		];
		foreach ($schedules as $key=>$schedule) {
			if (trim($schedule->group) != '') {
				$hasGroup = true;
			}
			if ($schedule->cut_off > 0) {
				$hasCutOff = true;
			}
			if ($schedule->time_limit > 0) {
				$hasTimeLimit = true;
			}
			if ($schedule->number > 0) {
				$hasNumber = true;
			} else {
				$schedule->number = '';
			}
			if (isset($specialEvents[$schedule->event])) {
				$specialEvents[$schedule->event][$schedule->round][] = $key;
			}
			$schedule->competition = $this;
		}
		$scheduleEvents = Events::getOnlyScheduleEvents();
		foreach ($schedules as $key=>$schedule) {
			if (isset($scheduleEvents[$schedule->event])) {
				$schedule->round = $schedule->group = $schedule->format = '';
				$schedule->cut_off = $schedule->time_limit = 0;
			}
			$event = Events::getFullEventName($schedule->event);
			$round = Yii::t('RoundTypes', RoundTypes::getFullRoundName($schedule->round));
			if (isset($specialEvents[$schedule->event][$schedule->round]) && count($specialEvents[$schedule->event][$schedule->round]) > 1) {
				$times = array_search($key, $specialEvents[$schedule->event][$schedule->round]);
				if ($times > 0) {
					$schedule->cut_off = 0;
				}
				switch ($times + 1) {
					case 1:
						$round .= Yii::t('common', ' (1st attempt)');
						break;
					case 2:
						$round .= Yii::t('common', ' (2nd attempt)');
						break;
					case 3:
						$round .= Yii::t('common', ' (3rd attempt)');
						break;
					default:
						$round .= Yii::t('common', ' ({times}th attempt)', [
							'{times}'=>$times + 1,
						]);
						break;
				}
			}
			$timeLimit = self::formatTime($schedule->time_limit);
			if ($schedule->event === '333mbf') {
				$timeLimit = Yii::t('Schedule', 'Up to 60 minutes') . ' <span class="comment">*</span>';
			} elseif ($schedule->event === '333fm') {
				$timeLimit = self::formatTime(3600);
			}
			$temp = [
				'Start Time'=>date('H:i', $schedule->start_time),
				'End Time'=>date('H:i', $schedule->end_time),
				'Event'=>$event,
				'Group'=>$schedule->group,
				'Round'=>trim($round),
				'Format'=>Yii::t('common', Formats::getFullFormatName($schedule->format)),
				'Cutoff'=>self::formatTime($schedule->cut_off, $schedule->event),
				'Time Limit'=>$timeLimit,
				'Competitors'=>$schedule->number,
				'id'=>$schedule->id,
				'event'=>$schedule->event,
				'round'=>$schedule->round,
				'schedule'=>$schedule,
			];
			if ($schedule->cumulative) {
				$temp['Time Limit'] = $cumulative . $temp['Time Limit'];
			}
			if ($hasGroup === false) {
				unset($temp['Group']);
			}
			if ($hasCutOff === false) {
				unset($temp['Cutoff']);
			}
			if ($hasTimeLimit === false) {
				unset($temp['Time Limit']);
			}
			if ($hasNumber === false) {
				unset($temp['Competitors']);
			}
			$formatedSchedules[$schedule->day][$schedule->stage][] = $temp;
		}
		return $formatedSchedules;
	}

	public function getScheduleColumns($schedules) {
		if (empty($schedules)) {
			return array();
		}
		$columns = array();
		foreach ($schedules[0] as $key=>$value) {
			if ($key == 'id' || $key == 'event' || $key == 'round' || gettype($value) === 'object') {
				continue;
			}
			$width = $this->getScheduleColumnWidth($key);
			$column = array(
				'type'=>'raw',
				'name'=>$key,
				'header'=>Yii::t('Schedule', $key),
				'headerHtmlOptions'=>array(
					'style'=>sprintf("width: %dpx;min-width: %dpx;vertical-align:bottom", $width, $width),
				),
			);
			if ($key == 'Event') {
				$column['type'] = 'raw';
				$column['value'] = 'Events::getFullEventNameWithIcon($data["event"], $data["Event"])';
			}
			if ($this->isMultiRegions && ($key == 'Start Time' || $key == 'End Time')) {
				if ($key == 'End Time') {
					continue;
				}
				$headerHtmlOptions = $column['headerHtmlOptions'];
				foreach ($this->timezones as $timezone) {
					$regions = implode('<br>', array_map(function($country) {
						return CHtml::tag('b', [], Yii::t('Region', $country->getAttributeValue('name')));
					}, $timezone['regions']));
					$column = [
						'header'=>$regions . '<br> ' .$timezone['text'],
						'headerHtmlOptions'=>$headerHtmlOptions,
						'type'=>'raw',
						'value'=>'$data["schedule"]->getTime(' . $timezone['second_offset'] . ')',
						// 'footer'=>$regions,
					];
					$columns[] = $column;
				}
				continue;
			}
			$columns[] = $column;
		}
		return $columns;
	}

	public function getIsMultiRegions() {
		return array_map(function($location) { return $location->country_id; }, $this->location) != array_fill(0, count($this->location), $this->location[0]->country_id);
	}

	public function getTimezones() {
		if ($this->_timezones === null) {
			foreach ($this->sortedLocations as $location) {
				$country = $location->country;
				if ($country) {
					$timezone = 8 + ($country->second_offset / 3600);
					// $timezone = number_format($timezone, 1);
					$timezone = $timezone >= 0 ? 'GMT +' . $timezone : 'GMT ' . $timezone;
					if (!isset($timezones[$timezone])) {
						$timezones[$timezone] = [
							'second_offset'=>$country->second_offset,
							'text'=>$timezone,
							'regions'=>[],
						];
					}
					$timezones[$timezone]['regions'][$country->id] = $country;
				}
			}
			usort($timezones, function($timezoneA, $timezoneB) {
				return $timezoneA['second_offset'] - $timezoneB['second_offset'];
			});
			$this->_timezones = $timezones;
		}
		return $this->_timezones;
	}

	private function getScheduleColumnWidth($name) {
		switch ($name) {
			case 'Start Time':
			case 'End Time':
				return 72;
			case 'Event':
				return 236;
			case 'Group':
				return 54;
			case 'Round':
				return 102;
			case 'Format':
				return 156;
			case 'Cutoff':
			case 'Time Limit':
				return 145;
			case 'Competitors':
				return 72;
		}
	}

	private function sortSchedules($scheduleA, $scheduleB) {
		$temp = $scheduleA['day'] - $scheduleB['day'];
		if ($temp == 0) {
			$temp = Schedule::getStagetWeight($scheduleA['stage']) - Schedule::getStagetWeight($scheduleB['stage']);
		}
		if ($temp == 0) {
			$temp = date('Hi', $scheduleA['start_time']) - date('Hi', $scheduleB['start_time']);
		}
		if ($temp == 0) {
			$temp = date('Hi', $scheduleA['end_time']) - date('Hi', $scheduleB['end_time']);
		}
		return $temp;
	}

	public function getOperationButton() {
		$isAdministrator = Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR);
		switch ($this->status) {
			case self::STATUS_HIDE:
			case self::STATUS_SHOW:
			case self::STATUS_LOCKED:
				$buttons[] = CHtml::link('预览', $this->getUrl('detail'), ['class'=>'btn btn-sm btn-orange btn-square tips', 'data-toggle'=>'tooltip', 'title'=>'预览', 'target'=>'_blank']);
				$buttons[] = CHtml::link('编辑', ['/board/competition/edit', 'id'=>$this->id], ['class'=>'btn btn-sm btn-blue btn-square tips', 'data-toggle'=>'tooltip', 'title'=>'编辑']);
				break;
			case self::STATUS_UNCONFIRMED:
			case self::STATUS_CONFIRMED:
			case self::STATUS_REJECTED:
				if ($this->application !== null) {
					$buttons[] = CHtml::link('查看', ['/board/competition/view', 'id'=>$this->id], ['class'=>'btn btn-orange btn-square btn-sm']);
				}
				if ($this->status == self::STATUS_UNCONFIRMED) {
					$buttons[] = CHtml::link('编辑', ['/board/competition/edit', 'id'=>$this->id], ['class'=>'btn btn-blue btn-square btn-sm']);
					$buttons[] = CHtml::link('项目', ['/board/competition/event', 'id'=>$this->id], ['class'=>'btn btn-sm btn-white btn-square tips', 'data-toggle'=>'tooltip', 'title'=>'项目']);
					$buttons[] = CHtml::link('申请资料', ['/board/competition/editApplication', 'id'=>$this->id], ['class'=>'btn btn-purple btn-square btn-sm']);
				}
				break;
		}
		return implode(' ', $buttons);
	}

	public function getOperationFeeButton() {
		if ($this->id < 382) {
			return '';
		}

		// 根据比赛日期确定每日费用
		$dailyRate = 3;
		if ($this->date < self::CUBING_FEE_BEFORE_202101) {
			$dailyRate = 1;
		} elseif ($this->date < self::CUBING_FEE_BEFORE_202507) {
			$dailyRate = 2;
		}

		$fee = $this->registeredCompetitors * $this->days * $dailyRate;
		$buttons = array();
		if (Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR)) {
			$buttons[] = CHtml::checkBox('paid', $this->paid == self::PAID, array(
				'class'=>'toggle tips',
				'data-toggle'=>'tooltip',
				'data-placement'=>'top',
				'title'=>'是否支付运营费',
				'data-id'=>$this->id,
				'data-url'=>CHtml::normalizeUrl(array('/board/competition/toggle')),
				'data-attribute'=>'paid',
				'data-value'=>$this->paid,
				'data-name'=>$this->name_zh,
			));
		}
		$buttons[] = $fee;
		return implode('', $buttons);
	}

	public function getFullEventName($event) {
		return Events::getFullEventName($event);
	}

	public function getRegistrationEvents() {
		$events = Events::getAllEvents();
		$registrationEvents = array();
		foreach ($this->associatedEvents as $key=>$value) {
			if (isset($events[$key])) {
				$registrationEvents[$key] = $events[$key];
			}
		}
		return $registrationEvents;
	}

	public function getEventsColumns($headerText = false, $showPending = false) {
		$region = 'Yii::t("Region", $data->user->country->getAttributeValue("name"))';
		if (Yii::app()->language == 'zh_cn' && $headerText) {
			$region .= '.$data->user->getRegionName($data->user->province). (in_array($data->user->province_id, array(215, 525, 567, 642)) ? "" : $data->user->getRegionName($data->user->city))';
		}
		$columns = array(
			// array(
			// 	'headerHtmlOptions'=>array(
			// 		'class'=>'battle-checkbox',
			// 	),
			// 	'header'=>Yii::t('common', 'Battle'),
			// 	'value'=>'Persons::getBattleCheckBox($data->user->getCompetitionName(), $data->user->wcaid)',
			// 	'type'=>'raw',
			// ),
			array(
				'name'=>'number',
				'header'=>'No.',
				'value'=>'$data->number',
			),
			array(
				'name'=>'name',
				'header'=>Yii::t('Results', 'Name'),
				'headerHtmlOptions'=>array(
					'class'=>'header-username',
				),
				'type'=>'raw',
				'value'=>'$data->user->getWcaLink($data->user->getAttributeValue("name", true))',
			),
			array(
				'name'=>'gender',
				'header'=>Yii::t('common', 'Gender'),
				'headerHtmlOptions'=>array(
					'class'=>'header-gender',
				),
				'type'=>'raw',
				'value'=>'$data->user->getGenderText()',
			),
			array(
				'name'=>'country_id',
				'header'=>Yii::t('common', 'Region'),
				'headerHtmlOptions'=>array(
					'class'=>'header-region',
				),
				'type'=>'raw',
				'value'=>$region,
			),
		);
		if ($this->isMultiLocation()) {
			$columns[] = array(
				'name'=>'location_id',
				'header'=>Yii::t('common', 'Competition Site'),
				'headerHtmlOptions'=>array(
					'class'=>'header-location',
				),
				'type'=>'raw',
				'value'=>"\$data->location->getFullAddress(false, {$this->isMultiRegions} + 0)",
			);
		}
		$showPending = json_encode($showPending);
		foreach ($this->associatedEvents as $event=>$value) {
			$columns[] = array(
				'name'=>(string)$event,
				'header'=>Events::getEventIcon($event),
				'headerHtmlOptions'=>array(
					'class'=>'header-event',
				),
				'htmlOptions'=>Yii::app()->controller->sGet('sort') === "$event" ? array(
					'class'=>'hover',
				) : array(),
				'type'=>'raw',
				'value'=>"\$data->getEventString('${event}', {$showPending})",
			);
		}
		return $columns;
	}

	public function handleDate() {
		foreach ([
					 'date', 'end_date', 'reg_start', 'reg_end',
					 'second_stage_date', 'third_stage_date', 'qualifying_end_time',
					 'cancellation_end_time', 'reg_reopen_time',
				 ] as $attribute) {
			if ($this->$attribute != '') {
				$date = strtotime($this->$attribute);
				if ($date !== false) {
					$this->$attribute = $date;
				} else {
					$this->$attribute = 0;
				}
			} else {
				$this->$attribute = 0;
			}
		}
	}

	public function formatDate() {
		if (!ctype_digit($this->date)) {
			return;
		}
		foreach (array('date', 'end_date') as $attribute) {
			if (!empty($this->$attribute)) {
				$this->$attribute = date('Y-m-d', $this->$attribute);
			} else {
				$this->$attribute = '';
			}
		}
		foreach ([
					 'reg_start', 'reg_end', 'second_stage_date', 'third_stage_date',
					 'qualifying_end_time', 'cancellation_end_time', 'reg_reopen_time',
				 ] as $attribute) {
			if (!empty($this->$attribute)) {
				$this->$attribute = date('Y-m-d H:i:s', $this->$attribute);
			} else {
				$this->$attribute = '';
			}
		}
	}

	public function formatSchedule() {
		if (empty($this->schedules)) {
			return;
		}
		$schedules = array();
		$oldSchedules = $this->schedules;
		foreach ($oldSchedules['start_time'] as $key=>$value) {
			$schedules[] = array(
				'start_time'=>strtotime($oldSchedules['start_time'][$key]),
				'end_time'=>strtotime($oldSchedules['end_time'][$key]),
				'day'=>isset($oldSchedules['day'][$key]) ? $oldSchedules['day'][$key] : 1,
				'stage'=>isset($oldSchedules['stage'][$key]) ? $oldSchedules['stage'][$key] : 'main',
				'event'=>isset($oldSchedules['event'][$key]) ? $oldSchedules['event'][$key] : '',
				'group'=>isset($oldSchedules['group'][$key]) ? $oldSchedules['group'][$key] : '',
				'round'=>isset($oldSchedules['round'][$key]) ? $oldSchedules['round'][$key] : '',
				'format'=>isset($oldSchedules['format'][$key]) ? $oldSchedules['format'][$key] : '',
				'number'=>isset($oldSchedules['number'][$key]) ? $oldSchedules['number'][$key] : '',
				'cut_off'=>isset($oldSchedules['cut_off'][$key]) ? $oldSchedules['cut_off'][$key] : '',
				'time_limit'=>isset($oldSchedules['time_limit'][$key]) ? $oldSchedules['time_limit'][$key] : '',
				'cumulative'=>isset($oldSchedules['cumulative'][$key]) ? $oldSchedules['cumulative'][$key] : '',
			);
		}
		$this->schedules = $schedules;
	}

	public function initLiveData($force = false) {
		$attributes = array('competition_id'=>$this->id);
		if (!$force && LiveEventRound::model()->countByAttributes($attributes) > 0) {
			return;
		}
		LiveResult::model()->deleteAllByAttributes($attributes);
		LiveEventRound::model()->deleteAllByAttributes($attributes);
		$schedules = array();
		$temp = $this->schedule;
		usort($temp, array($this, 'sortSchedules'));
		foreach ($temp as $schedule) {
			$schedules[$schedule->event][$schedule->round] = $schedule;
		}
		unset($temp);
		//events and rounds
		$rounds = array();
		foreach ($this->associatedEvents as $event=>$value) {
			if (isset($schedules[$event])) {
				$first = current($schedules[$event]);
				$rounds[$event] = $first->round;
				foreach ($schedules[$event] as $schedule) {
					$model = new LiveEventRound();
					$model->competition_id = $schedule->competition_id;
					$model->event = $schedule->event;
					$model->round = $schedule->round;
					$model->format = $schedule->getRealFormat();
					$model->cut_off = $schedule->cut_off;
					$model->time_limit = $schedule->time_limit;
					$model->number = $schedule->number;
					$model->status = LiveEventRound::STATUS_OPEN;
					$model->save();
				}
			}
		}
		//empty results of first rounds
		$registrations = Registration::getRegistrations($this);
		foreach ($registrations as $registration) {
			foreach ($registration->getAcceptedEvents() as $registrationEvent) {
				$event = $registrationEvent->event;
				if (!isset($rounds[$event])) {
					continue;
				}
				$model = new LiveResult();
				$model->competition_id = $this->id;
				$model->user_id = $registration->user_id;
				$model->number = $registration->number;
				$model->event = $event;
				$model->round = $rounds[$event];
				$model->save();
			}
		}
	}

	public function getEventsRounds() {
		$eventRounds = LiveEventRound::model()->findAllByAttributes(array(
			'competition_id'=>$this->id,
		), array(
			'order'=>'id ASC',
		));
		$events = array();
		$ranks = [];
		foreach ($eventRounds as $eventRound) {
			if (!isset($events[$eventRound->event])) {
				$events[$eventRound->event] = array(
					'i'=>$eventRound->event,
					'name'=>Events::getFullEventName($eventRound->event),
					'rs'=>array(),
				);
			}
			$attributes = $eventRound->getBroadcastAttributes();
			$attributes['name'] = Yii::t('RoundTypes', RoundTypes::getFullRoundName($eventRound->round));
			$attributes['allStatus'] = $eventRound->allStatus;
			if (!isset($ranks[$eventRound->round])) {
				$ranks[$eventRound->round] = $eventRound->wcaRound->rank;
			}
			$events[$eventRound->event]['rs'][] = $attributes;
		}
		foreach ($events as $event=>$eventRound) {
			usort($eventRound['rs'], function($roundA, $roundB) use($ranks) {
				return $ranks[$roundA['i']] - $ranks[$roundB['i']];
			});
			$events[$event] = $eventRound;
		}
		return array_values($events);
	}

	public function getLastActiveEventRound($events) {
		$liveResult = LiveResult::model()->findByAttributes(array(
			'competition_id'=>$this->id,
			// 'status'=>LiveResult::STATUS_NORMAL,
		), array(
			'condition'=>'update_time > 0',
			'order'=>'update_time DESC',
		));
		if ($liveResult !== null) {
			return array(
				'e'=>$liveResult->event,
				'r'=>$liveResult->round,
				'filter'=>'all',
			);
		}
		$event = current($events);
		if ($event === false) {
			return array(
				'e'=>'',
				'r'=>'',
				'filter'=>'all',
			);
		}
		return array(
			'e'=>$event['i'],
			'r'=>$event['rs'][0]['i'],
			'filter'=>'all',
		);
	}

	public function getUnofficialGroups() {
		$groups = [];
		$hasUGroups = false;
		$ages = self::getPodiumAges();
		foreach ($ages as $age) {
			if ($this->{'podiums_u' . $age}) {
				$hasUGroups = true;
				$groups[] = 'U' . $age;
			}
		}
		$o_ages = self::getPodiumOldAges('sort');
		foreach ($o_ages as $age) {
			$groups[] = 'O'.$age;
		}

		if (!$hasUGroups && $this->podiums_children) {
			$groups[] = Yii::t('live', 'Children');
		}
		if ($this->podiums_females) {
			$groups[] = Yii::t('live', 'Females');
		}
		if ($this->podiums_new_comers) {
			$groups[] = Yii::t('live', 'New Comers');
		}
		return $groups;
	}

	public function getYearsAgosDate($year, $offset = 0) {
		$lastDate = $this->end_date ?: $this->date;
		$date = strtotime(date('Y-m-d', $lastDate + $offset) . " {$year} years ago");
		if ($offset === 0 && date('m-d', $lastDate) === '02-29' && date('m-d', $date) === '03-01') {
			$date -= 86400;
		}
		return $date;
	}

	public function getLivePodiums() {
		$eventRounds = LiveEventRound::model()->findAllByAttributes(array(
			'competition_id'=>$this->id,
			'round'=>['c', 'f'],
			'status'=>LiveEventRound::STATUS_FINISHED,
		), array(
			'order'=>'id ASC',
		));
		$podiums = [];
		$greaterChinaPodiums = [];
		foreach ($eventRounds as $eventRound) {
			switch ($eventRound->format) {
				case '1':
				case '2':
				case '3':
					$order = 'best ASC';
					$format = 'b';
					break;
				case 'a':
				case 'm':
				default:
					$format = 'a';
					$order = 'average > 0 DESC, average ASC, best ASC';
					break;
			}
			$results = LiveResult::model()->findAllByAttributes([
				'competition_id'=>$this->id,
				'event'=>$eventRound->event,
				'round'=>$eventRound->round,
			], [
				'condition'=>'best > 0',
				'order'=>$order,
			]);
			$count = 0;
			$lastBest = 0;
			$lastAverage = 0;
			foreach ($results as $i=>$result) {
				if ($format == 'a') {
					if ($result->average != $lastAverage) {
						$lastAverage = $result->average;
						$lastBest = $result->best;
						$result->pos = $i + 1;
						$count = $i;
					} elseif ($result->best != $lastBest) {
						$lastBest = $result->best;
						$result->pos = $i + 1;
						$count = $i;
					} else {
						$result->pos = $count + 1;
					}
				} else {
					if ($result->best != $lastBest) {
						$lastBest = $result->best;
						$result->pos = $i + 1;
						$count = $i;
					} else {
						$result->pos = $count + 1;
					}
				}
				if ($result->pos <= $this->podiums_num) {
					$podiums[$eventRound->event][] = clone $result;
				}
				if ($this->podiums_greater_china && $result->user->isGreaterChinese()) {
					$greaterChinaPodiums[$eventRound->event][] = clone $result;
				}
			}
		}
		foreach ($greaterChinaPodiums as $event=>$results) {
			$count = 0;
			$lastBest = 0;
			$lastAverage = 0;
			$temp = [];
			foreach ($results as $i=>$result) {
				if ($result->average != $lastAverage) {
					$lastAverage = $result->average;
					$lastBest = $result->best;
					$result->pos = $i + 1;
					$count = $i;
				} elseif ($result->best != $lastBest) {
					$lastBest = $result->best;
					$result->pos = $i + 1;
					$count = $i;
				} else {
					$result->pos = $count + 1;
				}
				if ($result->pos > $this->podiums_num) {
					break;
				}
				$temp[] = $result;
			}
			$greaterChinaPodiums[$event] = $temp;
		}
		foreach ($this->podiumsEvents as $event) {
			// females, children and new comers
			$firstRound = $this->getFirstRound($event);
			$roundIds = $firstRound ? $firstRound->round : ['1', 'd'];
			$eventRound = LiveEventRound::model()->findByAttributes([
				'competition_id'=>$this->id,
				'event'=>$event,
				'round'=>$roundIds,
				'status'=>LiveEventRound::STATUS_FINISHED,
			]);
			if ($eventRound !== null) {
				$results = LiveResult::model()->with('user')->findAllByAttributes([
					'competition_id'=>$this->id,
					'event'=>$event,
					'round'=>$eventRound->round,
				], [
					'condition'=>'best > 0',
					'order'=>'average > 0 DESC, average ASC, best ASC',
				]);
				$ages = self::getPodiumAges();
				$o_ages = self::getPodiumOldAges('rsort');
				$temp = [
					Yii::t('live', 'Children')=>[],
					Yii::t('live', 'Females')=>[],
					Yii::t('live', 'New Comers')=>[],
				];
				$year = date('Y', $this->date);
				$monthAndDay = date('-m-d', $this->date);
				$podiumsChildren = $this->podiums_children;
				$dobs = [];
				foreach ($ages as $age) {
					$temp['U' . $age] = [];
					$podiumsChildren = $podiumsChildren && !($this->{'podiums_u' . $age});
					$dobs[$age] = strtotime(($year - $age) . $monthAndDay);
				}
				foreach ($o_ages as $age) {
					$temp['O'.$age] = [];
					$dobs[$age] = strtotime(($year - $age) . $monthAndDay);
				}

				$u12 = strtotime(($year - 12) . $monthAndDay);
				$currentYear = date('Y', $this->date);
				foreach ($results as $result) {
					//ignore non greater chinese user
					if ($this->podiums_greater_china && !$result->user->isGreaterChinese()) {
						continue;
					}
					$birthday = $result->user->birthday;
					if ($result->user->gender == User::GENDER_FEMALE && $this->podiums_females) {
						$temp[Yii::t('live', 'Females')][] = clone $result;
					}
					if ($birthday > $u12 && $podiumsChildren) {
						$temp[Yii::t('live', 'Children')][] = clone $result;
					}
					if (($result->user->wcaid === '' || ($this->newcomer && substr($result->user->wcaid, 0, 4) == $currentYear)) && $this->podiums_new_comers) {
						$temp[Yii::t('live', 'New Comers')][] = clone $result;
					}
					foreach ($ages as $age) {
						if ($birthday > $dobs[$age] && $this->{'podiums_u' . $age}) {
							$temp['U' . $age][] = clone $result;
							break;
						}
					}
					// that logic here is different from U the above. GetPodiumOldAges has already filtered the data once.
					foreach ($o_ages as $age) {
						if ($birthday < $dobs[$age] ) {
							$temp['O' . $age][] = clone $result;
							break;
						}
					}
				}
				foreach ($temp as $group=>$results) {
					$count = 0;
					$lastBest = 0;
					$lastAverage = 0;
					foreach ($results as $i=>$result) {
						if ($result->average != $lastAverage) {
							$lastAverage = $result->average;
							$result->pos = $i + 1;
							$count = $i;
						} elseif ($result->best != $lastBest) {
							$lastBest = $result->best;
							$result->pos = $i + 1;
							$count = $i;
						} else {
							$result->pos = $count + 1;
						}
						if ($result->pos > $this->podiums_num) {
							break;
						}
						$result->subEventTitle = ' ' . $group . Yii::t('live', ' ({round})', [
								'{round}'=>Yii::t('RoundTypes', 'First round'),
							]);
						if ($this->podiums_greater_china) {
							$greaterChinaPodiums['unofficial'][] = $result;
						} else {
							$podiums['unofficial'][] = $result;
						}
					}
				}
			}
		}
		return [
			'podiums'=>$podiums,
			'greaterChinaPodiums'=>$greaterChinaPodiums,
		];
	}

	public function computeRecords($event, $type = 'best') {
		$results = LiveResult::model()->findAllByAttributes([
			'competition_id'=>$this->id,
			'event'=>$event,
		], [
			'condition'=>"{$type} > 0",
			'order'=>"{$type} ASC",
		]);
		usort($results, function($resultA, $resultB) use ($type) {
			$temp = $this->getRoundDate($resultA->event, $resultA->round)
				- $this->getRoundDate($resultB->event, $resultB->round);
			if ($temp == 0) {
				$temp = $resultA->$type - $resultB->$type;
			}
			return $temp;
		});
		//get region winners
		$dateRegionalWinners = [];
		$regionalWinners = [];
		foreach ($results as $result) {
			$user = $result->user;
			$countryId = $user->country_id;
			$date = $this->getRoundDate($event, $result->round);
			if (!isset($dateRegionalWinners[$date][$countryId])) {
				if (isset($regionalWinners[$countryId]) && $regionalWinners[$countryId]->$type < $result->$type) {
					continue;
				}
				$regionalWinners[$countryId] = $result;
				$dateRegionalWinners[$date][$countryId][] = $result;
			} else {
				$winner = $dateRegionalWinners[$date][$countryId][0];
				if ($winner->$type === $result->$type) {
					$dateRegionalWinners[$date][$countryId][] = $result;
				}
			}
		}
		$WR = Results::getRecord('World', $event, $type, $this->date);
		if (DEV) {
			Yii::log(json_encode($WR), 'debug', 'WR' . date('Y-m-d', $this->date));
		}
		$regionRecords = [];
		$records = [];
		$attribute = sprintf('regional_%s_record', $type == 'best' ? 'single' : 'average');
		foreach ($dateRegionalWinners as $dateWinners) {
			foreach ($dateWinners as $countryId=>$results) {
				$result = $results[0];
				$value = $result->$type;
				$user = $result->user;
				$wcaCountry = $user->country->wcaCountry;
				$NR = Results::getRecord($wcaCountry->id, $event, $type, $this->date);
				if (DEV) {
					Yii::log(json_encode($NR), 'debug', 'NR' . date('Y-m-d', $this->date));
				}
				if ($NR == null || $value <= $NR[$type]) {
					$continent = $wcaCountry->continent;
					$crName = $continent->recordName;
					$$crName = Results::getRecord($continent->name, $event, $type, $this->date);
					// check WR CR NR
					$recordSet = false;
					foreach (['WR', $crName, 'NR'=>$wcaCountry->id] as $recordName=>$region) {
						if (is_numeric($recordName)) {
							$recordName = $region;
						}
						if ($recordSet) {
							// set smaller regional record
							$regionRecords[$region] = $value;
							continue;
						}
						// check if value sub former record
						if ($$recordName !== null && $value <= $$recordName[$type]
							//we should be careful if no record were fetched
							|| $$recordName === null && $recordName == 'NR'
						) {
							// if two persons broke a same bigger record
							// the worse one should get a smaller record
							// example: A got a sub WR result 5.00, B got another sub WR result 5.01
							// A should be WR. if A and B were in the same continent, then B should be NR
							// if not, B should be CR
							if (!isset($regionRecords[$region]) || $value <= $regionRecords[$region]) {
								$record = $recordName;
								$regionRecords[$region] = $value;
								$recordSet = true;
							}
						}
					}
					if ($recordSet) {
						foreach ($results as $result) {
							$result->$attribute = $record;
							$records[$result->id] = $result;
						}
					}
				}
			}
		}
		$oldRecords = LiveResult::model()->findAllByAttributes([
			'competition_id'=>$this->id,
			'event'=>$event,
		], [
			'condition'=>"{$attribute} != ''",
		]);
		$temp = [];
		foreach ($oldRecords as $result) {
			if (!isset($records[$result->id])) {
				$temp[] = $result;
				$result->$attribute = '';
				$result->save();
			} elseif ($records[$result->id]->$attribute == $result->$attribute) {
				unset($records[$result->id]);
			}
		}
		foreach ($records as $result) {
			$result->save();
		}
		return [
			'updated'=>$records,
			'removed'=>$temp,
		];
	}

	public function getRoundDate($event, $round) {
		static $dates;
		if ($dates === null) {
			foreach ($this->schedule as $schedule) {
				$dates[$schedule->event][$schedule->round] = $this->date + ($schedule->day - 1) * 86400;
			}
		}
		return isset($dates[$event][$round]) ? $dates[$event][$round] : $this->date;
	}

	public function getAllScheduleRounds() {
		if ($this->_scheduledRounds === null) {
			foreach ($this->schedule as $schedule) {
				$this->_scheduledRounds[$schedule->event][$schedule->round] = $schedule;
			}
		}
		return $this->_scheduledRounds;
	}

	public function getScheduledRound($event, $round) {
		$rounds = $this->getAllScheduleRounds();
		return isset($rounds[$event][$round]) ? $rounds[$event][$round] : null;
	}

	public function getFirstRound($event) {
		$rounds = $this->getAllScheduleRounds();
		if (!isset($rounds[$event])) {
			return null;
		}
		foreach ($rounds[$event] as $round) {
			return $round;
		}
		return null;
	}

	public function checkPermission($user, $scope = 'default') {
		if ($user === null) {
			return false;
		}
		if ($user->isAdministrator()) {
			return true;
		}
		if ($user->isWCADelegate() && array_search($user->id, $this->delegates) !== false) {
			return true;
		}
		if ($scope == 'default' && array_search($user->id, $this->organizers) !== false) {
			return true;
		}
		return false;
	}

	public function lock() {
		$this->status = self::STATUS_LOCKED;
	}

	public function hide() {
		$this->status = self::STATUS_HIDE;
	}

	public function announce() {
		$this->status = self::STATUS_SHOW;
		$this->attachEventHandler('onAfterSave', function($event) {
			if ($this->announcement_posted == self::YES) {
				return;
			}
			$news = new News();
			$template = NewsTemplate::model()->findByPk(1);
			if (!$template) {
				return;
			}
			//post annoucement
			$data = $this->generateTemplateData();
			$contents = $template->render($data);
			foreach ($contents as $key=>$value) {
				if ($news->hasAttribute($key) && $key !== $news->getTableSchema()->primaryKey) {
					$news->$key = $value;
				}
			}
			$news->description = $news->description_zh = '';
			$news->user_id = Yii::app()->user->id;
			$news->date = time();
			$news->status = News::STATUS_SHOW;
			$news->formatDate();
			try {
				$news->save();
			} catch (Exception $e) {

			}
		});
	}

	public function __toJson($full = false) {
		$data = [
			'id'=>$this->id,
			'name'=>$this->getAttributeValue('name'),
			'type'=>$this->type,
			'alias'=>$this->alias,
			'url'=>CHtml::normalizeUrl($this->getUrl()),
			'date'=>[
				'from'=>$this->date,
				'to'=>$this->end_date ?: $this->date,
			],
			'locations'=>JsonHelper::formatData($this->location),
			'registration'=>[
				'from'=>$this->reg_start,
				'to'=>$this->reg_end,
			],
			'competitor_limit'=>$this->person_num,
			'registered_competitors'=>$this->registeredCompetitors,
			'live'=>$this->live,
		];
		if ($full) {
			$data += [
				'wca_competition_id'=>$this->wca_competition_id,
				'organizers'=>$this->organizer,
				'delegates'=>$this->delegate,
				'base_entry_fee'=>$this->entry_fee,
				'second_phase_time'=>$this->second_stage_date,
				'second_phase_fee'=>$this->getEventFee(self::EVENT_FEE_ENTRY, self::STAGE_SECOND),
				'third_phase_time'=>$this->third_stage_date,
				'third_phase_fee'=>$this->getEventFee(self::EVENT_FEE_ENTRY, self::STAGE_THIRD),
				'events'=>$this->allEvents,
				'information'=>$this->getAttributeValue('information'),
				'regulations'=>$this->getAttributeValue('regulations'),
				'travel'=>$this->getAttributeValue('travel'),
			];
			$data['registration'] += [
				'cancellation_end_time'=>$this->cancellation_end_time,
				'reopen_time'=>$this->reg_reopen_time,
			];
		}
		return $data;
	}

	public function getWCIF() {
		$schedules = $this->schedule;
		usort($schedules, [$this, 'sortSchedules']);
		$specialEvents = [
			'333fm'=>[],
			'333mbf'=>[],
		];
		foreach ($schedules as $key=>$schedule) {
			$eventSchedules[$schedule->event][$schedule->round] = $schedule;
			if (isset($specialEvents[$schedule->event])) {
				$specialEvents[$schedule->event][$schedule->round][] = $key;
			}
		}
		$wcaEvents = Events::getNormalEvents();
		$events = [];
		foreach ($wcaEvents as $event=>$name) {
			$temp = [
				'id'=>"$event",
				'rounds'=>[],
			];
			if (isset($this->associatedEvents[$event])) {
				$eventSchedules[$event] = array_values($eventSchedules[$event]);
				for ($i = 1; $i <= $this->associatedEvents[$event]['round']; $i++) {
					$schedule = $eventSchedules[$event][$i - 1];
					$format = substr($schedule->format, -1);
					$cumulativeRoundIds = [];
					if ($schedule->cumulative) {
						$cumulativeRoundIds[] = "$event";
					}
					$round = [
						'id'=>"{$event}-r{$i}",
						'format'=>$format,
						'timeLimit'=>[
							'centiseconds'=>$schedule->time_limit * 100,
							'cumulativeRoundIds'=>$cumulativeRoundIds,
						],
						'cutoff'=>$schedule->cut_off > 0 ? [
							'numberOfAttempts'=>(int)substr($schedule->format, 0, 1),
							'attemptResult'=>$schedule->cut_off * 100,
						] : null,
						'advancementCondition'=>isset($eventSchedules[$event][$i]) ? [
							'type'=>'ranking',
							'level'=>(int)$eventSchedules[$event][$i]->number,
						] : null,
						'scrambleGroupCount'=>1,
						'scrambleSetCount'=>1,
						'roundResults'=>[],
					];
					$temp['rounds'][] = $round;
				}
			} else {
				$temp['rounds'] = null;
			}
			$events[] = $temp;
		}
		$activities = [];
		$rounds = [];
		$ids = [];
		$codes = ["registration", "tutorial", "breakfast", "lunch", "dinner", "awards", "misc"];
		$rooms = [];
		foreach ($schedules as $key=>$schedule) {
			if (isset($wcaEvents[$schedule->event])) {
				$round = $this->getRoundNumber($schedule->event, $schedule->round);
				$activityCode = "{$schedule->event}-r{$round}";
			} elseif (in_array($schedule->event, $codes)) {
				$activityCode = "other-{$schedule->event}";
			} else {
				$activityCode = "other-misc";
			}
			if (isset($specialEvents[$schedule->event][$schedule->round])) {
				$times = array_search($key, $specialEvents[$schedule->event][$schedule->round]);
				if ($times > 0) {
					$schedule->cut_off = 0;
				}
				$activityCode .= '-a' . ($times + 1);
			}
			$stage = $schedule->stage;
			if (!isset($ids[$stage])) {
				$ids[$stage] = 0;
			}
			$rooms[$stage][] = [
				'id'=>++$ids[$stage],
				'name'=>Events::getFullEventName($schedule->event),
				'activityCode'=>$activityCode,
				'startTime'=>sprintf("%sT%s+08:00", date('Y-m-d', $this->date + ($schedule->day - 1) * 86400), date('H:i:s', $schedule->start_time)),
				'endTime'=>sprintf("%sT%s+08:00", date('Y-m-d', $this->date + ($schedule->day - 1) * 86400), date('H:i:s', $schedule->end_time)),
				'childActivities'=>[],
			];
		}
		$location = $this->location[0];
		$venue = explode(',', $location->venue);
		$schedule = [
			'startDate'=>date('Y-m-d', $this->date),
			'numberOfDays'=>$this->days,
			'venues'=>[
				[
					'id'=>1,
					'name'=>trim($venue[0]),
					'countryIso2'=>'CN',
					'latitudeMicrodegrees'=>(int)($location->latitude * 1e6),
					'longitudeMicrodegrees'=>(int)($location->longitude * 1e6),
					'timezone'=>'Asia/Shanghai',
					'rooms'=>array_values(array_map(function($activities, $stage, $id) {
						return [
							'id'=>$id,
							'name'=>trim(strip_tags(Schedule::getStageText($stage))),
							'color'=>Schedule::getStageColor($stage),
							'activities'=>$activities,
						];
					}, $rooms, array_keys($rooms), range(1, count($rooms)))),
				],
			],
		];
		return [
			'events'=>$events,
			'schedule'=>$schedule,
		];
	}

	private function getRoundNumber($event, $round) {
		$roundNum = $this->associatedEvents[$event]['round'];
		switch ($round) {
			case '1':
			case 'd':
				return 1;
			case '2':
			case 'e':
				return 2;
			case '3':
			case 'g':
				return 3;
			case 'f':
			case 'd':
				return $roundNum;
			default:
				return 1;
		}
	}

	public function generateTemplateData() {
		$data = array(
			'competition'=>$this,
		);
		if ($this->wca_competition_id == '') {
			return $data;
		}
		$events = CHtml::listData(Results::model()->findAllByAttributes(array(
			'competitionId'=>$this->wca_competition_id,
		), array(
			'group'=>'eventId',
			'select'=>'eventId,COUNT(1) AS average'
		)), 'eventId', 'average');
		if ($events === array()) {
			return $data;
		}
		arsort($events);
		$eventId = array_keys($events)[0];
		$primaryEvents = array(
			'333',
			'777',
			'666',
			'555',
			'444',
			'222',
			'333fm',
			'333oh',
			'333ft',
			'333bf',
			'444bf',
			'555bf',
		);
		foreach ($primaryEvents as $event) {
			if (isset($this->associatedEvents[$event])) {
				$eventId = $event;
				break;
			}
		}
		$results = Results::model()->findAllByAttributes(array(
			'competitionId'=>$this->wca_competition_id,
			'roundTypeId'=>array(
				'c',
				'f',
			),
			'eventId'=>$eventId,
			'pos'=>array(1, 2, 3),
		), array(
			'order'=>'eventId, pos',
		));
		if (count($results) < 3) {
			return $data;
		}
		$event = new stdClass();
		$event->name = Events::getEventName($eventId);
		$event->name_zh = Yii::t('event', $event->name);
		$data['event'] = $event;
		$winners = array('winner', 'runnerUp', 'secondRunnerUp');
		foreach ($winners as $key=>$top3) {
			$data[$top3] = $this->makePerson($results[$key]);
		}
		$data['records'] = array();
		$data['records_zh'] = array();
		$recordResults = Results::model()->with('event')->findAllByAttributes(array(
			'competitionId'=>$this->wca_competition_id,
		), array(
			'condition'=>'regionalSingleRecord !="" OR regionalAverageRecord !=""',
			'order'=>'event.`rank` ASC, best ASC, average ASC',
		));
		$records = array();
		foreach ($recordResults as $record) {
			if ($record->regionalSingleRecord) {
				$records[$record->regionalSingleRecord]['single'][] = $record;
			}
			if ($record->regionalAverageRecord) {
				$records[$record->regionalAverageRecord]['average'][] = $record;
			}
		}
		foreach ($records as $region=>$record) {
			if (isset($record['single'])) {
				$records[$region]['single'] = $this->filterRecords($record['single'], 'best', $region);
			}
			if (isset($record['average'])) {
				$records[$region]['average'] = $this->filterRecords($record['average'], 'average', $region);
			}
		}
		if (isset($records['WR'])) {
			$rec = $this->makeRecords($records['WR']);
			$data['records'][] = sprintf('World records: %s.', $rec['en']);
			$data['records_zh'][] = sprintf('世界纪录：%s。', $rec['zh']);
		}
		$continents = array(
			'AfR'=>array(
				'en'=>'Africa',
				'zh'=>'非洲',
			),
			'AsR'=>array(
				'en'=>'Asia',
				'zh'=>'亚洲',
			),
			'OcR'=>array(
				'en'=>'Oceania',
				'zh'=>'大洋洲',
			),
			'ER'=>array(
				'en'=>'Europe',
				'zh'=>'欧洲',
			),
			'NAR'=>array(
				'en'=>'North America',
				'zh'=>'北美洲',
			),
			'SAR'=>array(
				'en'=>'South America',
				'zh'=>'南美洲',
			),
		);
		foreach ($continents as $cr=>$continent) {
			if (isset($records[$cr])) {
				$rec = $this->makeRecords($records[$cr]);
				$data['records'][] = sprintf('%s records: %s.', $continent['en'], $rec['en']);
				$data['records_zh'][] = sprintf('%s纪录：%s。', $continent['zh'], $rec['zh']);
			}
		}
		if (isset($records['NR'])) {
			$rec = $this->makeRecords($records['NR'], true);
			foreach ($rec['en'] as $country=>$re) {
				$re = implode(', ', $re);
				$data['records'][] =sprintf('%s records: %s.', $country, $re);
			}
			foreach ($rec['zh'] as $country=>$re) {
				$re = implode('；', $re);
				$country = Yii::t('Region', $country);
				$data['records_zh'][] =sprintf('%s纪录：%s。', $country, $re);
			}
		}
		$data['records'] = implode('<br>', $data['records']);
		$data['records_zh'] = implode('<br>', $data['records_zh']);
		if (!empty($data['records'])) {
			$data['records'] = '<br>' . $data['records'];
			$data['records_zh'] = '<br>' . $data['records_zh'];
		}
		return $data;
	}

	private function filterRecords($records, $attribute, $region) {
		$temp = array();
		$region = strtoupper($region);
		foreach ($records as $record) {
			if ($region !== 'NR') {
				if (!isset($temp[$record->eventId])) {
					$temp[$record->eventId] = $record;
				}
			} else {
				if (!isset($temp[$record->personCountryId][$record->eventId])) {
					$temp[$record->personCountryId][$record->eventId] = $record;
				}
			}
		}
		if ($region === 'NR') {
			$temp = call_user_func_array('array_merge', array_values(array_map('array_values', $temp)));
		}
		return $temp;
	}

	private function makePerson($result, $appendUnit = true, $type = 'both') {
		switch ($type) {
			case 'average':
				$score = $result->average;
				break;
			case 'single':
				$score = $result->best;
				break;
			default:
				$score = $result->average ?: $result->best;
				break;
		}
		$temp = new stdClass();
		$temp->name = $result->personName;
		$temp->name_zh = preg_match('{\((.*?)\)}i', $result->personName, $matches) ? $matches[1] : $result->personName;
		$temp->link = CHtml::link($temp->name, array('/results/p', 'id'=>$result->personId), array());
		$temp->link_zh = CHtml::link($temp->name_zh, array('/results/p', 'id'=>$result->personId), array());
		$temp->score = Results::formatTime($score, $result->eventId);
		$temp->score_zh = $temp->score;
		if ($appendUnit && is_numeric($temp->score)) {
			switch ($result->eventId) {
				case '333fm':
					$unit = array(
						'en'=>' turns',
						'zh'=>'步',
					);
					break;
				default:
					$unit = array(
						'en'=>' seconds',
						'zh'=>'秒',
					);
					break;
			}
			$temp->score .= $unit['en'];
			$temp->score_zh .= $unit['zh'];
		}
		return $temp;
	}

	private function makeRecords($records, $isNR = false) {
		$rec = array(
			'en'=>array(),
			'zh'=>array(),
		);
		foreach ($records as $type=>$recs) {
			foreach ($recs as $result) {
				$eventName = Events::getEventName($result->eventId);
				$temp = $this->makePerson($result, true, $type);
				$enRec = sprintf('%s %s %s (%s)',
					$temp->link,
					$eventName,
					$temp->score,
					$type
				);
				$zhRec = sprintf('%s的%s纪录（%s），创造者%s',
					Yii::t('event', $eventName),
					$type === 'average' ? '平均' : '单次',
					$temp->score_zh,
					$temp->link_zh
				);
				if ($isNR) {
					$rec['en'][$result->personCountryId][] = $enRec;
					$rec['zh'][$result->personCountryId][] = $zhRec;
				} else {
					$rec['en'][] = $enRec;
					$rec['zh'][] = $zhRec;
				}
			}
		}
		if (!$isNR) {
			$rec['en'] = implode(', ', $rec['en']);
			$rec['zh'] = implode('；', $rec['zh']);
		}
		return $rec;
	}

	protected function beforeValidate() {
		$this->handleDate();
		return parent::beforeValidate();
	}

	protected function beforeSave() {
		if (date('Y-m-d', $this->date) === date('Y-m-d', $this->end_date)) {
			$this->end_date = 0;
		}
		$this->name = trim(preg_replace('{ +}', ' ', $this->name));
		$this->name_zh = trim(preg_replace('{ +}', ' ', $this->name_zh));
		$this->alias = str_replace(' ', '-', $this->name);
		$this->alias = preg_replace('{[^-a-z0-9]}i', '', $this->alias);
		$this->alias = preg_replace('{-+}i', '-', $this->alias);
		$this->podiums_events = json_encode($this->podiumsEvents);
		return parent::beforeSave();
	}

	protected function afterSave() {
		parent::afterSave();
		if (Yii::app() instanceof CConsoleApplication) {
			return;
		}
		$isAdmin = Yii::app()->user->checkRole(User::ROLE_DELEGATE) || Yii::app()->user->checkPermission('caqa_member');
		// organizer team members and score takers
		foreach (['organizerTeamMember'=>'CompetitionOrganizerTeamMember', 'scoreTaker'=>'ScoreTaker'] as $attribute=>$modelName) {
			$oldMembers = array_values(CHtml::listData($this->$attribute, 'user_id', 'user_id'));
			$newMembers = array_filter(array_values((array)$this->{$attribute . 's'}));
			sort($oldMembers);
			sort($newMembers);
			if ($oldMembers != $newMembers) {
				foreach ($oldMembers as $value) {
					if (!in_array($value, $newMembers) && (!$this->isAccepted() || $isAdmin)) {
						$modelName::model()->deleteAllByAttributes(array(
							'competition_id'=>$this->id,
							'user_id'=>$value,
						));
					}
				}
				foreach ($newMembers as $value) {
					if (!in_array($value, $oldMembers)) {
						$model = new $modelName();
						$model->competition_id = $this->id;
						$model->user_id = $value;
						$model->save();
					}
				}
			}
		}
		//处理代表和主办
		foreach (array('organizer', 'delegate') as $attribute) {
			$attributeId = $attribute . '_id';
			$oldValues = array_values(CHtml::listData($this->$attribute, $attributeId, $attributeId));
			$newValues = array_values((array)$this->{$attribute . 's'});
			sort($oldValues);
			sort($newValues);
			if ($oldValues != $newValues) {
				$modelName = 'Competition' . ucfirst($attribute);
				foreach ($oldValues as $value) {
					if (!in_array($value, $newValues) && (!$this->isAccepted() || $isAdmin)) {
						$modelName::model()->deleteAllByAttributes(array(
							'competition_id'=>$this->id,
							$attributeId=>$value,
						));
					}
				}
				foreach ($newValues as $value) {
					if (!in_array($value, $oldValues)) {
						$model = new $modelName();
						$model->competition_id = $this->id;
						$model->{$attribute . '_id'} = $value;
						$model->save();
					}
				}
			}
		}
		//处理地址
		$oldLocations = $this->location;
		foreach ($this->locations as $key=>$value) {
			if (isset($oldLocations[$key])) {
				$location = $oldLocations[$key];
			} else {
				$location = new CompetitionLocation();
				$location->competition_id = $this->id;
				$location->location_id = $key;
			}
			$location->attributes = $value;
			foreach (['location_id', 'country_id', 'province_id', 'city_id', 'delegate_id', 'organizer_id', 'status', 'competitor_limit'] as $attribute) {
				$location->$attribute = intval($location->$attribute);
			}
			$location->longitude = floatval($location->longitude);
			$location->latitude = floatval($location->latitude);
			$location->save(false);
		}
		//处理赛事说明
		$oldExplanations = array_values(CHtml::listData($this->explanation, 'id', 'label'));
		$newExplanations = array_values((array)$this->explanations);
		sort($oldExplanations);
		sort($newExplanations);
		if ($oldExplanations != $newExplanations) {
			foreach ($oldExplanations as $value) {
				if (!in_array($value, $newExplanations) && (!$this->isAccepted() || $isAdmin)) {
					CompetitionExplanation::model()->deleteAllByAttributes(array(
						'competition_id'=>$this->id,
						'label'=>$value,
					));
				}
			}
			foreach ($newExplanations as $value) {
				if (!in_array($value, $oldExplanations)) {
					$model = new CompetitionExplanation();
					$model->competition_id = $this->id;
					$model->label = $value;
					$model->save();
				}
			}
		}
		if ($this->isOld()) {
			$this->old->save(false);
		}
	}

	public function updateEvents($events) {
		CompetitionEvent::model()->deleteAllByAttributes(['competition_id'=>$this->id]);
		foreach ($events as $event=>$attributes) {
			if ($attributes['round'] > 0) {
				$competitionEvent = new CompetitionEvent();
				$competitionEvent->competition_id = $this->id;
				$competitionEvent->event = "$event";
				foreach ($attributes as $key=>$value) {
					$competitionEvent->{$key} = intval($value);
				}
				$competitionEvent->save();
			}
		}
		return true;
	}

	public function updateSchedules() {
		//处理赛程
		$schedules = $this->schedules;
		if (!$this->checkSchedules()) {
			$this->formatSchedule();
			return false;
		}
		if (!empty($schedules['start_time'])) {
			Schedule::model()->deleteAllByAttributes(array(
				'competition_id'=>$this->id,
			));
			foreach ($schedules['start_time'] as $key=>$startTime) {
				if (empty($startTime) || !isset($schedules['end_time'][$key]) || empty($schedules['end_time'][$key])) {
					continue;
				}
				$model = new Schedule();
				$model->competition_id = $this->id;
				$model->start_time = strtotime($startTime);
				$model->end_time = strtotime($schedules['end_time'][$key]);
				$model->day = isset($schedules['day'][$key]) ? $schedules['day'][$key] : 1;
				$model->stage = isset($schedules['stage'][$key]) ? $schedules['stage'][$key] : 'main';
				$model->event = isset($schedules['event'][$key]) ? $schedules['event'][$key] : '';
				$model->group = isset($schedules['group'][$key]) ? $schedules['group'][$key] : '';
				$model->round = isset($schedules['round'][$key]) ? $schedules['round'][$key] : '';
				$model->format = isset($schedules['format'][$key]) ? $schedules['format'][$key] : '';
				$model->number = isset($schedules['number'][$key]) ? intval($schedules['number'][$key]) : 0;
				$model->cut_off = isset($schedules['cut_off'][$key]) ? intval($schedules['cut_off'][$key]) : 0;
				$model->time_limit = isset($schedules['time_limit'][$key]) ? intval($schedules['time_limit'][$key]) : 0;
				$model->cumulative = isset($schedules['cumulative'][$key]) ? intval($schedules['cumulative'][$key]) : 0;
				$model->save(false);
			}
		}
		return true;
	}

	public function checkRegistrationStart() {
		if ($this->reg_start >= $this->reg_end) {
			$this->addError('reg_start', '报名起始时间必须早于报名截止时间');
		}
	}

	public function checkRegistrationEnd() {
		if ($this->reg_end >= $this->date) {
			$this->addError('reg_end', '报名截止时间必须早于比赛开始至少一天');
		}
	}

	public function checkCancellationEnd() {
		//之前的比赛ID到669
		if ($this->person_num > 0 && (
				$this->cancellation_end_time > 0 || $this->isWCACompetition() && $this->isAccepted() && $this->id > 669
			)) {
			if ($this->cancellation_end_time > $this->reg_end - 86400 && !$this->multi_countries) {
				$this->addError('cancellation_end_time', '补报截止时间必须早于报名截止时间至少一天');
			}
			if ($this->cancellation_end_time < $this->reg_start + 86400 * 7) {
				$this->addError('cancellation_end_time', '补报截止时间必须晚于报名开始时间至少一周');
			}
		}
		if (!$this->multi_countries && ($this->cancellation_end_time == 0 xor $this->reg_reopen_time == 0)) {
			$this->addError('cancellation_end_time', '请同时设置补报截止时间和报名重开时间');
			$this->addError('reg_reopen_time', '请同时设置补报截止时间和报名重开时间');
		}
	}

	public function checkRegistrationReopen() {
		//之前的比赛ID到669
		if ($this->person_num > 0 && (
				$this->reg_reopen_time > 0 || $this->isWCACompetition() && $this->isAccepted() && $this->id > 669
			)) {
			if ($this->reg_reopen_time > $this->reg_end - 43200) {
				$this->addError('reg_reopen_time', '报名重开时间必须早于比赛开始至少半天');
			}
			if ($this->reg_reopen_time < $this->cancellation_end_time + 43200) {
				$this->addError('reg_reopen_time', '报名重开时间必须晚于退赛截止时间至少半天');
			}
		}
	}

	public function checkQualifyingEndTime() {
		if ($this->has_qualifying_time) {
			if ($this->qualifying_end_time > $this->date - 3 * 86400) {
				$this->addError('qualifying_end_time', '资格线截止时间必须早于比赛开始前至少三天');
			}
		}
	}

	public function checkSecondStageDate() {
		if (($this->second_stage_date >= $this->reg_end && $this->reg_end > 0)
			|| ($this->second_stage_date <= $this->reg_start && $this->second_stage_date > 0)
		) {
			$this->addError('second_stage_date', '第二阶段时间必须介于报名开始和报名结束之间');
		}
		if ($this->second_stage_date >= $this->third_stage_date && $this->third_stage_date > 0) {
			$this->addError('second_stage_date', '第二阶段时间必须介于报名开始和第三阶段之间');
		}
	}

	public function checkSecondStageRatio() {
		$this->second_stage_ratio = floatval($this->second_stage_ratio);
		if ($this->second_stage_date > 0 && $this->second_stage_ratio <= 1) {
			$this->addError('second_stage_ratio', '倍率必须大于1');
		}
	}

	public function checkThirdStageDate() {
		if (($this->third_stage_date >= $this->reg_end && $this->reg_end > 0)
			|| ($this->third_stage_date <= $this->reg_start && $this->third_stage_date > 0)
		) {
			$this->addError('third_stage_date', '第三阶段时间必须介于报名开始和报名结束之间');
		}
		if ($this->second_stage_date >= $this->third_stage_date && $this->third_stage_date > 0) {
			$this->addError('third_stage_date', '第三阶段时间必须介于第二阶段和报名结束之间');
		}
	}

	public function checkThirdStageRatio() {
		$this->third_stage_ratio = floatval($this->third_stage_ratio);
		if ($this->third_stage_date > 0 && $this->third_stage_ratio <= 1) {
			$this->addError('third_stage_ratio', '倍率必须大于1');
		}
	}

	public function checkName() {
		// don't check anything for special competitions
		if ($this->special) {
			return;
		}
		if (!preg_match('{^[\'.\-a-z0-9& ]+$}i', $this->name, $matches)) {
			$this->addError('name', '英文名只能由字母、数字、空格、短杠-、点.和单引号\'组成');
		}
		if (!preg_match('{ \d{4}$}i', $this->name, $matches)) {
			$this->addError('name', '英文名必须以年份结尾');
		}
		if (!preg_match('{^[a-z0-9 \x{4e00}-\x{9fc0}“”]+$}iu', $this->name_zh)) {
			$this->addError('name_zh', '中文名只能由中文、英文、数字、空格和双引号“”组成');
		}
		if (!preg_match('{^\d{4}}i', $this->name_zh, $matches)) {
			$this->addError('name_zh', '中文名必须以年份开头');
		}
	}

	public function checkType() {
		if ($this->type == self::TYPE_WCA) {
			if (empty($this->delegates)) {
				$this->addError('delegates', 'WCA比赛需至少选择一名代表！');
			}
			$this->fill_passport = self::YES;
		}
	}

	public function checkWcaCompetitionId() {
		if ($this->type == self::TYPE_OTHER && $this->wca_competition_id != '') {
			$this->addError('wca_competition_id', '非WCA比赛请勿填写WCA比赛ID');
		}
		if ($this->type == self::TYPE_WCA && $this->wca_competition_id != '') {
			$wcaCompetition = Competitions::model()->findByPk($this->wca_competition_id);
			if ($wcaCompetition == null) {
				$this->addError('wca_competition_id', '请填写WCA官网已公示比赛的ID');
			}
		}
	}

	public function checkLocations() {
		$locations = $this->locations;
		$special = $this->special;
		if (isset($locations[0]['province_id'])) {
			return;
		}
		if (!isset($locations['province_id'])) {
			$locations['province_id'] = array();
		}
		$temp = array();
		$index = 0;
		$hasFeeInfo = $this->multi_countries || $this->complex_multi_location;
		foreach ($locations['province_id'] as $key=>$provinceId) {
			if (empty($provinceId) && empty($locations['city_id'][$key])
				&& empty($locations['venue'][$key])  && empty($locations['venue_zh'][$key])
			) {
				continue;
			}
			if ($this->multi_countries) {
				if (empty($locations['country_id'][$key])) {
					continue;
				}
				if ($locations['country_id'][$key] != 1) {
					$provinceId = 0;
					$locations['city_id'][$key] = 0;
					if (empty($locations['fee'][$key]) && !$special) {
						$this->addError('locations.fee.' . $index, '非大陆地区请填写费用！');
					}
					if ($locations['country_id'][$key] > 4) {
						if (empty($locations['city_name'][$key]) && !$special) {
							$this->addError('locations.city_name.' . $index, '非大陆及港澳台地区请填写英文城市！');
						}
						if (empty($locations['city_name_zh'][$key]) && !$special) {
							$this->addError('locations.city_name_zh.' . $index, '非大陆及港澳台地区请填写中文城市！');
						}
					}
				} elseif (!empty($locations['fee'][$key]) && !ctype_digit($locations['fee'][$key]) && !$special) {
					$this->addError('locations.fee.' . $index, '大陆地区请填写整数费用！');
				}
				if (empty($locations['delegate_id'][$key])) {
					if (empty($locations['delegate_name'][$key]) && !$special) {
						$this->addError('locations.delegate_name.' . $index, '必须选择一个代表或者手动填写！');
					}
					// if (empty($locations['delegate_email'][$key]) && !$special) {
					// 	$this->addError('locations.delegate_email.' . $index, '必须选择一个代表或者手动填写！');
					// }
				}
			}
			if (!$this->multi_countries || $locations['country_id'][$key] == 1) {
				if (empty($provinceId) && !$special) {
					$this->addError('locations.province_id.' . $index, '省份不能为空');
				}
				if (empty($locations['city_id'][$key]) && !$special) {
					$this->addError('locations.city_id.' . $index, '城市不能为空');
				}
			}
			$locations['venue'][$key] = trim($locations['venue'][$key]);
			if ($locations['venue'][$key] == '' && !$special) {
				$this->addError('locations.venue.' . $index, '英文地址不能为空');
			}
			// check capitalization, comma and space
			if (strpos($locations['venue'][$key], '，') !== false && !$special) {
				$this->addError('locations.venue.' . $index, '英文地址请使用半角逗号');
			}
			if (!$this->multi_countries) {
				$venues = explode(',', $locations['venue'][$key]);
				foreach ($venues as $k=>$venue) {
					if (!preg_match('{^[0-9A-Z]}', trim($venue)) && !$special) {
						$this->addError('locations.venue.' . $index, '首字母请大写');
						break;
					}
					if ($k > 0 && $venue[0] !== ' ' && !$special) {
						$this->addError('locations.venue.' . $index, '逗号之后请添加空格');
						break;
					}
				}
			}

			$locations['venue_zh'][$key] = trim($locations['venue_zh'][$key]);
			if ($locations['venue_zh'][$key] == '' && !$special) {
				$this->addError('locations.venue_zh.' . $index, '中文地址不能为空');
			}
			if ($locations['longitude'][$key] && !preg_match('{^-?\d+(\.\d+)?$}', $locations['longitude'][$key]) && !$special) {
				$this->addError('locations.longitude.' . $index, '经度无效！');
			}
			if ($locations['latitude'][$key] && !preg_match('{^-?\d+(\.\d+)?$}', $locations['latitude'][$key]) && !$special) {
				$this->addError('locations.latitude.' . $index, '纬度无效！');
			}
			$temp[] = array(
				'country_id'=>$this->multi_countries ? $locations['country_id'][$key] : 0,
				'province_id'=>$provinceId,
				'city_id'=>$locations['city_id'][$key],
				'city_name'=>$this->multi_countries ? $locations['city_name'][$key] : '',
				'city_name_zh'=>$this->multi_countries ? $locations['city_name_zh'][$key] : '',
				'venue'=>$locations['venue'][$key],
				'venue_zh'=>$locations['venue_zh'][$key],
				'longitude'=>$locations['longitude'][$key],
				'latitude'=>$locations['latitude'][$key],
				'delegate_id'=>$this->multi_countries ? $locations['delegate_id'][$key] : 0,
				'delegate_name'=>$this->multi_countries ? $locations['delegate_name'][$key] : '',
				'delegate_email'=>$this->multi_countries ? $locations['delegate_email'][$key] : '',
				'fee'=>$hasFeeInfo ? $locations['fee'][$key] : '',
				'payment_method'=>$this->multi_countries ? $locations['payment_method'][$key] : '',
				'status'=>$this->multi_countries ? intval($locations['status'][$key]) : 1,
				'organizer_id'=>$this->complex_multi_location ? intval($locations['organizer_id'][$key]) : 0,
				'competitor_limit'=>$hasFeeInfo ? intval($locations['competitor_limit'][$key]) : 0,
			);
			$index++;
		}
		if ($this->hasErrors() || count($temp) == 0) {
			$this->addError('locations', '地址填写有误，请检查各地址填写！');
		}
		$this->locations = $temp;
	}

	public function checkOrganizerTeamMembers() {
		$organizerTeamMembers = array_filter((array)$this->organizerTeamMembers);
		$personNum = $this->person_num;
		$max = min(ceil($personNum / 100), 5);
		if (count($organizerTeamMembers) > $max) {
			$this->addError('organizerTeamMembers', "主办团队人数不能超过${max}人！");
		}
	}

	public function checkSchedules() {
		$schedules = $this->schedules;
		if (!empty($schedules['start_time'])) {
			$onlyScheculeEvents = Events::getOnlyScheduleEvents();
			$combinedRoundTypes = array('c', 'd', 'e', 'g');
			foreach ($schedules['start_time'] as $key=>$startTime) {
				$errorKey = 'schedules.' . $key;
				if (empty($startTime) || !isset($schedules['end_time'][$key]) || empty($schedules['end_time'][$key])) {
					continue;
				}
				$event = isset($schedules['event'][$key]) ? $schedules['event'][$key] : '';
				$group = isset($schedules['group'][$key]) ? $schedules['group'][$key] : '';
				$round = isset($schedules['round'][$key]) ? $schedules['round'][$key] : '';
				$format = isset($schedules['format'][$key]) ? $schedules['format'][$key] : '';
				$number = isset($schedules['number'][$key]) ? intval($schedules['number'][$key]) : 0;
				$cutOff = isset($schedules['cut_off'][$key]) ? intval($schedules['cut_off'][$key]) : 0;
				$timeLimit = isset($schedules['time_limit'][$key]) ? intval($schedules['time_limit'][$key]) : 0;
				$cumulative = isset($schedules['cumulative'][$key]) ? intval($schedules['cumulative'][$key]) : 0;
				if (empty($event)) {
					$this->addError($errorKey, '必须选择项目！');
					return false;
				}
				if (isset($onlyScheculeEvents[$event])) {
					if (!empty($group) || !empty($round) || !empty($format) || !empty($number) || !empty($cutOff) || !empty($timeLimit)) {
						$this->addError($errorKey, '非比赛项目不能设置分组、轮次、赛制、人数、及格线、还原时限等！');
						return false;
					}
				} else {
					if ($round == '') {
						$this->addError($errorKey, '请选择轮次！');
						return false;
					}
					if ($format == '') {
						$this->addError($errorKey, '请选择赛制！');
						return false;
					}
					if (in_array($round, $combinedRoundTypes)) {
						if ($format != '2/a' && $format != '1/m') {
							$this->addError($errorKey, '请正确选择组合制轮次的赛制！');
							return false;
						}
						if (empty($cutOff)) {
							$this->addError($errorKey, '组合制轮次请设置及格线！');
							return false;
						}
					} else {
						if ($format == '2/a' || $format == '1/m') {
							$this->addError($errorKey, '请正确选择非组合制轮次的赛制！');
							return false;
						}
						if (!empty($cutOff)) {
							$this->addError($errorKey, '非组合制轮次请勿设置及格线！');
							return false;
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'competition';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		$criteria = new CDbCriteria();
		$criteria->addInCondition('status', [
			self::STATUS_HIDE,
			self::STATUS_SHOW,
		]);
		$rules = [
			['name, name_zh, date, reg_end', 'required'],
			['entry_fee, second_stage_all, online_pay, person_num, competitor_limit_type, auto_accept, fill_passport, local_type, live, status, newcomer', 'numerical', 'integerOnly'=>true],
			['fill_passport, show_regulations, show_qrcode, t_shirt, staff, allow_change_event,
				podiums_children, podiums_females, podiums_new_comers, podiums_greater_china,
				podiums_u3, podiums_u4, podiums_u5, podiums_u6, podiums_u7, podiums_u8,
				podiums_u9, podiums_u10, podiums_u11, podiums_u12, podiums_u13, podiums_u14,
				podiums_u15, podiums_u16, podiums_u17, podiums_u18,
				podiums_o25, podiums_o30, podiums_o35, podiums_o45,
				entry_ticket, guest_limit, attend_ceremory, name_card_fee', 'numerical', 'integerOnly'=>true],
			['podiums_o', 'safe'],
			['podiums_num', 'numerical', 'integerOnly'=>true, 'max'=>8, 'min'=>3],
			['type', 'length', 'max'=>10],
			['wca_competition_id', 'length', 'max'=>32],
			['name_zh', 'length', 'max'=>50],
			['name', 'length', 'max'=>128],
			['locations', 'checkLocations', 'skipOnError'=>true],
			['name', 'checkName', 'skipOnError'=>true],
			['name', 'unique', 'className'=>'Competition', 'attributeName'=>'name', 'skipOnError'=>true, 'on'=>'accept', 'criteria'=>$criteria],
			['name_zh', 'unique', 'className'=>'Competition', 'attributeName'=>'name_zh', 'skipOnError'=>true, 'on'=>'accept', 'criteria'=>$criteria],
			['type', 'checkType', 'skipOnError'=>true],
			['wca_competition_id', 'checkWcaCompetitionId'],
			['reg_start', 'checkRegistrationStart', 'skipOnError'=>true],
			['reg_end', 'checkRegistrationEnd', 'skipOnError'=>true],
			['cancellation_end_time', 'checkCancellationEnd', 'skipOnError'=>true, 'except'=>'accept'],
			['reg_reopen_time', 'checkRegistrationReopen', 'skipOnError'=>true, 'except'=>'accept'],
			['qualifying_end_time', 'checkQualifyingEndTime', 'skipOnError'=>true],
			['second_stage_date', 'checkSecondStageDate', 'skipOnError'=>true],
			['second_stage_ratio', 'checkSecondStageRatio', 'skipOnError'=>true],
			['third_stage_date', 'checkThirdStageDate', 'skipOnError'=>true],
			['third_stage_ratio', 'checkThirdStageRatio', 'skipOnError'=>true],
			['organizerTeamMembers', 'checkOrganizerTeamMembers', 'skipOnError'=>true],
			['refund_type, end_date, oldDelegate, oldDelegateZh, oldOrganizer, oldOrganizerZh, organizers, delegates, locations, schedules, explanations,
				regulations, regulations_zh, information, information_zh, travel, travel_zh, events, podiumsEvents, scoreTakers', 'safe'],
			['province, year, id, type, wca_competition_id, name, name_zh, date, end_date, reg_end, events, entry_fee, information, information_zh, travel, travel_zh, person_num, auto_accept, status', 'safe', 'on'=>'search'],
			['live_stream_url', 'url'],
		];
		if (!(Yii::app() instanceof CConsoleApplication) && Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR)) {
			$rules[] = ['tba', 'safe'];
		}
		if (!$this->isOld()) {
			$rules[] = ['organizers', 'required'];
		} else {
			$rules[] = ['oldOrganizer, oldOrganizerZh', 'required'];
		}
		return $rules;
	}

	/**
	 * @return array relational rules.
	 */
	public function relations() {
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return [
			'organizer'=>[self::HAS_MANY, 'CompetitionOrganizer', 'competition_id'],
			'organizerTeamMember'=>[self::HAS_MANY, 'CompetitionOrganizerTeamMember', 'competition_id'],
			'scoreTaker'=>[self::HAS_MANY, 'ScoreTaker', 'competition_id'],
			'delegate'=>[self::HAS_MANY, 'CompetitionDelegate', 'competition_id'],
			'location'=>[self::HAS_MANY, 'CompetitionLocation', 'competition_id', 'order'=>'location.location_id'],
			'old'=>[self::BELONGS_TO, 'OldCompetition', 'old_competition_id'],
			'schedule'=>[self::HAS_MANY, 'Schedule', 'competition_id', 'order'=>'schedule.day,schedule.stage,schedule.start_time,schedule.end_time'],
			'registeredCompetitors'=>[self::STAT, 'Registration', 'competition_id', 'condition'=>'status=1'],
			'liveResults'=>[self::HAS_MANY, 'LiveResult', 'competition_id'],
			'allEvents'=>[self::HAS_MANY, 'CompetitionEvent', 'competition_id', 'order'=>'allEvents.id'],
			'application'=>[self::HAS_ONE, 'CompetitionApplication', 'competition_id'],
			'tickets'=>[self::HAS_MANY, 'Ticket', 'type_id', 'on'=>'type=' . Ticket::TYPE_COMPETITION],
			'series'=>[self::HAS_ONE, 'CompetitionSeries', 'competition_id'],
			'explanation'=>[self::HAS_MANY, 'CompetitionExplanation', 'competition_id'],
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Competition', 'ID'),
			'type' => Yii::t('Competition', 'Type'),
			'wca_competition_id' => Yii::t('Competition', 'Wca Competition ID'),
			'explanation' => Yii::t('Competition', 'Explanation'),
			'name' => Yii::t('Competition', 'Competition Name'),
			'name_zh' => Yii::t('Competition', 'Competition Name'),
			'date' => Yii::t('Competition', 'Date'),
			'end_date' => Yii::t('Competition', 'End Date'),
			'reg_start' => Yii::t('Competition', 'Registration Starting Time'),
			'reg_end' => Yii::t('Competition', 'Registration Ending Time'),
			'qualifying_end_time' => Yii::t('Competition', 'Qualifying Ending Time'),
			'province_id' => Yii::t('Competition', 'Province'),
			'city_id' => Yii::t('Competition', 'City'),
			'venue' => Yii::t('Competition', 'Venue'),
			'venue_zh' => Yii::t('Competition', 'Venue'),
			'events' => Yii::t('Competition', 'Events'),
			'entry_fee' => Yii::t('Competition', 'Entry Fee'),
			'online_pay' => Yii::t('Competition', 'Online Pay'),
			'second_stage_date' => Yii::t('Competition', 'Second Stage Date'),
			'second_stage_ratio' => Yii::t('Competition', 'Second Stage Ratio'),
			'second_stage_all' => Yii::t('Competition', 'Second Stage All'),
			'third_stage_date' => Yii::t('Competition', 'Third Stage Date'),
			'third_stage_ratio' => Yii::t('Competition', 'Third Stage Ratio'),
			'regulations' => Yii::t('Competition', 'Regulations'),
			'regulations_zh' => Yii::t('Competition', 'Regulations'),
			'information' => Yii::t('Competition', 'Information'),
			'information_zh' => Yii::t('Competition', 'Information'),
			'travel' => Yii::t('Competition', 'Travel'),
			'travel_zh' => Yii::t('Competition', 'Travel'),
			'person_num' => Yii::t('Competition', 'Person Num'),
			'status' => Yii::t('Competition', 'Status'),
			'organizers' => Yii::t('Competition', 'Organizers'),
			'delegates' => Yii::t('Competition', 'Delegates'),
			'year' => Yii::t('common', 'Year'),
			'event' => Yii::t('common', 'Event'),
			'province' => Yii::t('common', 'Province'),
		);
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
	public function search($admin = false) {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;
		$criteria->with = ['location', 'location.province', 'location.city'];
		$criteria->compare('t.id', $this->id, true);
		$criteria->compare('t.type', $this->type, true);
		$criteria->compare('t.wca_competition_id', $this->wca_competition_id, true);
		$criteria->compare('t.name', $this->name, true);
		$criteria->compare('t.name_zh', $this->name_zh, true);
		$criteria->compare('t.date', $this->date, true);
		$criteria->compare('t.end_date', $this->end_date, true);
		$criteria->compare('t.reg_end', $this->reg_end, true);
		$criteria->compare('t.entry_fee', $this->entry_fee);
		$criteria->compare('t.online_pay', $this->online_pay);
		$criteria->compare('t.information', $this->information, true);
		$criteria->compare('t.information_zh', $this->information_zh, true);
		$criteria->compare('t.travel', $this->travel, true);
		$criteria->compare('t.travel_zh', $this->travel_zh, true);
		$criteria->compare('t.person_num', $this->person_num);
		$criteria->compare('t.auto_accept', $this->auto_accept);
		if ($this->status !== '' && $this->status !== null) {
			$criteria->compare('t.status', $this->status);
		} else {
			$criteria->compare('t.status', array_keys(self::getAllStatus($this->scenario)));
		}

		if (!$admin) {
			if ($this->year === 'current') {
				$criteria->compare('t.date', '>=' . (time() - 86400 * 184));
			} elseif (in_array($this->year, self::getYears())) {
				$criteria->compare('t.date', '>=' . strtotime($this->year . '-01-01'));
				$criteria->compare('t.date', '<=' . strtotime($this->year . '-12-31'));
			}
			if ($this->province > 0) {
				$criteria->with = ['location'=>['together'=>true]];
				$criteria->compare('location.province_id', $this->province);
			}
			if ($this->event !== '') {
				$criteria->with['allEvents'] = ['together'=>true];
				$criteria->compare('allEvents.event', $this->event);
			}
		}

		if ($admin) {
			$user = Yii::app()->controller->user;
			switch (true) {
				case $user->isAdministrator():
					break;
				case Yii::app()->user->checkPermission('caqa_member'):
					break;
				case $user->isDelegate():
					$criteria->with = array(
						'organizer'=>array(
							'together'=>true,
						),
						'delegate'=>array(
							'together'=>true,
						),
						'location', 'location.province', 'location.city'
					);
					$criteria->addCondition('organizer.organizer_id=:user_id OR delegate.delegate_id=:user_id');
					$criteria->params[':user_id'] = $user->id;
					break;
				case $user->isOrganizer():
				default:
					$criteria->with = array(
						'organizer'=>array(
							'together'=>true,
						),
						'location', 'location.province', 'location.city'
					);
					$criteria->compare('organizer.organizer_id', Yii::app()->user->id);
					break;
			}
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'date DESC, end_date DESC, type DESC',
			),
			'pagination'=>array(
				'pageVar'=>'page',
				'pageSize'=>100,
			),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Competition the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
}
