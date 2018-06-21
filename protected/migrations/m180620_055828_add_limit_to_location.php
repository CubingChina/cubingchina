<?php

class m180620_055828_add_limit_to_location extends CDbMigration {
	public function up() {
		$this->addColumn('competition_location', 'competitor_limit', 'SMALLINT(3) UNSIGNED NOT NULL DEFAULT "0"');
		$this->refreshTableSchema('competition_location');
		return true;
	}

	public function down() {
		$this->dropColumn('competition_location', 'competitor_limit');
		return true;
	}
}
