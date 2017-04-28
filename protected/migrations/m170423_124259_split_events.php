<?php

class m170423_124259_split_events extends CDbMigration {
	public function up() {
		$this->createTable('competition_event', [
			'id'=>'int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'competition_id'=>"int(11) UNSIGNED NOT NULL",
			'event'=>'',
			'organized_competition'=>'text NOT NULL',
			'self_introduction'=>'text NOT NULL',
			'team_introduction'=>'text NOT NULL',
			'venue_detail'=>'text NOT NULL',
			'budget'=>'text NOT NULL',
			'sponsor'=>'text NOT NULL',
			'other'=>'text NOT NULL',
			'create_time'=>"int(11) UNSIGNED NOT NULL",
			'update_time'=>"int(11) UNSIGNED NOT NULL",
		]);
		$this->createIndex('competition_id', 'competition_application', 'competition_id');
		return true;
	}

	public function down() {
		$this->dropTable('competition_event');
		return true;
	}
}
