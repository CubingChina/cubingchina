<?php 
class ImportCommand extends CConsoleCommand {
	private $_provinceId = 215;
	private $_cityId = 217;

	public function actionCompetition() {
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
			$competition->name_zh = $oldCompetition['比赛名称'];
			$competition->date = strtotime($oldCompetition['比赛日期']);
			if ($oldCompetition['天数'] > 1) {
				$competition->end_date = $competition->date + 86400 * ($oldCompetition['天数'] - 1);
			}
			$competition->reg_end = $competition->date - 86400;
			$competition->old_competition_id = $oldCompetition['比赛id'];
			//地点
			$location = new CompetitionLocation();
			$location->province_id = $this->_provinceId;
			$location->city_id = $this->_cityId;
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
			$oldComp->delegate = OldCompetition::generateInfo($this->makeInfo($oldCompetition['监督代表']));
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
				->group('比赛成绩.项目id')
				->queryAll();
			if ($resultEvents !== array()) {
				foreach ($resultEvents as $value) {
					$eventName = $value['项目名'];
					$event = isset($this->_eventsMap[$eventName]) ? $this->_eventsMap[$eventName] : 'funny';
					$events[$event] = array(
						'round'=>1,
						'fee'=>0,
					);
				}
			} else {
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
