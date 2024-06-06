<?php

class m240606_031434_add_column_special extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'special', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'special');
		return true;
	}
}
