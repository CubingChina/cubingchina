<?php

class m171107_065440_add_status_to_competition_location extends CDbMigration {
	public function up() {
		$this->addColumn('competition_location', 'status', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "1"');
		return true;
	}

	public function down() {
		$this->dropColumn('competition_location', 'status');
		return true;
	}
}
