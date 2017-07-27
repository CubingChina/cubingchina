<?php

class m170726_050625_add_more_podiums_options extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'podiums_greater_china', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('competition', 'podiums_u8', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('competition', 'podiums_u10', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('competition', 'podiums_u12', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'podiums_greater_china');
		$this->dropColumn('competition', 'podiums_u8');
		$this->dropColumn('competition', 'podiums_u10');
		$this->dropColumn('competition', 'podiums_u12');
		return true;
	}
}
