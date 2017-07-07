<?php

class m170706_180859_add_has_been_full extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'has_been_full', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->refreshTableSchema('competition');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'has_been_full');
		return true;
	}
}
