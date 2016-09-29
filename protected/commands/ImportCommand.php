<?php

class ImportCommand extends CConsoleCommand {
	private $_provinceId = 215;
	private $_cityId = 217;

	public function actionHeatUser() {
		$competition = Competition::model()->findByPk(440);
		$heatSchedules = HeatSchedule::model()->findAllByAttributes([
			'competition_id'=>$competition->id,
		]);
		$temp = [];
		foreach ($heatSchedules as $heatSchedule) {
			$temp[$heatSchedule->event][] = $heatSchedule;
		}
		$heatSchedules = $temp;
		$registrations = Registration::getRegistrations($competition);
		$temp = [];
		foreach ($registrations as $registration) {
			foreach ($registration->events as $event) {
				$temp[$event][$registration->user_id] = $registration;
			}
		}
		$registrations = $temp;
		$userSchedules = [];
		foreach ($heatSchedules as $event=>$schedules) {
			$wcaIds = [];
			foreach ($registrations[$event] as $registration) {
				if ($registration->user->wcaid) {
					$wcaIds[$registration->user->wcaid] = $registration;
				}
			}
			switch ($event) {
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
				'eventId'=>$event,
				'personId'=>array_keys($wcaIds),
			));
			foreach ($results as $result) {
				$wcaIds[$result->personId]->best = $result->best;
			}
			uasort($registrations[$event], function($rA, $rB) {
				if ($rA->best > 0 && $rB->best > 0) {
					$temp = $rA->best - $rB->best;
				} elseif ($rA->best > 0) {
					$temp = -1;
				} elseif ($rB->best > 0) {
					$temp = 1;
				} else {
					$temp = 0;
				}
				return -$temp;
			});
			$i = 0;
			$count = count($schedules);
			foreach ($registrations[$event] as $registration) {
				$schedule = $schedules[$i % $count];
				if ($event == '777' && isset($registrations['333fm'][$registration->user_id])) {
					$j = $i;
					while ($j < $i + $count) {
						if ($schedule->start_time >= $heatSchedules['333fm'][0]->end_time) {
							break;
						}
						$schedule = $schedules[++$j % $count];
					}
				} elseif (isset($userSchedules[$registration->user_id][$schedule->day])) {
					$j = $i;
					$temp = $userSchedules[$registration->user_id][$schedule->day];
					while ($j < $i + $count) {
						if (!isset($temp[$schedule->start_time])) {
							break;
						}
						$schedule = $schedules[++$j % $count];
					}
				}
				$heatScheduleUser = new HeatScheduleUser();
				$heatScheduleUser->schedule = $schedule;
				$heatScheduleUser->heat_id = $schedule->id;
				$heatScheduleUser->competition_id = $schedule->competition_id;
				$heatScheduleUser->user_id = $registration->user_id;
				$heatScheduleUser->save();
				if (!in_array($schedule->event, ["333bf", "444bf", "555bf", "333mbf", "333fm"])) {
					$userSchedules[$registration->user_id][$schedule->day][$schedule->start_time] = $heatScheduleUser;
				}
				$i++;
			}
		}
	}

	public function actionHeat() {
		$competition = Competition::model()->findByPk(440);
		$stageNums = [
			'main'=>[16, 16, 16],
			'side'=>[24],
		];
		$events = [];
		foreach ($competition->getListableSchedules() as $day=>$stages) {
			foreach ($stages as $stage=>$schedules) {
				foreach ($schedules as $schedule) {
					$schedule = $schedule['schedule'];
					if (isset($events[$schedule->event])) {
						continue;
					}
					$events[$schedule->event] = 1;
					if (!isset($stageNums[$stage])) {
						$heatSchedule = new HeatSchedule();
						$heatSchedule->attributes = $schedule->attributes;
						$heatSchedule->save();
						continue;
					}
					$group = count($stageNums[$stage]) > 1 ? 'A' : '';
					foreach ($stageNums[$stage] as $num) {
						for ($time = $schedule->start_time; $time < $schedule->end_time; $time += 15*60) {
							$heatSchedule = new HeatSchedule();
							$heatSchedule->attributes = $schedule->attributes;
							$heatSchedule->start_time = $time;
							$heatSchedule->end_time = min($schedule->end_time, $time + 900);
							$heatSchedule->group = $group;
							$heatSchedule->save();
						}
						$group++;
					}
				}
			}
		}
	}

	public function actionAC2() {
		$competition = Competition::model()->findByPk(440);
		$registrations = Registration::getRegistrations($competition);
		$times = [
			"333"=>[2],
			"444"=>[2],
			"555"=>[2],
			"222"=>[2],
			"333bf"=>[],
			"333oh"=>[2],
			"333fm"=>[],
			"333ft"=>[0],
			"minx"=>[1],
			"pyram"=>[2],
			"sq1"=>[1],
			"clock"=>[1],
			"skewb"=>[2],
			"666"=>[0],
			"777"=>[0],
			"444bf"=>[],
			"555bf"=>[],
			"333mbf"=>[],
		];
		$coordinates = [
			[334, 711],
			[479, 711],
			[334, 856],
			[479, 856],
		];
		$basePath = Yii::getPathOfAlias('application.data');
		$draw = new ImagickDraw();
		$draw->setFont($basePath . '/msyhbd.ttf');
		$draw->setFontSize(36);
		$draw->setFontWeight(700);
		$draw->setFillColor(new ImagickPixel('white'));
		$corner = new Imagick($basePath . '/corner.jpg');
		foreach ($registrations as $registration) {
			$number = $registration->number;
			$text = $registration->user->getCompetitionName();
			$image = new Imagick($basePath . '/cert.jpg');
			$len1 = mb_strlen($text, 'utf8');
			$len2 = strlen($text);
			$len = $len1 + ceil(($len2 - $len1) / 3);
			$image->annotateImage($draw, 298 - 25, 1010, 0, $text);
			$image->annotateImage($draw, (874 + 85) / 2 - 25 - strlen("$number") * 5, 1110, 0, "$number");
			$numbers = [];
			foreach ($registration->events as $event) {
				$numbers = array_merge($numbers, $times[$event]);
			}
			foreach (array_unique($numbers) as $i) {
				$coordinate = $coordinates[$i];
				$image->compositeImage($corner, Imagick::COMPOSITE_DEFAULT, $coordinate[0], $coordinate[1]);
			}
			$image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
			$image->writeImage($basePath . '/competitors/' . $number . '.jpg');
			var_dump($number);
			// if ($number > 10) {break;}
		}
	}

	public function actionAC() {
		$competition = Competition::model()->findByPk(440);
		$registrations = Registration::getRegistrations($competition);
		$users = [];
		foreach ($registrations as $registration) {
			$users[$registration->user->name_zh ?: $registration->user->name] = $registration;
		}
		$excel = PHPExcel_IOFactory::load(Yii::getPathOfAlias('application.data') . '/AC2016 Staff.xlsx');
		$sheet = $excel->getSheet(0);
		$events = Events::getNormalEvents();
		for ($i = 2; ; $i++) {
			$name = $sheet->getCell('B' . $i)->getValue();
			if ($name == '') {
				break;
			}
			$passport = $sheet->getCell('G' . $i)->getValue();
			$mobile = $sheet->getCell('F' . $i)->getValue();
			if (isset($users[$name])) {
				if ($passport == '') {
					$sheet->getCell('G' . $i)->setValue("'" . $users[$name]->passport_number);
				}
				if ($mobile == '') {
					$sheet->getCell('F' . $i)->setValue($users[$name]->user->mobile);
				}
				$col = 'M';
				foreach ($events as $event=>$e) {
					if (in_array("$event", $users[$name]->events)) {
						$sheet->getCell($col . $i)->setValue(1);
					}
					$col++;
				}
			}
		}
		$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$writer->setPreCalculateFormulas(true);
		$writer->save(Yii::getPathOfAlias('application.data') . '/AC2016 Staff1.xlsx');
	}

	public function actionResult($file, $comp) {
		$file = PHPExcel_IOFactory::load(Yii::getPathOfAlias('application.data') . '/' . $file);
		$users = [];
		$competition = Competition::model()->findByPk($comp);
		$registrations = Registration::getRegistrations($competition);
		foreach ($registrations as $registration) {
			$users[$registration->user->getCompetitionName()] = $registration;
		}
		$number = count($registrations) + 1;
		$liveUsers;
		foreach ($file->getAllSheets() as $sheet) {
			$title = $sheet->getTitle();
			list($event, $round) = explode('-', $title);
			$formatString = trim($sheet->getCell('A2')->getValue());
			if (strpos($formatString, 'average') !== false) {
				$format = 'a';
			} elseif (strpos($formatString, 'mean') !== false) {
				$format = 'm';
			} elseif (($pos = strpos($formatString, 'best of ')) !== false) {
				$format = substr($formatString, $pos + 8, 1);
			} else {
				$format = 'a';
			}
			$eventRound = new LiveEventRound();
			$eventRound->competition_id = $competition->id;
			$eventRound->event = $event;
			$eventRound->round = $round;
			$eventRound->format = $format;
			$eventRound->status = LiveEventRound::STATUS_FINISHED;
			$eventRound->save();
			switch ($format) {
				case '1':
				case '2':
				case '3':
					$valueNum = $format;
					break;
				case 'm':
					$valueNum = 3;
					break;
				default:
					$valueNum = 5;
					break;
			}
			for ($row = 5; ; $row++) {
				$col = 'B';
				$name = trim($sheet->getCell($col . $row)->getValue());
				if ($name === '') {
					break;
				}
				$col++;
				$gender = trim($sheet->getCell($col . $row)->getValue());
				$result = new LiveResult();
				$result->competition_id = $competition->id;
				$result->event = $event;
				$result->round = $round;
				if (isset($users[$name])) {
					$result->number = $users[$name]->number;
					$result->user_id = $users[$name]->user_id;
				} else {
					if (!isset($liveUsers[$name])) {
						preg_match('{([^(]+)( \([)]+\))?}i', $name, $matches);
						$attributes = [
							'name'=>$matches[1],
						];
						if (isset($matches[3])) {
							$attributes['name_zh'] = $matches[3];
						}
						$user = User::model()->findByAttributes($attributes);
						if ($user === null) {
							$user = new LiveUser();
							$user->name = $matches[1];
							$user->name_zh = isset($matches[3]) ? $matches[3] : '';
							$user->country_id = 1;
							$user->gender = $gender == 'f' ? User::GENDER_FEMALE : User::GENDER_MALE;
							$user->save(false);
						}
						$liveUsers[$name] = [
							'user'=>$user,
							'number'=>$number++,
						];
					}
					$result->number = $liveUsers[$name]['number'];
					$result->user_type = $liveUsers[$name]['user'] instanceof User ? 0 : LiveResult::USER_TYPE_LIVE;
					$result->user_id = $liveUsers[$name]['user']->id;
				}
				$col++;
				for ($i = 1; $i <= $valueNum; $i++) {
					$col++;
					$value = trim($sheet->getCell($col . $row)->getValue());
					$result->{'value' . $i} = $value === 'DNF' ? -1 : ($value === 'DNS' ? -2 : $value * 100);
				}
				$col++;
				$value = trim($sheet->getCell($col . $row)->getCalculatedValue());
				$result->best = $value === 'DNF' ? -1 : ($value === 'DNS' ? -1 : $value * 100);
				if ($format == 'm' || $format == 'a') {
					if ($format == 'a') {
						$col++;
					}
					$col++;
					$col++;
					$value = trim($sheet->getCell($col . $row)->getCalculatedValue());
					$result->average = $value === 'DNF' ? -1 : ($value === 'DNS' ? -1 : intval($value * 100));
				}
				$r = $result->save();
				if (!$r) {
					var_dump($result->errors, $result->average);
				}
			}
		}
	}

	public function actionCompetition() {
		$provinces = CHtml::listData(Region::getRegionsByPid(1), 'id', 'name_zh');
		$cities = Yii::app()->db
			->cache(86400)
			->createCommand()
			->select('*')
			->from('region')
			->where('pid>1')
			->order('id')
			->queryAll();
		$allCities = array();
		foreach ($cities as $city) {
			if (!isset($allCities[$city['pid']])) {
				$allCities[$city['pid']] = array();
			}
			$allCities[$city['pid']][$city['id']] = $city['name_zh'];
		}
		$cities = $allCities;
		$db = Yii::app()->db;
		$db->createCommand()->truncateTable('old_competition');
		$oldEvents = $db->createCommand()
			->select('*')
			->from('cubingchina_mf8.比赛项目')
			->order('项目顺序')
			->queryAll();
		$oldEvents = array_combine(array_map(function($event) {
			return $event['项目顺序'];
		}, $oldEvents), $oldEvents);
		$oldCompetitions = $db->createCommand()
			->select('*')
			->from('cubingchina_mf8.比赛事件')
			->order('比赛id')
			->queryAll();
		foreach ($oldCompetitions as $oldCompetition) {
			$competition = new Competition();
			//基本信息
			$competition->name_zh = str_replace(' ', '', $oldCompetition['比赛名称']);
			$competition->date = strtotime($oldCompetition['比赛日期']);
			if ($oldCompetition['天数'] > 1) {
				$competition->end_date = $competition->date + 86400 * ($oldCompetition['天数'] - 1);
			}
			$competition->reg_end = $competition->date - 86400;
			$competition->old_competition_id = $oldCompetition['比赛id'];
			//地点
			$location = new CompetitionLocation();
			$detected = false;
			$address = $oldCompetition['地址'] . $oldCompetition['比赛名称'];
			foreach ($provinces as $provinceId=>$province) {
				if (strpos($address, $province) !== false) {
					$location->province_id = $provinceId;
					$location->city_id = $this->_cityId;
					foreach ($cities[$provinceId] as $cityId=>$city) {
						if (mb_strlen($city, 'utf-8') > 2) {
							$city = mb_substr($city, 0, -1, 'utf-8');
						}
						if (strpos($address, $city) !== false) {
							$location->city_id = $cityId;
							break;
						}
					}
					$detected = true;
					break;
				}
			}
			if (!$detected) {
				$location->province_id = $this->_provinceId;
				$location->city_id = $this->_cityId;
			}
			$location->venue_zh = $oldCompetition['地址'];
			$competition->location = array($location);

			if ($oldCompetition['是否wca']) {
				$competition->type = Competition::TYPE_WCA;
				$wcaCompetitions = Competitions::model()->findAllByAttributes(array(
					'countryId'=>'China',
					'year'=>date('Y', $competition->date),
					'month'=>date('m', $competition->date),
					'day'=>date('d', $competition->date),
				));
				$wcaCompetition = null;
				if (count($wcaCompetitions) == 1) {
					$wcaCompetition = $wcaCompetitions[0];
				} else {
					foreach ($wcaCompetitions as $value) {
						if (strpos($value->website, '=' . $competition->old_competition_id) !== false) {
							$wcaCompetition = $value;
							break;
						}
					}
				}
				if ($wcaCompetition !== null) {
					$competition->name = $wcaCompetition->name;
					$competition->wca_competition_id = $wcaCompetition->id;
					$location->venue = $wcaCompetition->venueDetails . ', ' . $wcaCompetition->venueAddress;
				}
			} else {
				$competition->type = Competition::TYPE_OTHER;
			}
			//代表和主办
			$oldComp = new OldCompetition();
			$oldComp->id = $oldCompetition['比赛id'];
			$oldComp->delegate_zh = OldCompetition::generateInfo($this->makeInfo($oldCompetition['监督代表']));
			$organizer = $this->makeInfo($oldCompetition['主办方']);
			if ($oldCompetition['主办电邮']) {
				$organizer[0]['email'] = $oldCompetition['主办电邮'];
			}
			$oldComp->organizer_zh = OldCompetition::generateInfo($organizer);
			$oldComp->save(false);
			//项目
			$events = array();
			$resultEvents = $db->createCommand()
				->select('*')
				->from('cubingchina_mf8.比赛成绩')
				->leftJoin('cubingchina_mf8.比赛项目', '比赛项目.项目id=比赛成绩.项目id')
				->where('比赛成绩.事件id=' . $oldCompetition['比赛id'])
				->group('比赛成绩.项目id, 比赛成绩.第N轮')
				->queryAll();
			if ($resultEvents !== array()) {
				foreach ($resultEvents as $value) {
					$eventName = $value['项目名'];
					$event = isset($this->_eventsMap[$eventName]) ? $this->_eventsMap[$eventName] : 'funny';
					if (!isset($events[$event])) {
						$events[$event] = array(
							'round'=>0,
							'fee'=>0,
						);
					}
					$events[$event]['round']++;
				}
			}
			if ($events === array() && $competition->wca_competition_id !== '') {
				$resultEvents = Results::model()->findAllByAttributes(array(
					'competitionId'=>$competition->wca_competition_id,
				), array(
					'group'=>'eventId,roundId',
				));
				if ($resultEvents !== array()) {
					foreach ($resultEvents as $value) {
						$event = $value->eventId;
						if (!isset($events[$event])) {
							$events[$event] = array(
								'round'=>0,
								'fee'=>0,
							);
						}
						$events[$event]['round']++;
					}
				}
			}
			if ($events === array()) {
				for ($i = 0; $i < strlen($oldCompetition['比赛项目']); $i++) {
					if ($oldCompetition['比赛项目']{$i}) {
						$eventName = $oldEvents[$i + 1]['项目名'];
						$event = isset($this->_eventsMap[$eventName]) ? $this->_eventsMap[$eventName] : 'funny';
						$events[$event] = array(
							'round'=>1,
							'fee'=>0,
						);
					}
				}
			}
			$competition->events = $events;
			$competition->handleEvents();
			$ret = $competition->save(false);
			$location->competition_id = $competition->id;
			$location->save(false);
		}
	}

	private function makeInfo($info) {
		return array_filter(array_map(function($name) {
			return array(
				'name'=>$name,
			);
		}, preg_split('{[/,\s，、]+}u', $info)));
	}

	private $_eventsMap = array(
		"2x2 Blindfolded"=>'funny',
		"2x2 Cube"=>'222',
		"3x3 Blindfolded"=>'333bf',
		"3x3 Cube"=>'333',
		"3x3 Fewest move"=>'333fm',
		"3x3 Multi blind"=>'333mbf',
		"3x3 One-handed"=>'333oh',
		"3x3 With feet"=>'333ft',
		"4x4 Blindfolded"=>'444bf',
		"4x4 Cube"=>'444',
		"5x5 Blindfolded"=>'555bf',
		"5x5 Cube"=>'555',
		"6x6 Blindfolded"=>'funny',
		"6x6 Cube"=>'666',
		"7x7 Blindfolded"=>'funny',
		"7x7 Cube"=>'777',
		"Clock"=>'clock',
		"Crazy3x3-Earth"=>'funny',
		"Crazy3x3-Juoiter"=>'funny',
		"Crazy3x3-Mars"=>'funny',
		"Crazy3x3-Mercury"=>'funny',
		"Crazy3x3-Neptune"=>'funny',
		"Crazy3x3-Saturn"=>'funny',
		"Crazy3x3-Uranus"=>'funny',
		"Crazy3x3-Venus"=>'funny',
		"Gear Cube"=>'funny',
		"Master Magic"=>'mmagic',
		"Megaminx"=>'minx',
		"Mirror"=>'funny',
		"Pyraminx"=>'pyram',
		"Rubiks Magic"=>'magic',
		"Skewb"=>'skewb',
		"speedstack"=>'stack',
		"Square 1"=>'sq1',
		"SuoerSQ1"=>'funny',
	);
}
