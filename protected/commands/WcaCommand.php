<?php

class WcaCommand extends CConsoleCommand {
	public function actionUpdate() {
		$competitions = Competition::model()->findAllByAttributes(array(
			'type'=>Competition::TYPE_WCA,
		), array(
			'condition'=>'date < unix_timestamp() AND date > unix_timestamp() - 86400 * 7',
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
		echo 'updated wcaid: ', array_sum($num), PHP_EOL;
		Yii::import('application.statistics.*');
		Yii::app()->cache->flush();
		$data = Statistics::getData(true);
		echo 'set results_statistics_data: ', $data ? 1 : 0, PHP_EOL;
	}
}
