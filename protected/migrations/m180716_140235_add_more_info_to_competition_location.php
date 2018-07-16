<?php

class m180716_140235_add_more_info_to_competition_location extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'complex_multi_location', 'TINYINT(11) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('competition_location', 'organizer_id', 'INT(11) UNSIGNED NOT NULL DEFAULT "0"');
		$this->refreshTableSchema('competition_location');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'complex_multi_location');
		$this->dropColumn('competition_location', 'organizer_id');
		return true;
	}
}
