<?php

class m171116_120220_create_custom_event extends CDbMigration {
	public function up() {
		$this->createTable('custom_event', [
			'id'=>'varchar(32) PRIMARY KEY',
			'name'=>'varchar(32) NOT NULL',
			'name_zh'=>'varchar(32) NOT NULL',
			'rank'=>'int(11) NOT NULL DEFAULT "0"',
		]);
		$this->alterColumn('competition_event', 'event', 'varchar(32)');
		$this->alterColumn('live_event_round', 'event', 'varchar(32)');
		$this->alterColumn('live_result', 'event', 'varchar(32)');
		return true;
	}

	public function down() {
		$this->dropTable('custom_event');
		return true;
	}
}
