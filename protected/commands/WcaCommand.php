<?php

class WcaCommand extends CConsoleCommand {
	public function actionUpdate() {
		$this->log('start update');
		$competitions = Competition::model()->findAllByAttributes(array(
			'type'=>Competition::TYPE_WCA,
		), array(
			'condition'=>'date < unix_timestamp() AND date > unix_timestamp() - 86400 * 20',
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
				WHERE `u`.`wcaid`='' and `r`.`competition_id`=%id%";
		$db = Yii::app()->db;
		$num = [];
		foreach ($competitions as $competition) {
			$num[$competition->id] = $db->createCommand(str_replace('%id%', $competition->id, $sql))->execute();
		}
		$this->log('updated wcaid:', array_sum($num));
		$result = Results::buildChampionshipPodiums();
		$this->log('build podiums:', $result ? 1 : 0);
		Yii::import('application.statistics.*');
		Yii::app()->cache->flush();
		$data = Statistics::getData(true);
		$this->log('set results_statistics_data:', $data ? 1 : 0);
	}

	private function log() {
		printf("[%s] %s\n", date('Y-m-d H:i:s'), implode(' ', func_get_args()));
	}
}
