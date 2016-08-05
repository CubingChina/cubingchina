<?php

class ImportCommand extends CConsoleCommand {
	private $_provinceId = 215;
	private $_cityId = 217;

	public function actionResult() {
		$file = PHPExcel_IOFactory::load(Yii::getPathOfAlias('application.data.xxx') . '.xls');
		$users = [];
		$competition = Competition::model()->findByPk(30);
		$registrations = Registration::getRegistrations($competition);
		foreach ($registrations as $registration) {
			$users[$registration->user->name_zh] = $registration;
		}
		$number = count($registrations) + 1;
		$liveUsers;
		foreach ($file->getAllSheets() as $sheet) {
			$title = $sheet->getTitle();
			var_dump($title);
			list($event, $round) = explode('-', $title);
			$eventRound = new LiveEventRound();
			$eventRound->competition_id = $competition->id;
			$eventRound->event = $event;
			$eventRound->round = $round;
			$eventRound->format = $event === '555' ? '3' : 'a';
			$eventRound->status = LiveEventRound::STATUS_FINISHED;
			$eventRound->save();
			for ($row = 3; ; $row++) {
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
						$user = new LiveUser();
						$user->name_zh = $name;
						$user->country_id = 1;
						$user->gender = $gender == '女' ? User::GENDER_FEMALE : User::GENDER_MALE;
						$user->save(false);
						$liveUsers[$name] = [
							'user'=>$user,
							'number'=>$number++,
						];
					}
					$result->number = $liveUsers[$name]['number'];
					$result->user_type = LiveResult::USER_TYPE_LIVE;
					$result->user_id = $liveUsers[$name]['user']->id;
				}
				$col++;
				for ($i = 1; $i <= 5; $i++) {
					$col++;
					$value = trim($sheet->getCell($col . $row)->getValue());
					$result->{'value' . $i} = $value === 'DNF' ? -1 : ($value === 'DNS' ? -1 : $value * 100);
				}
				$col++;
				$col++;
				$value = trim($sheet->getCell($col . $row)->getCalculatedValue());
				$result->best = $value === 'DNF' ? -1 : ($value === 'DNS' ? -1 : $value * 100);
				$col++;
				$col++;
				$col++;
				$col++;
				$value = trim($sheet->getCell($col . $row)->getCalculatedValue());
				$result->average = $value === 'DNF' ? -1 : ($value === 'DNS' ? -1 : $value * 100);
				$result->save();
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
