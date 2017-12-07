<?php

class m171206_132015_add_podiums_num extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'podiums_num', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "3"');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'podiums_num');
		return true;
	}
}
