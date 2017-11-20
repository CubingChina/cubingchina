<?php

class m171120_093311_add_show_as_delegate extends CDbMigration {
	public function up() {
		$this->addColumn('user', 'show_as_delegate', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		return true;
	}

	public function down() {
		$this->dropColumn('user', 'show_as_delegate');
		return true;
	}
}
