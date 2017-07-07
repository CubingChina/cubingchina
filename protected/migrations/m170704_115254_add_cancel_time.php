<?php

class m170704_115254_add_cancel_time extends CDbMigration {
	public function up() {
		$this->addColumn('registration', 'cancel_time', 'INT(11) UNSIGNED NOT NULL DEFAULT "0"');
		$this->refreshTableSchema('registration');
		return true;
	}

	public function down() {
		$this->dropColumn('registration', 'cancel_time');
		return true;
	}
}
