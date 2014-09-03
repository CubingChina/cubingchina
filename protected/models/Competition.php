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
 * @property string $reg_end_day
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
	private $_schedules;
	private $_description;

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

	public static function getUpcomingRegistrableCompetitions($limit = 5) {
		return self::model()->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'condition'=>'date>' . time() . ' AND reg_end_day>' . (time() - 86400),
			'limit'=>$limit,
			'order'=>'date ASC',
		));
	}

	public static function getUpcomingCompetitions($limit = 5) {
		return self::model()->findAllByAttributes(array(
			'status'=>self::STATUS_SHOW,
		), array(
			'condition'=>'date>' . time(),
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

	public static function getCompetitionById($id) {
		if (ctype_digit($id)) {
			$attribute = 'id';
		} else {
			$attribute = 'wca_competition_id';
		}
		$competition = self::model()->findByAttributes(array(
			$attribute=>$id,
		));
		return $competition;
	}

	public static function getCompetitionByName($name) {
		return self::model()->with('province', 'city')->findByAttributes(array(
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
			$id = trim($competition->wca_competition_id) ?: $competition->id;
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
			self::TYPE_OTHER=>'其它',
		);
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

	public function isRegistrationEnded() {
		return time() > strtotime(date('Y-m-d', $this->reg_end_day)) + 86400;
	}

	public function isEnded() {
		return time() > $this->date;
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
		$isCN = Yii::app()->controller->isCN;
		if ($isCN) {
			$venue = $this->province->getAttributeValue('name') . $this->city->getAttributeValue('name') . $this->getAttributeValue('venue');
		} else {
			$venue = $this->getAttributeValue('venue') . ', ' . $this->city->getAttributeValue('name') . ', ' . $this->province->getAttributeValue('name');
		}
		$params['{venue}'] = $venue;
		$organizers = '';
		$count = count($this->organizer);
		foreach ($this->organizer as $key=>$organizer) {
			if ($key == 0) {
				$organizers .= $organizer->user->getAttributeValue('name');
			} elseif ($key < $count - 1) {
				$organizers .= Yii::t('common', ', ') . $organizer->user->getAttributeValue('name');
			} else {
				$organizers .= Yii::t('common', ' and ') . $organizer->user->getAttributeValue('name');
			}
		}
		$params['{organizers}'] = $organizers;
		$delegates = '';
		$count = count($this->delegate);
		foreach ($this->delegate as $key=>$delegate) {
			if ($key == 0) {
				$delegates .= $delegate->user->getAttributeValue('name');
			} elseif ($key < $count - 1) {
				$delegates .= Yii::t('common', ', ') . $delegate->user->getAttributeValue('name');
			} else {
				$delegates .= Yii::t('common', ' and ') . $delegate->user->getAttributeValue('name');
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
		switch ($this->type) {
			case self::TYPE_WCA:
				$name = CHtml::image('/f/images/wca.png', Yii::t('common', 'WCA Competition'), array('class'=>'wca-competition')) . $name;
				break;
		}
		return CHtml::link($name, $this->getUrl(), array('class'=>'comp-type-' . strtolower($this->type)));
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

	public function getDelegates() {
		if ($this->_delegates === null) {
			$this->_delegates = CHtml::listData($this->delegate, 'delegate_id', 'delegate_id');
		}
		return $this->_delegates;
	}

	public function setDelegates($delegates) {
		$this->_delegates = $delegates;
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
				'Round'=>Yii::t('common', Rounds::getFullRoundName($schedule->round)),
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
		// var_dump($schedules[0]);exit;
		foreach ($schedules[0] as $key=>$value) {
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
			$s = date('Hi', $scheduleA['start_time']) - date('Hi', $scheduleB['start_time']);
			if ($s != 0) {
				return $s;
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

	public function export($exportFormsts, $all = 0, $xlsx = 0, $extra = 0, $order = 'date') {
		$attributes = array(
			'competition_id'=>$this->id,
		);
		if ($all == 0) {
			$attributes['status'] = Registration::STATUS_ACCEPTED;
		}
		if (!in_array($order, array('date', 'user.name'))) {
			$order = 'date';
		}
		$registrations = Registration::model()->with(array(
			'user'=>array(
				'condition'=>'user.status=' . User::STATUS_NORMAL,
			),
			'user.country',
		))->findAllByAttributes($attributes, array(
			'order'=>'date',
		));
		//计算序号
		$number = 1;
		foreach ($registrations as $registration) {
			if ($registration->isAccepted()) {
				$registration->number = $number++;
			}
		}
		usort($registrations, function ($rA, $rB) use($order) {
			if ($rA->number === $rB->number || ($rA->number !== null && $rB->number !== null)) {
				switch ($order) {
					case 'user.name':
						return strcmp($rA->user->getCompetitionName(), $rB->user->getCompetitionName());
					case 'date':
					default:
						return $rA->date - $rB->date;
				}
			}
			if ($rA->number === null) {
				return 1;
			}
			if ($rB->number === null) {
				return -1;
			}
			return 0;
		});
		$template = PHPExcel_IOFactory::load(Yii::getPathOfAlias('application.data.results') . '.xls');
		$export = new PHPExcel();
		$export->getProperties()
			->setCreator(Yii::app()->params->author)
			->setLastModifiedBy(Yii::app()->params->author)
			->setTitle($this->wca_competition_id ?: $this->name)
			->setSubject($this->name);
		$export->removeSheetByIndex(0);
		//注册页
		$sheet = $template->getSheet(0);
		$sheet->setCellValue('A1', $this->wca_competition_id ?: $this->name);
		$events = $this->getRegistrationEvents();
		$col = 'J';
		$cubecompsEvents = array(
			'333'=>'3x3',
			'444'=>'4x4',
			'555'=>'5x5',
			'666'=>'6x6',
			'777'=>'7x7',
			'222'=>'2x2',
			'333bf'=>'333bld',
			'333fm'=>'fmc',
			'minx'=>'mega',
			'pyram'=>'pyra',
			'444bf'=>'444bld',
			'555bf'=>'555bld',
			'333mbf'=>'333mlt',
		);
		foreach ($events as $event=>$data) {
			$sheet->setCellValue($col . 2, "=SUM({$col}4:{$col}" . (count($registrations) + 4) . ')');
			$sheet->setCellValue($col . 3, isset($cubecompsEvents[$event]) ? $cubecompsEvents[$event] : $event);
			$sheet->getColumnDimension($col)->setWidth(5.5);
			$col++;
		}
		foreach ($registrations as $key=>$registration) {
			$user = $registration->user;
			$row = $key + 4;
			$sheet->setCellValue('A' . $row, $registration->number)
				->setCellValue('B' . $row, $user->getCompetitionName())
				->setCellValue('C' . $row, $user->country->name)
				->setCellValue('D' . $row, $user->wcaid)
				->setCellValue('E' . $row, $user->getWcaGender())
				->setCellValue('F' . $row, date('Y-m-d', $user->birthday));
			$col = 'J';
			foreach ($events as $event=>$data) {
				if (in_array("$event", $registration->events)) {
					$sheet->setCellValue($col . $row, 1);
				}
				$col++;
			}
			if ($extra) {
				$col++;
				$sheet->setCellValue($col . $row, $user->mobile);
				$col++;
				$sheet->setCellValue($col . $row, $user->email);
			}
			if (!$registration->isAccepted()) {
				$sheet->getStyle("A{$row}:D{$row}")->applyFromArray(array(
					'fill'=>array(
						'type'=>PHPExcel_Style_Fill::FILL_SOLID,
						'color'=>array(
							'argb'=>'FFFFFF00',
						),
					),
		 		));
			}
		}
		$export->addExternalSheet($sheet);
		//各个项目
		foreach ($exportFormsts as $event=>$rounds) {
			$count = count($rounds);
			foreach ($rounds as $round=>$format) {
				if ($round == $count - 1) {
					$round = 'f';
				} else {
					$round++;
				}
				$sheet = $template->getSheetByName($format);
				if ($sheet === null) {
					continue;
				}
				$sheet = clone $sheet;
				$sheet->setTitle("{$event}-{$round}");
				$template->addSheet($sheet);
				$sheet->setCellValue('A1', Events::getFullEventName($event) . ' - ' . Rounds::getFullRoundName($round));
				if ($round == 1 || $count == 1) {
					$row = 5;
					foreach ($registrations as $registration) {
						if (!in_array("$event", $registration->events)) {
							continue;
						}
						$user = $registration->user;
						$sheet->setCellValue('B' . $row, $user->getCompetitionName())
							->setCellValue('C' . $row, $user->country->name)
							->setCellValue('D' . $row, $user->wcaid);
						if ($row > 5) {
							$formula = $sheet->getCell('A' . ($row - 1))->getValue();
							$formula = strtr($formula, array(
								'-4'=>'_temp_',
								$row - 1=>$row,
								$row - 2=>$row - 1,
								$row=>$row+1,
							));
							$formula = str_replace('_temp_', '-4', $formula);
							$sheet->setCellValue('A' . $row, $formula);
						}
						if (!$registration->isAccepted()) {
							$sheet->getStyle("A{$row}:D{$row}")->applyFromArray(array(
								'fill'=>array(
									'type'=>PHPExcel_Style_Fill::FILL_SOLID,
									'color'=>array(
										'argb'=>'FFFFFF00',
									),
								),
					 		));
						}
						$row++;
					}
				}
				$export->addExternalSheet($sheet);
			}
		}
		$export->setActiveSheetIndex(0);
		Yii::app()->controller->setIsAjaxRequest(true);
		if ($xlsx) {
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="' . $this->name . '.xlsx"');
			$objWriter = PHPExcel_IOFactory::createWriter($export, 'Excel2007');
		} else {
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $this->name . '.xls"');
			$objWriter = PHPExcel_IOFactory::createWriter($export, 'Excel5');
		}
		$objWriter->setPreCalculateFormulas(false);
		$objWriter->save('php://output');
		exit;
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
		foreach (array('date', 'end_date', 'reg_end_day') as $attribute) {
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
		foreach (array('date', 'end_date', 'reg_end_day') as $attribute) {
			if (!empty($this->$attribute)) {
				$this->$attribute = date('Y-m-d', $this->$attribute);
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
				'stage'=>$oldSchedules['start_time'][$key],
				'start_time'=>strtotime($oldSchedules['start_time'][$key]),
				'end_time'=>strtotime($oldSchedules['start_time'][$key]),
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
		$this->name = preg_replace('{ +}', ' ', $this->name);
		$this->name_zh = preg_replace('{ +}', ' ', $this->name_zh);
		$this->alias = str_replace(' ', '-', $this->name);
		$this->alias = preg_replace('{[^-a-z0-9]}i', '', $this->alias);
		return parent::beforeSave();
	}

	protected function afterSave() {
		$isAdmin = Yii::app()->user->checkAccess(User::ROLE_ADMINISTRATOR);
		//处理代表和主办
		foreach (array('organizer', 'delegate') as $attribute) {
			$attributeId = $attribute . '_id';
			$oldValues = array_values(CHtml::listData($this->$attribute, $attributeId, $attributeId));
			$newValues = array_values($this->{$attribute . 's'});
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
	}

	public function checkRegistrationEnd() {
		if (date('Ymd', $this->reg_end_day) >= date('Ymd', $this->date)) {
			$this->addError('reg_end_day', '注册截止时间必须早于比赛开始至少一天');
		}
	}

	public function checkName() {
		if (!preg_match('{^[\'-a-z0-9 ]+$}i', $this->name)) {
			$this->addError('name', '英文名只能由英文字母、数字和空格组成');
		}
		if (!preg_match('{^[a-z0-9 \x{4e00}-\x{9fc0}]+$}iu', $this->name_zh)) {
			$this->addError('name_zh', '中文名只能由中文、英文、数字和空格组成');
		}
	}

	public function checkType() {
		if ($this->type == self::TYPE_WCA && empty($this->delegates)) {
			$this->addError('delegates', 'WCA比赛需至少选择一名代表！');
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
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('organizers, venue, venue_zh, province_id, city_id, name, name_zh, date', 'required'),
			array('province_id, city_id, entry_fee, person_num, check_person, status', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>10),
			array('wca_competition_id', 'length', 'max'=>32),
			array('name_zh', 'length', 'max'=>50),
			array('name', 'length', 'max'=>128),
			array('name', 'checkName', 'skipOnError'=>true),
			array('name', 'unique', 'className'=>'Competition', 'attributeName'=>'name', 'skipOnError'=>true),
			array('name_zh', 'unique', 'className'=>'Competition', 'attributeName'=>'name_zh', 'skipOnError'=>true),
			array('type', 'checkType', 'skipOnError'=>true),
			array('date, end_date, reg_end_day', 'length', 'max'=>11, 'skipOnError'=>true),
			array('reg_end_day', 'checkRegistrationEnd', 'skipOnError'=>true),
			array('venue, venue_zh, alipay_url', 'length', 'max'=>512),
			array('organizers, delegates, schedules, regulations, regulations_zh, information, information_zh, travel, travel_zh, events', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, type, wca_competition_id, name, name_zh, date, end_date, reg_end_day, province_id, city_id, venue, venue_zh, events, entry_fee, alipay_url, information, information_zh, travel, travel_zh, person_num, check_person, status', 'safe', 'on'=>'search'),
		);
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
			'schedule'=>array(self::HAS_MANY, 'Schedule', 'competition_id', 'order'=>'schedule.day,schedule.stage,schedule.start_time,schedule.end_time'),
			'province'=>array(self::BELONGS_TO, 'Region', 'province_id'),
			'city'=>array(self::BELONGS_TO, 'Region', 'city_id'),
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
			'reg_end_day' => Yii::t('Competition', 'Registration Ending Time'),
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
	public function search() {
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria = new CDbCriteria;
		$criteria->with = array('province', 'city');
		$criteria->compare('t.id', $this->id,true);
		$criteria->compare('t.type', $this->type,true);
		$criteria->compare('t.wca_competition_id', $this->wca_competition_id,true);
		$criteria->compare('t.name', $this->name,true);
		$criteria->compare('t.name_zh', $this->name_zh,true);
		$criteria->compare('t.date', $this->date,true);
		$criteria->compare('t.end_date', $this->end_date,true);
		$criteria->compare('t.reg_end_day', $this->reg_end_day,true);
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
		$criteria->compare('t.status', $this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'date DESC',
			),
			'pagination'=>array(
				'pageVar'=>'page',
				'pageSize'=>50,
			),
		));
	}

	public function adminSearch() {

		$criteria = new CDbCriteria;

		$criteria->compare('t.id', $this->id,true);
		$criteria->compare('t.type', $this->type,true);
		$criteria->compare('t.wca_competition_id', $this->wca_competition_id,true);
		$criteria->compare('t.name', $this->name,true);
		$criteria->compare('t.name_zh', $this->name_zh,true);
		$criteria->compare('t.date', $this->date,true);
		$criteria->compare('t.end_date', $this->end_date,true);
		$criteria->compare('t.reg_end_day', $this->reg_end_day,true);
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
		$criteria->compare('t.status', $this->status);

		if (Yii::app()->controller->user->isOrganizer()) {
			$criteria->with = array(
				'organizer'=>array(
					'together'=>true,
				),
			);
			$criteria->compare('organizer.organizer_id', Yii::app()->user->id);
		}

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>'t.date DESC',
			)
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
