<?php
use GuzzleHttp\Client;

class WcaCommand extends CConsoleCommand {
	private $_panalties = [];

	public function actionUpdate() {
		$this->log('start update');
		$client = new Client();
		try {
			$response = $client->get('https://www.worldcubeassociation.org/delegates');
			if ($response->getStatusCode() != 200) {
				throw new Exception('Error response');
			}
			$body = $response->getBody();
			if (!preg_match_all('|href="mailto:(?P<email>[^"]+)".+?href="/persons/(?P<wcaid>[^"]+)".+?<div class="name">(?P<name>[^<]+)</div>|s', $body, $matches)) {
				throw new Exception('Error response');
			}
			Delegates::model()->deleteAll();
			foreach ($matches['wcaid'] as $key=>$wcaid) {
				$delegate = new Delegates();
				$delegate->wca_id = $wcaid;
				$delegate->email = $matches['email'][$key];
				$delegate->name = $matches['name'][$key];
				$delegate->save();
			}
		} catch (Exception $e) {
		}
		$this->log('updated delegates');
		$competitions = Competition::model()->findAllByAttributes(array(
			'type'=>Competition::TYPE_WCA,
		), array(
			'condition'=>'date < unix_timestamp() + 86400 * 365 AND date > unix_timestamp() - 86400 * 20',
			'order'=>'date ASC',
		));
		$wcaDb = intval(file_get_contents(dirname(__DIR__) . '/config/wcaDb'));
		$sql = "UPDATE `user` `u`
				INNER JOIN `registration` `r` ON `u`.`id`=`r`.`user_id`
				LEFT JOIN `competition` `c` ON `r`.`competition_id`=`c`.`id`
				LEFT JOIN `wca_v2_{$wcaDb}`.`results` `rs`
					ON `c`.`wca_competition_id`=`rs`.`competition_id`
					AND `rs`.`person_name`=CASE WHEN `u`.`name_zh`='' THEN `u`.`name` ELSE CONCAT(`u`.`name`, ' (', `u`.`name_zh`, ')') END
				SET `u`.`wcaid`=`rs`.`person_id`
				WHERE `u`.`wcaid`='' AND `rs`.`person_id` IS NOT NULL AND `r`.`status`=1 AND `r`.`competition_id`=%id%";
		$db = Yii::app()->db;
		$num = [];
		foreach ($competitions as $competition) {
			$num[$competition->id] = $db->createCommand(str_replace('%id%', $competition->id, $sql))->execute();
			if ($competition->wca_competition_id == '') {
				$wcaCompetition = Competitions::model()->findByAttributes([
					'year'=>intval(date('Y', $competition->date)),
					'month'=>intval(date('m', $competition->date)),
					'day'=>intval(date('d', $competition->date)),
				], [
					'condition'=>"external_website LIKE '%{$competition->alias}%'",
				]);
				if ($wcaCompetition !== null) {
					$competition->wca_competition_id = $wcaCompetition->id;
					$competition->formatDate();
					$competition->save();
				}
			}
		}
		$this->log('updated wcaid:', array_sum($num));
		Yii::app()->cache->flush();
		foreach (Championships::getAllTypes() as $type) {
			Yii::app()->cache->getData('Championships::buildChampionshipPodiums', [$type]);
		}
		$this->log('podiums built');
		Yii::import('application.statistics.*');
		foreach (Statistics::$lists as $key=>$list) {
			Statistics::getData($key + 1);
		}
		$this->log('statistics data built');
	}

	public function actionBuildRanksSum() {
		Yii::getLogger()->autoDump = true;
		Yii::getLogger()->autoFlush = 1;
		$events = Events::getNormalEvents();
		$persons = Persons::model()->with('country')->findAllByAttributes(['sub_id'=>1]);
		RanksSum::model()->getDbConnection()->createCommand()->truncateTable('ranks_sum');
		foreach (['single', 'average'] as $type) {
			$className = 'Ranks' . ucfirst($type);
			foreach ($persons as $person) {
				$ranks = $className::model()->findAllByAttributes([
					'person_id'=>$person->wca_id,
				]);
				$sum = $this->getPenlties($type, $person->country);
				foreach ($ranks as $rank) {
					$sum['worldRank'][$rank->event_id] = $rank->world_rank;
					if ($rank->continent_rank > 0) {
						$sum['continentRank'][$rank->event_id] = $rank->continent_rank;
					}
					if ($rank->country_rank > 0) {
						$sum['countryRank'][$rank->event_id] = $rank->country_rank;
					}
				}
				$ranksSum = new RanksSum();
				$ranksSum->person_id = $person->wca_id;
				$ranksSum->country_id = $person->country_id;
				$ranksSum->continent_id = $person->country->continent_id;
				$ranksSum->type = $type;
				foreach ($sum as $key=>$value) {
					$ranksSum->$key = array_sum($value);
				}
				$ranksSum->save();
			}
		}
	}

	private function getPenlties($type, $country) {
		if (isset($this->_panalties[$type][$country->id])) {
			return $this->_panalties[$type][$country->id];
		}
		return $this->_panalties[$type][$country->id] = [
			'worldRank'=>RanksPenalty::getPenlties($type, 'World'),
			'continentRank'=>RanksPenalty::getPenlties($type, $country->continent_id),
			'countryRank'=>RanksPenalty::getPenlties($type, $country->id),
		];
	}

	private function log() {
		printf("[%s] %s\n", date('Y-m-d H:i:s'), implode(' ', func_get_args()));
	}
}
