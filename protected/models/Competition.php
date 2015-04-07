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
 * @property integer $province_id
 * @property integer $city_id
 * @property string $venue
 * @property string $venue_zh
 * @property string $events
 * @property integer $entry_fee
 * @property string $alipay_url
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

	const STATUS_HIDE = 0;
	const STATUS_SHOW = 1;
	const STATUS_DELETE = 2;

	const NOT_CHECK_PERSON = 0;
	const CHECK_PERSON = 1;

	private $_organizers;
	private $_delegates;
	private $_locations;
	private $_schedules;
	private $_description;

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
		if ($second < 3600) {
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
			'order'=>'date ASC',
		));
	}

	public static function getUpcomingCompetitions($limit = 5) {
		$yesterday = strtotime('yesterday');
		return self::model()->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'condition'=>"date > {$yesterday} OR end_date > {$yesterday}",
			'limit'=>$limit,
			'order'=>'date ASC',
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
		if ($this->isMultiLocation()) {
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
		return CHtml::link($logo . $name, $this->getUrl(), array('class'=>'comp-type-' . strtolower($this->type)));
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
		return 'http://www.worldcubeassociation.org/results/c.php?i=' . $this->wca_competition_id;
	}

	public function getUrl($type = 'detail') {
		return array(
			'/competition/' . $type,
			'name'=>$this->getUrlName(),
		);
	}

	public function getUrlName() {
		return $this->alias;
	}

	public function getDisplayDate() {
		$date = date("Y-m-d", $this->date);
		if ($this->end_date > 0) {
			if (date('Y', $this->end_date) != date('Y', $this->date)) {
				$date .= date('~Y-m-d', $this->end_date);
			} elseif (date('m', $this->end_date) != date('m', $this->date)) {
				$date .= date('~m-d', $this->end_date);
			} else {
				$date .= date('~d', $this->end_date);
			}
		}
		return $date;
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
		}
		return $this->_schedules;
	}

	public function setSchedules($schedules) {
		$this->_schedules = $schedules;
	}

	public function getListableSchedules() {
		$listableSchedules = array();
		$schedules = $this->schedule;
		usort($schedules, array($this, 'sortSchedules'));
		$hasGroup = false;
		$specialEvents = array(
			'333fm'=>array(),
			'333mbf'=>array(),
		);
		foreach ($schedules as $key=>$schedule) {
			if (trim($schedule->group) != '') {
				$hasGroup = true;
			}
			if (isset($specialEvents[$schedule->event])) {
				$specialEvents[$schedule->event][$schedule->round][] = $key;
			}
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
						$event .= Yii::t('common', '(1st attempt)');
						break;
					case 2:
						$event .= Yii::t('common', '(2nd attempt)');
						break;
					case 3:
						$event .= Yii::t('common', '(3rd attempt)');
						break;
					default:
						$event .= Yii::t('common', '({times}th attempt)', array(
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
				'id'=>$schedule->id,
				'event'=>$schedule->event,
				'round'=>$schedule->round,
			);
			if ($hasGroup === false) {
				array_splice($temp, 3, 1);
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
		foreach (array_keys($schedules[0]) as $key) {
			if ($key == 'id' || $key == 'event' || $key == 'round') {
				continue;
			}
			$width = $this->getScheduleColumnWidth($key);
			$column = array(
				'name'=>$key,
				'header'=>Yii::t('Schedule', $key),
				'headerHtmlOptions'=>array(
					'style'=>sprintf("width: %dpx;min-width: %dpx", $width, $width),
				),
			);
			if ($key == 'Event') {
				$column['type'] = 'raw';
				$column['value'] = 'CHtml::tag("span", array(
					"class"=>"event-icon event-icon-" . $data["event"],
				), $data["Event"])';
			}
			$columns[] = $column;
		}
		return $columns;
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
		}
	}

	private function sortSchedules($scheduleA, $scheduleB) {
		if ($scheduleA['day'] < $scheduleB['day']) {
			return -1;
		} elseif ($scheduleA['day'] > $scheduleB['day']) {
			return 1;
		} else {
			$temp = date('Hi', $scheduleA['start_time']) - date('Hi', $scheduleB['start_time']);
			if ($temp != 0) {
				return $temp;
			}
			return date('Hi', $scheduleA['end_time']) - date('Hi', $scheduleB['end_time']);
		}
	}

	public function getOperationButton() {
		$buttons = array();
		$buttons[] = CHtml::link('预览', $this->getUrl('detail'), array('class'=>'btn btn-xs btn-orange btn-square', 'target'=>'_blank'));
		$buttons[] = CHtml::link('编辑', array('/board/competition/edit', 'id'=>$this->id), array('class'=>'btn btn-xs btn-blue btn-square'));
		if (Yii::app()->user->checkAccess(User::ROLE_DELEGATE)) {
			switch ($this->status) {
				case self::STATUS_HIDE:
					$buttons[] = CHtml::link('公示', array('/board/competition/show', 'id'=>$this->id), array('class'=>'btn btn-xs btn-green btn-square'));
					break;
				case self::STATUS_SHOW:
					$buttons[] = CHtml::link('隐藏', array('/board/competition/hide', 'id'=>$this->id), array('class'=>'btn btn-xs btn-red btn-square'));
					break;
			}
		}
		if ($this->status == self::STATUS_SHOW) {
			$buttons[] = CHtml::link('报名管理', array('/board/registration/index', 'Registration'=>array('competition_id'=>$this->id)), array('class'=>'btn btn-xs btn-purple btn-square'));
		}
		return implode(' ', $buttons);
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
				);
				continue;
			}
			if (!isset($temp[$key]['round']) || $temp[$key]['round'] == '') {
				$temp[$key]['round'] = 0;
			}
			if (!isset($temp[$key]['fee']) || $temp[$key]['fee'] == '') {
				$temp[$key]['fee'] = 0;
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
		$region = '$data->user->country->getAttributeValue("name")';
		if (Yii::app()->language == 'zh_cn') {
			$region .= '.$data->user->getRegionName($data->user->province).$data->user->getRegionName($data->user->city)';
		}
		$columns = array(
			array(
				'name'=>'number',
				'header'=>'No.',
				'value'=>'$data->number',
			),
			array(
				'name'=>'name',
				'header'=>Yii::t('Competition', 'Name'),
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
					), $headerText ? $event : ''),
					'headerHtmlOptions'=>array(
						'class'=>'header-event',
					),
					'type'=>'raw', 
					'value'=>"\$data->getEventsString('$event')",
				);
			}
		}
		return $columns;
	}

	public function handleDate() {
		foreach (array('date', 'end_date', 'reg_start', 'reg_end') as $attribute) {
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
		foreach (array('reg_start', 'reg_end') as $attribute) {
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
				'day'=>$oldSchedules['day'][$key],
				'stage'=>$oldSchedules['stage'][$key],
				'start_time'=>strtotime($oldSchedules['start_time'][$key]),
				'end_time'=>strtotime($oldSchedules['end_time'][$key]),
				'event'=>$oldSchedules['event'][$key],
				'group'=>$oldSchedules['group'][$key],
				'round'=>$oldSchedules['round'][$key],
				'format'=>$oldSchedules['format'][$key],
				'cut_off'=>$oldSchedules['cut_off'][$key],
				'time_limit'=>$oldSchedules['time_limit'][$key],
			);
		}
		$this->schedules = $schedules;
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
		return parent::beforeSave();
	}

	protected function afterSave() {
		if (Yii::app() instanceof CConsoleApplication) {
			return;
		}
		$isAdmin = Yii::app()->user->checkAccess(User::ROLE_DELEGATE);
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
				$model->day = $schedules['day'][$key];
				$model->stage = $schedules['stage'][$key];
				$model->event = $schedules['event'][$key];
				$model->group = $schedules['group'][$key];
				$model->round = $schedules['round'][$key];
				$model->format = $schedules['format'][$key];
				$model->cut_off = intval($schedules['cut_off'][$key]);
				$model->time_limit = intval($schedules['time_limit'][$key]);
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

	public function checkName() {
		if (!preg_match('{^[\'-a-z0-9& ]+$}i', $this->name)) {
			$this->addError('name', '英文名只能由字母、数字、空格、短杠-和单引号\'组成');
		}
		if (!preg_match('{^[a-z0-9 \x{4e00}-\x{9fc0}“”]+$}iu', $this->name_zh)) {
			$this->addError('name_zh', '中文名只能由中文、英文、数字、空格和双引号“”组成');
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
			if (empty($provinceId)) {
				$this->addError('locations.province_id.' . $index, '省份不能为空');
				$error = true;
			}
			if (empty($locations['city_id'][$key])) {
				$this->addError('locations.city_id.' . $index, '城市不能为空');
				$error = true;
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
				'province_id'=>$provinceId,
				'city_id'=>$locations['city_id'][$key],
				'venue'=>$locations['venue'][$key],
				'venue_zh'=>$locations['venue_zh'][$key],
			);
			$index++;
		}
		if ($error || count($temp) == 0) {
			$this->addError('locations', '地址填写有误，请检查各地址填写！');
		}
		$this->locations = $temp;
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
			array('province_id, city_id, entry_fee, person_num, check_person, status', 'numerical', 'integerOnly'=>true),
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
			array('venue, venue_zh, alipay_url', 'length', 'max'=>512),
			array('locations', 'checkLocations', 'skipOnError'=>true),
			array('end_date, oldDelegate, oldDelegateZh, oldOrganizer, oldOrganizerZh, organizers, delegates, locations, schedules, regulations, regulations_zh, information, information_zh, travel, travel_zh, events', 'safe'),
			array('province, year, id, type, wca_competition_id, name, name_zh, date, end_date, reg_end, province_id, city_id, venue, venue_zh, events, entry_fee, alipay_url, information, information_zh, travel, travel_zh, person_num, check_person, status', 'safe', 'on'=>'search'),
		);
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
			'alipay_url' => Yii::t('Competition', 'Alipay Url'),
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
		$criteria->compare('t.id', $this->id,true);
		$criteria->compare('t.type', $this->type,true);
		$criteria->compare('t.wca_competition_id', $this->wca_competition_id,true);
		$criteria->compare('t.name', $this->name,true);
		$criteria->compare('t.name_zh', $this->name_zh,true);
		$criteria->compare('t.date', $this->date,true);
		$criteria->compare('t.end_date', $this->end_date,true);
		$criteria->compare('t.reg_end', $this->reg_end,true);
		$criteria->compare('t.province_id', $this->province_id);
		$criteria->compare('t.city_id', $this->city_id);
		$criteria->compare('t.venue', $this->venue,true);
		$criteria->compare('t.venue_zh', $this->venue_zh,true);
		$criteria->compare('t.events', $this->events,true);
		$criteria->compare('t.entry_fee', $this->entry_fee);
		$criteria->compare('t.alipay_url', $this->alipay_url,true);
		$criteria->compare('t.information', $this->information,true);
		$criteria->compare('t.information_zh', $this->information_zh,true);
		$criteria->compare('t.travel', $this->travel,true);
		$criteria->compare('t.travel_zh', $this->travel_zh,true);
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
				'defaultOrder'=>'date DESC',
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
