<?php

class m181029_115038_change_event_column extends CDbMigration {
	public function up() {
		$this->alterColumn('registration_event', 'event', 'VARCHAR(32)');
	}

	public function down() {
		return true;
	}
}
