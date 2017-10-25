<?php

class m171025_094052_add_auto_accept extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'auto_accept', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->execute('UPDATE competition SET auto_accept=1-check_person');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'auto_accept');
		return true;
	}
}
