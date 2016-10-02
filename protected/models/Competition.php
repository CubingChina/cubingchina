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
 * @property integer $check_person
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

	const NOT_CHECK_PERSON = 0;
	const CHECK_PERSON = 1;

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

	private $_organizers;
	private $_delegates;
	private $_locations;
	private $_schedules;
	private $_description;
	private $_timezones;

	public $year;
	public $province;
	public $event;

	public static function formatTime($second) {
		$second = intval($second);
		if ($second <= 0) {
			return '';
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

	public static function getUnpublicCount() {
		return self::model()->with(array(
			'organizer'=>array(
				'together'=>true,
				'condition'=>'organizer.organizer_id=' . Yii::app()->user->id,
			),
		))->countByAttributes(array(
			'status'=>self::STATUS_HIDE,
		));
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
		$with = array();
		if (Yii::app()->controller->user->isOrganizer()) {
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

	public static function getAllStatus() {
		return array(
			self::STATUS_HIDE=>'隐藏',
			self::STATUS_SHOW=>'公示',
			// self::STATUS_DELETE=>'删除',
		);
	}

	public static function getCheckPersons() {
		return array(
			self::CHECK_PERSON=>'否',
			self::NOT_CHECK_PERSON=>'是',
		);
	}

	public function isOnlinePay() {
		return $this->online_pay == self::ONLINE_PAY;
	}

	public function isPublic() {
		return $this->status == self::STATUS_SHOW;
	}

	public function isRegistrationStarted() {
		return time() > $this->reg_start;
	}

	public function isRegistrationEnded() {
		return time() > $this->reg_end;
	}

	public function isRegistrationFull() {
		return $this->person_num > 0 && Registration::model()->with(array(
			'user'=>array(
				'condition'=>'user.status=' . User::STATUS_NORMAL,
			),
		))->countByAttributes(array(
			'competition_id'=>$this->id,
			'status'=>Registration::STATUS_ACCEPTED,
		)) >= $this->person_num;
	}

	public function canRegister() {
		return !$this->isRegistrationEnded() && !$this->isRegistrationFull();
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
		$this->formatEvents();
		$events = $this->events;
		foreach ($this->schedule as $schedule) {
			if (!isset($events[$schedule->event])) {
				continue;
			}
			$events[$schedule->event]['schedule'][$schedule->round] = 1;
		}
		foreach ($events as $event) {
			if ($event['round'] > 0 && (
				(!isset($event['schedule']['c']) && !isset($event['schedule']['f'])) ||
				$event['round'] > count($event['schedule'])
			)) {
				return false;
			}
		}
		return true;
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

	public function getSortedLocations() {
		$locations = $this->location;
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
		$status = self::getAllStatus();
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

	public function getWcaRegulationUrl() {
		switch (Yii::app()->language) {
			case 'zh_cn':
				return 'https://www.worldcubeassociation.org/regulations/translations/chinese/';
			case 'zh_tw':
				return 'https://www.worldcubeassociation.org/regulations/translations/chinese-traditional/';
			default:
				return 'https://www.worldcubeassociation.org/regulations/';
		}
	}

	public function getWcaUrl() {
		return Competitions::getWcaUrl($this->wca_competition_id);
	}

	public function getHasResults() {
		return $this->type == self::TYPE_WCA && Results::model()->countByAttributes(array(
			'competitionId'=>$this->wca_competition_id,
		)) > 0;
	}

	public function getUrl($type = 'detail', $params = array()) {
		$controller = $type === 'live' || $type === 'statistics' ? 'live' : 'competition';
		$url = array(
			"/$controller/$type",
			'name'=>$this->getUrlName(),
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

	public function getEventFee($event, $stage = null) {
		$now = time();
		$isBasic = !isset($this->events[$event]);
		if ($stage === null) {
			if ($now < $this->second_stage_date) {
				$stage = self::STAGE_FIRST;
			} elseif ($now < $this->third_stage_date) {
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
		}
		$basicFee = $isBasic ? $this->entry_fee : $this->events[$event]['fee'];
		switch ($stage) {
			case self::STAGE_FIRST:
				return $basicFee;
			case self::STAGE_SECOND:
			case self::STAGE_THIRD:
				$ratio = $this->{$stage . '_stage_ratio'};
				if ($isBasic) {
					return ceil($basicFee * $ratio);
				}
				if (isset($this->events[$event]['fee_' . $stage]) && $this->events[$event]['fee_' . $stage] > 0) {
					return $this->events[$event]['fee_' . $stage];
				}
				return $this->second_stage_all ? ceil($basicFee * $ratio) : $basicFee;
		}
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

	public function getUserSchedules($user) {
		$listableSchedules = array();
		$schedules = HeatScheduleUser::model()->findAllByAttributes([
			'user_id'=>$user->id,
			'competition_id'=>$this->id,
		]);
		$schedules = CHtml::listData($schedules, 'id', 'schedule');
		usort($schedules, array($this, 'sortSchedules'));
		$hasGroup = false;
		$hasCutOff = false;
		$hasTimeLimit = false;
		$hasNumber = false;
		$cumulative = Yii::t('common', 'Cumulative ');
		$specialEvents = array(
			'333fm'=>array(),
			'333mbf'=>array(),
		);
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
			$event = Yii::t('event', Events::getFullEventName($schedule->event));
			if (isset($specialEvents[$schedule->event][$schedule->round]) && count($specialEvents[$schedule->event][$schedule->round]) > 1) {
				$times = array_search($key, $specialEvents[$schedule->event][$schedule->round]);
				switch ($times + 1) {
					case 1:
						$event .= Yii::t('common', ' (1st attempt)');
						break;
					case 2:
						$event .= Yii::t('common', ' (2nd attempt)');
						break;
					case 3:
						$event .= Yii::t('common', ' (3rd attempt)');
						break;
					default:
						$event .= Yii::t('common', ' ({times}th attempt)', array(
							'{times}'=>$times,
						));
						break;
				}
			}
			$temp = array(
				'Start Time'=>date('H:i', $schedule->start_time),
				'End Time'=>date('H:i', $schedule->end_time),
				'Event'=>$event,
				'Group'=>$schedule->group,
				'Round'=>Yii::t('Rounds', Rounds::getFullRoundName($schedule->round)),
				'Format'=>Yii::t('common', Formats::getFullFormatName($schedule->format)),
				'Cut Off'=>self::formatTime($schedule->cut_off),
				'Time Limit'=>self::formatTime($schedule->time_limit),
				'Competitors'=>$schedule->number,
				'id'=>$schedule->id,
				'event'=>$schedule->event,
				'round'=>$schedule->round,
				'schedule'=>$schedule,
			);
			if ($schedule->cumulative) {
				$temp['Time Limit'] = $cumulative . $temp['Time Limit'];
			}
			if ($hasGroup === false) {
				unset($temp['Group']);
			}
			if ($hasCutOff === false) {
				unset($temp['Cut Off']);
			}
			if ($hasTimeLimit === false) {
				unset($temp['Time Limit']);
			}
			if ($hasNumber === false) {
				unset($temp['Competitors']);
			}
			$listableSchedules[$schedule->day][$schedule->stage][] = $temp;
		}
		return $listableSchedules;
	}

	public function getListableSchedules() {
		$listableSchedules = array();
		$schedules = $this->schedule;
		usort($schedules, array($this, 'sortSchedules'));
		$hasGroup = false;
		$hasCutOff = false;
		$hasTimeLimit = false;
		$hasNumber = false;
		$cumulative = Yii::t('common', 'Cumulative ');
		$specialEvents = array(
			'333fm'=>array(),
			'333mbf'=>array(),
		);
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
			$event = Yii::t('event', Events::getFullEventName($schedule->event));
			if (isset($specialEvents[$schedule->event][$schedule->round]) && count($specialEvents[$schedule->event][$schedule->round]) > 1) {
				$times = array_search($key, $specialEvents[$schedule->event][$schedule->round]);
				switch ($times + 1) {
					case 1:
						$event .= Yii::t('common', ' (1st attempt)');
						break;
					case 2:
						$event .= Yii::t('common', ' (2nd attempt)');
						break;
					case 3:
						$event .= Yii::t('common', ' (3rd attempt)');
						break;
					default:
						$event .= Yii::t('common', ' ({times}th attempt)', array(
							'{times}'=>$times,
						));
						break;
				}
			}
			$temp = array(
				'Start Time'=>date('H:i', $schedule->start_time),
				'End Time'=>date('H:i', $schedule->end_time),
				'Event'=>$event,
				'Group'=>$schedule->group,
				'Round'=>Yii::t('Rounds', Rounds::getFullRoundName($schedule->round)),
				'Format'=>Yii::t('common', Formats::getFullFormatName($schedule->format)),
				'Cut Off'=>self::formatTime($schedule->cut_off),
				'Time Limit'=>self::formatTime($schedule->time_limit),
				'Competitors'=>$schedule->number,
				'id'=>$schedule->id,
				'event'=>$schedule->event,
				'round'=>$schedule->round,
				'schedule'=>$schedule,
			);
			if ($schedule->cumulative) {
				$temp['Time Limit'] = $cumulative . $temp['Time Limit'];
			}
			if ($hasGroup === false) {
				unset($temp['Group']);
			}
			if ($hasCutOff === false) {
				unset($temp['Cut Off']);
			}
			if ($hasTimeLimit === false) {
				unset($temp['Time Limit']);
			}
			if ($hasNumber === false) {
				unset($temp['Competitors']);
			}
			$listableSchedules[$schedule->day][$schedule->stage][] = $temp;
		}
		return $listableSchedules;
	}

	public function getScheduleColumns($schedules) {
		if (empty($schedules)) {
			return array();
		}
		$columns = array();
		foreach ($schedules[0] as $key=>$value) {
			if ($key == 'id' || $key == 'event' || $key == 'round' || !is_string($value)) {
				continue;
			}
			$width = $this->getScheduleColumnWidth($key);
			$column = array(
				'name'=>$key,
				'header'=>Yii::t('Schedule', $key),
				'headerHtmlOptions'=>array(
					'style'=>sprintf("width: %dpx;min-width: %dpx;vertical-align:bottom", $width, $width),
				),
			);
			if ($key == 'Event') {
				$column['type'] = 'raw';
				$column['value'] = 'CHtml::tag("span", array(
					"class"=>"event-icon event-icon-" . $data["event"],
				), $data["Event"])';
			}
			if ($this->multi_countries && ($key == 'Start Time' || $key == 'End Time')) {
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
			case 'Cut Off':
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
		$buttons = array();
		$buttons[] = CHtml::link('预览', $this->getUrl('detail'), array('class'=>'btn btn-xs btn-orange btn-square', 'target'=>'_blank'));
		$buttons[] = CHtml::link('编辑', array('/board/competition/edit', 'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		if (Yii::app()->user->checkRole(User::ROLE_DELEGATE)) {
			switch ($this->status) {
				case self::STATUS_HIDE:
					$buttons[] = CHtml::tag('button', array(
						'class'=>'btn btn-xs btn-green btn-square toggle',
						'data-id'=>$this->id,
						'data-url'=>CHtml::normalizeUrl(array('/board/competition/toggle')),
						'data-attribute'=>'status',
						'data-value'=>$this->status,
						'data-text'=>'["公示","隐藏"]',
						'data-name'=>$this->name_zh,
					), '公示');
					break;
				case self::STATUS_SHOW:
					$buttons[] = CHtml::tag('button', array(
						'class'=>'btn btn-xs btn-red btn-square toggle',
						'data-id'=>$this->id,
						'data-url'=>CHtml::normalizeUrl(array('/board/competition/toggle')),
						'data-attribute'=>'status',
						'data-value'=>$this->status,
						'data-text'=>'["公示","隐藏"]',
						'data-name'=>$this->name_zh,
					), '隐藏');
					break;
			}
		}
		if ($this->status == self::STATUS_SHOW) {
			$buttons[] = CHtml::link('报名管理', array('/board/registration/index', 'Registration'=>array('competition_id'=>$this->id)), array('class'=>'btn btn-xs btn-purple btn-square'));
			if (!$this->canRegister()) {
				$buttons[] = CHtml::tag('button', array(
					'class'=>'btn btn-xs btn-square toggle btn-' . ($this->live ? 'red' : 'green'),
					'data-id'=>$this->id,
					'data-url'=>CHtml::normalizeUrl(array('/board/competition/toggle')),
					'data-attribute'=>'live',
					'data-value'=>$this->live,
					'data-text'=>'["开启直播","关闭直播"]',
					'data-name'=>$this->name_zh,
				), $this->live ? '关闭直播' : '开启直播');
			}
		}
		return implode(' ', $buttons);
	}

	public function getOperationFeeButton() {
		if ($this->id < 382) {
			return '';
		}
		$fee = $this->operationFee * $this->days;
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

	public function handleEvents() {
		$temp = $this->events;
		foreach ($temp as $key=>$value) {
			if (!is_array($value) || !isset($value['round']) || $value['round'] == 0) {
				unset($temp[$key]);
				continue;
			}
		}
		$this->events = json_encode($temp);
	}

	public function formatEvents() {
		if (is_array($this->events)) {
			return;
		}
		$temp = json_decode($this->events, true);
		if ($temp === null) {
			$temp = array();
		}
		$events = Events::getAllEvents();
		foreach ($events as $key=>$value) {
			if (!isset($temp[$key])) {
				$temp[$key] = array(
					'round'=>0,
					'fee'=>0,
					'fee_second'=>'',
					'fee_third'=>'',
				);
				continue;
			}
			if (!isset($temp[$key]['round']) || $temp[$key]['round'] == '') {
				$temp[$key]['round'] = 0;
			}
			if (!isset($temp[$key]['fee']) || $temp[$key]['fee'] == '') {
				$temp[$key]['fee'] = 0;
			}
			if (!isset($temp[$key]['fee_second'])) {
				$temp[$key]['fee_second'] = '';
			}
			if (!isset($temp[$key]['fee_third'])) {
				$temp[$key]['fee_third'] = '';
			}
		}
		$this->events = $temp;
	}

	public function getRegistrationEvents() {
		$events = Events::getAllEvents();
		$registrationEvents = array();
		foreach ($this->events as $key=>$value) {
			if ($value['round'] > 0 && isset($events[$key])) {
				$registrationEvents[$key] = $events[$key];
			}
		}
		return $registrationEvents;
	}

	public function getEventsColumns($headerText = false) {
		$region = 'Yii::t("Region", $data->user->country->getAttributeValue("name"))';
		if (Yii::app()->language == 'zh_cn') {
			$region .= '.$data->user->getRegionName($data->user->province). (in_array($data->user->province_id, array(215, 525, 567, 642)) ? "" : $data->user->getRegionName($data->user->city))';
		}
		$columns = array(
			array(
				'headerHtmlOptions'=>array(
					'class'=>'battle-checkbox',
				),
				'header'=>Yii::t('common', 'Battle'),
				'value'=>'Persons::getBattleCheckBox($data->user->getCompetitionName(), $data->user->wcaid)',
				'type'=>'raw',
			),
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
				'value'=>'$data->user->getWcaLink()',
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
				'value'=>'$data->location->getFullAddress(false)',
			);
		}
		foreach ($this->events as $event=>$value) {
			if ($value['round'] > 0) {
				$columns[] = array(
					'name'=>(string)$event,
					'header'=>CHtml::tag('span', array(
						'class'=>'event-icon event-icon-white event-icon-' . $event,
						'title'=>Yii::t('event', Events::getFullEventName($event)),
					), $headerText ? $event : '&nbsp;'),
					'headerHtmlOptions'=>array(
						'class'=>'header-event',
					),
					'htmlOptions'=>Yii::app()->controller->sGet('sort') === "$event" ? array(
						'class'=>'hover',
					) : array(),
					'type'=>'raw',
					'value'=>"\$data->getEventsString('$event')",
				);
			}
		}
		return $columns;
	}

	public function handleDate() {
		foreach (array('date', 'end_date', 'reg_start', 'reg_end', 'second_stage_date', 'third_stage_date') as $attribute) {
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
		foreach (array('date', 'end_date') as $attribute) {
			if (!empty($this->$attribute)) {
				$this->$attribute = date('Y-m-d', $this->$attribute);
			} else {
				$this->$attribute = '';
			}
		}
		foreach (array('reg_start', 'reg_end', 'second_stage_date', 'third_stage_date') as $attribute) {
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
		$this->formatEvents();
		$schedules = array();
		$temp = $this->schedule;
		usort($temp, array($this, 'sortSchedules'));
		foreach ($temp as $schedule) {
			$schedules[$schedule->event][$schedule->round] = $schedule;
		}
		unset($temp);
		//events and rounds
		$rounds = array();
		foreach ($this->events as $event=>$value) {
			if ($value['round'] == 0) {
				continue;
			}
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
			foreach ($registration->events as $event) {
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
					'name'=>Yii::t('event', Events::getFullEventName($eventRound->event)),
					'rs'=>array(),
				);
			}
			$attributes = $eventRound->getBroadcastAttributes();
			$attributes['name'] = Yii::t('Rounds', Rounds::getFullRoundName($eventRound->round));
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

	protected function beforeValidate() {
		$this->handleDate();
		$this->handleEvents();
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
		return parent::beforeSave();
	}

	protected function afterSave() {
		if (Yii::app() instanceof CConsoleApplication) {
			return;
		}
		$isAdmin = Yii::app()->user->checkRole(User::ROLE_DELEGATE);
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
					if (!in_array($value, $newValues) && $isAdmin) {
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
		//处理赛程
		$schedules = $this->schedules;
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
			$location->save(false);
		}
		if ($this->isOld()) {
			$this->old->save(false);
		}
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
		if (!preg_match('{^[\'\-a-z0-9& ]+$}i', $this->name, $matches)) {
			$this->addError('name', '英文名只能由字母、数字、空格、短杠-和单引号\'组成');
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
		if ($this->type == self::TYPE_WCA && empty($this->delegates)) {
			$this->addError('delegates', 'WCA比赛需至少选择一名代表！');
		}
	}

	public function checkLocations() {
		$locations = $this->locations;
		if (isset($locations[0]['province_id'])) {
			return;
		}
		if (!isset($locations['province_id'])) {
			$locations['province_id'] = array();
		}
		$temp = array();
		$index = 0;
		$error = false;
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
					if (empty($locations['fee'][$key])) {
						$this->addError('locations.fee.' . $index, '非大陆地区请填写费用！');
						$error = true;
					}
					if ($locations['country_id'][$key] > 4) {
						if (empty($locations['city_name'][$key])) {
							$this->addError('locations.city_name.' . $index, '非大陆及港澳台地区请填写英文城市！');
							$error = true;
						}
						if (empty($locations['city_name_zh'][$key])) {
							$this->addError('locations.city_name_zh.' . $index, '非大陆及港澳台地区请填写中文城市！');
							$error = true;
						}
					}
				}
				if (empty($locations['delegate_id'][$key]) && empty($locations['delegate_text'][$key])) {
					$this->addError('locations.delegate_text.' . $index, '必须选择一个代表或者手动填写！');
					$error = true;
				}
			}
			if (!$this->multi_countries || $locations['country_id'][$key] == 1) {
				if (empty($provinceId)) {
					$this->addError('locations.province_id.' . $index, '省份不能为空');
					$error = true;
				}
				if (empty($locations['city_id'][$key])) {
					$this->addError('locations.city_id.' . $index, '城市不能为空');
					$error = true;
				}
			}
			if (trim($locations['venue'][$key]) == '') {
				$this->addError('locations.venue.' . $index, '英文地址不能为空');
				$error = true;
			}
			if (trim($locations['venue_zh'][$key]) == '') {
				$this->addError('locations.venue_zh.' . $index, '中文地址不能为空');
				$error = true;
			}
			$temp[] = array(
				'country_id'=>$this->multi_countries ? $locations['country_id'][$key] : 0,
				'province_id'=>$provinceId,
				'city_id'=>$locations['city_id'][$key],
				'city_name'=>$this->multi_countries ? $locations['city_name'][$key] : '',
				'city_name_zh'=>$this->multi_countries ? $locations['city_name_zh'][$key] : '',
				'venue'=>$locations['venue'][$key],
				'venue_zh'=>$locations['venue_zh'][$key],
				'delegate_id'=>$this->multi_countries ? $locations['delegate_id'][$key] : 0,
				'delegate_text'=>$this->multi_countries ? $locations['delegate_text'][$key] : '',
				'fee'=>$this->multi_countries ? $locations['fee'][$key] : '',
			);
			$index++;
		}
		if ($error || count($temp) == 0) {
			$this->addError('locations', '地址填写有误，请检查各地址填写！');
		}
		$this->locations = $temp;
	}

	public function checkSchedules() {
		$schedules = $this->schedules;
		if (!empty($schedules['start_time'])) {
			$onlyScheculeEvents = Events::getOnlyScheduleEvents();
			$combinedRounds = array('c', 'd', 'e', 'g');
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
					if ($format == '') {
						$this->addError($errorKey, '请设置赛制！');
						return false;
					}
					if (in_array($round, $combinedRounds)) {
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
		$rules = array(
			array('name, name_zh, date, reg_end', 'required'),
			array('entry_fee, second_stage_all, online_pay, person_num, check_person, fill_passport, local_type, live, status', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>10),
			array('wca_competition_id', 'length', 'max'=>32),
			array('name_zh', 'length', 'max'=>50),
			array('name', 'length', 'max'=>128),
			array('name', 'checkName', 'skipOnError'=>true),
			array('name', 'unique', 'className'=>'Competition', 'attributeName'=>'name', 'skipOnError'=>true),
			array('name_zh', 'unique', 'className'=>'Competition', 'attributeName'=>'name_zh', 'skipOnError'=>true),
			array('type', 'checkType', 'skipOnError'=>true),
			array('reg_start', 'checkRegistrationStart', 'skipOnError'=>true),
			array('reg_end', 'checkRegistrationEnd', 'skipOnError'=>true),
			array('second_stage_date', 'checkSecondStageDate', 'skipOnError'=>true),
			array('second_stage_ratio', 'checkSecondStageRatio', 'skipOnError'=>true),
			array('third_stage_date', 'checkThirdStageDate', 'skipOnError'=>true),
			array('third_stage_ratio', 'checkThirdStageRatio', 'skipOnError'=>true),
			array('locations', 'checkLocations', 'skipOnError'=>true),
			array('schedules', 'checkSchedules'),
			array('end_date, oldDelegate, oldDelegateZh, oldOrganizer, oldOrganizerZh, organizers, delegates, locations, schedules, regulations, regulations_zh, information, information_zh, travel, travel_zh, events', 'safe'),
			array('province, year, id, type, wca_competition_id, name, name_zh, date, end_date, reg_end, events, entry_fee, information, information_zh, travel, travel_zh, person_num, check_person, status', 'safe', 'on'=>'search'),
		);
		if (!(Yii::app() instanceof CConsoleApplication) && Yii::app()->user->checkRole(User::ROLE_ADMINISTRATOR)) {
			$rules[] = array('tba', 'safe');
		}
		if (!$this->isOld()) {
			$rules[] = array('organizers', 'required');
		} else {
			$rules[] = array('oldOrganizer, oldOrganizerZh', 'required');
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
			'organizer'=>array(self::HAS_MANY, 'CompetitionOrganizer', 'competition_id'),
			'delegate'=>array(self::HAS_MANY, 'CompetitionDelegate', 'competition_id'),
			'location'=>array(self::HAS_MANY, 'CompetitionLocation', 'competition_id'),
			'old'=>array(self::BELONGS_TO, 'OldCompetition', 'old_competition_id'),
			'schedule'=>array(self::HAS_MANY, 'Schedule', 'competition_id', 'order'=>'schedule.day,schedule.stage,schedule.start_time,schedule.end_time'),
			'operationFee'=>array(self::STAT, 'Registration', 'competition_id', 'condition'=>'status=1'),
			'liveResults'=>array(self::HAS_MANY, 'LiveResult', 'competition_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('Competition', 'ID'),
			'type' => Yii::t('Competition', 'Type'),
			'wca_competition_id' => Yii::t('Competition', 'Wca Competition ID'),
			'name' => Yii::t('Competition', 'Competition Name'),
			'name_zh' => Yii::t('Competition', 'Competition Name'),
			'date' => Yii::t('Competition', 'Date'),
			'end_date' => Yii::t('Competition', 'End Date'),
			'reg_start' => Yii::t('Competition', 'Registration Starting Time'),
			'reg_end' => Yii::t('Competition', 'Registration Ending Time'),
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
			'regulations' => Yii::t('Competition', 'Regulations'),
			'regulations_zh' => Yii::t('Competition', 'Regulations'),
			'information' => Yii::t('Competition', 'Information'),
			'information_zh' => Yii::t('Competition', 'Information'),
			'travel' => Yii::t('Competition', 'Travel'),
			'travel_zh' => Yii::t('Competition', 'Travel'),
			'person_num' => Yii::t('Competition', 'Person Num'),
			'check_person' => Yii::t('Competition', 'Check Person'),
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
		$criteria->with = array('location', 'location.province', 'location.city');
		$criteria->compare('t.id', $this->id, true);
		$criteria->compare('t.type', $this->type, true);
		$criteria->compare('t.wca_competition_id', $this->wca_competition_id, true);
		$criteria->compare('t.name', $this->name, true);
		$criteria->compare('t.name_zh', $this->name_zh, true);
		$criteria->compare('t.date', $this->date, true);
		$criteria->compare('t.end_date', $this->end_date, true);
		$criteria->compare('t.reg_end', $this->reg_end, true);
		$criteria->compare('t.events', $this->events, true);
		$criteria->compare('t.entry_fee', $this->entry_fee);
		$criteria->compare('t.online_pay', $this->online_pay);
		$criteria->compare('t.information', $this->information, true);
		$criteria->compare('t.information_zh', $this->information_zh, true);
		$criteria->compare('t.travel', $this->travel, true);
		$criteria->compare('t.travel_zh', $this->travel_zh, true);
		$criteria->compare('t.person_num', $this->person_num);
		$criteria->compare('t.check_person', $this->check_person);
		if ($this->status !== '' && $this->status !== null) {
			$criteria->compare('t.status', $this->status);
		} else {
			$criteria->compare('t.status', array(
				Competition::STATUS_SHOW,
				Competition::STATUS_HIDE,
			));
		}

		if (!$admin) {
			if ($this->year === 'current') {
				$criteria->compare('t.date', '>=' . (time() - 86400 * 184));
			} elseif (in_array($this->year, self::getYears())) {
				$criteria->compare('t.date', '>=' . strtotime($this->year . '-01-01'));
				$criteria->compare('t.date', '<=' . strtotime($this->year . '-12-31'));
			}
			if ($this->event !== '') {
				$criteria->compare('t.events', '"' . $this->event . '"', true);
			}
			if ($this->province > 0) {
				unset($criteria->with[0]);
				$criteria->with = array(
					'location'=>array(
						'together'=>true,
					),
				);
				$criteria->compare('location.province_id', $this->province);
			}
		}

		if ($admin && Yii::app()->controller->user->isOrganizer()) {
			$criteria->with = array(
				'organizer'=>array(
					'together'=>true,
				),
				'location', 'location.province', 'location.city'
			);
			$criteria->compare('organizer.organizer_id', Yii::app()->user->id);
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
