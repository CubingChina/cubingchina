<?php

class m170423_124259_split_events extends CDbMigration {
	public function up() {
		$this->createTable('competition_event', [
			'id'=>'int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'competition_id'=>"int(11) UNSIGNED NOT NULL",
			'event'=>'varchar(6)',
			'round'=>'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
			'fee'=>'mediumint(6) UNSIGNED NOT NULL DEFAULT "0"',
			'fee_second'=>'mediumint(6) UNSIGNED NOT NULL DEFAULT "0"',
			'fee_third'=>'mediumint(6) UNSIGNED NOT NULL DEFAULT "0"',
			'qualifying_best'=>'mediumint(6) UNSIGNED NOT NULL DEFAULT "0"',
			'qualifying_average'=>'mediumint(6) UNSIGNED NOT NULL DEFAULT "0"',
			'create_time'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'update_time'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
		]);
		$this->createIndex('competition_id', 'competition_event', 'competition_id');
		$this->createIndex('event', 'competition_event', 'event');
		$this->createIndex('competition_event', 'competition_event', ['competition_id', 'event'], true);
		$this->addColumn('competition', 'has_qualifying_time', "TINYINT(1) UNSIGNED DEFAULT '0' AFTER `disable_chat`");
		$competitions = Competition::model()->findAll();
		foreach ($competitions as $competition) {
			$competition->events = json_decode($competition->events, true);
			foreach ($competition->events as $event=>$value) {
				if ($value['round'] == 0) {
					continue;
				}
				$competitionEvent = new CompetitionEvent();
				$competitionEvent->competition_id = $competition->id;
				$competitionEvent->event = $event;
				$competitionEvent->attributes = $value;
				$competitionEvent->save();
			}
		}
		$this->dropColumn('competition', 'events');
		return true;
	}

	public function down() {
		$this->dropTable('competition_event');
		$this->dropColumn('competition', 'has_qualifying_time');
		$this->addColumn('competition', 'events', "TEXT NOT NULL AFTER `reg_end`");
		return true;
	}
}
