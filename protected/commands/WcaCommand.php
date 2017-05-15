<?php

class WcaCommand extends CConsoleCommand {
	private $_panalties = [];

	public function actionUpdate() {
		$this->log('start update');
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
				LEFT JOIN `wca_{$wcaDb}`.`Results` `rs`
					ON `c`.`wca_competition_id`=`rs`.`competitionId`
					AND `rs`.`personName`=CASE WHEN `u`.`name_zh`='' THEN `u`.`name` ELSE CONCAT(`u`.`name`, ' (', `u`.`name_zh`, ')') END
				SET `u`.`wcaid`=`rs`.`personId`
				WHERE `u`.`wcaid`='' AND `rs`.`personId` IS NOT NULL AND `r`.`competition_id`=%id%";
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
					$competition->formatEvents();
					$competition->formatDate();
					$competition->save();
				}
			}
		}
		$this->log('updated wcaid:', array_sum($num));
		// foreach (Competitions::$championshipPatterns as $type=>$patterns) {
		// 	foreach ($patterns as $regionId=>$pattern) {
		// 		Yii::app()->cache->getData('Results::buildChampionshipPodiums', array($type, $regionId));
		// 	}
		// }
		// $this->log('podiums built');
		// Yii::import('application.statistics.*');
		// Yii::app()->cache->flush();
		// $data = Statistics::getData(true);
		// $this->log('set results_statistics_data:', $data ? 1 : 0);
	}

	public function actionBuildRanksSum() {
		Yii::getLogger()->autoDump = true;
		Yii::getLogger()->autoFlush = 1;
		$events = Events::getNormalEvents();
		$persons = Persons::model()->with('country')->findAllByAttributes(['subid'=>1]);
		RanksSum::model()->getDbConnection()->createCommand()->truncateTable('RanksSum');
		foreach (['single', 'average'] as $type) {
			$className = 'Ranks' . ucfirst($type);
			foreach ($persons as $person) {
				$ranks = $className::model()->findAllByAttributes([
					'personId'=>$person->id,
				]);
				$sum = $this->getPenlties($type, $person->country);
				foreach ($ranks as $rank) {
					$sum['worldRank'][$rank->eventId] = $rank->worldRank;
					if ($rank->continentRank > 0) {
						$sum['continentRank'][$rank->eventId] = $rank->continentRank;
					}
					if ($rank->countryRank > 0) {
						$sum['countryRank'][$rank->eventId] = $rank->countryRank;
					}
				}
				$ranksSum = new RanksSum();
				$ranksSum->personId = $person->id;
				$ranksSum->countryId = $person->countryId;
				$ranksSum->continentId = $person->country->continentId;
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
			'continentRank'=>RanksPenalty::getPenlties($type, $country->continentId),
			'countryRank'=>RanksPenalty::getPenlties($type, $country->id),
		];
	}

	private function log() {
		printf("[%s] %s\n", date('Y-m-d H:i:s'), implode(' ', func_get_args()));
	}
}
