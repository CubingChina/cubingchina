<?php

class m170706_164824_add_cancellation_end_and_reopen_time extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'cancellation_end_time', 'INT(11) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('competition', 'reg_reopen_time', 'INT(11) UNSIGNED NOT NULL DEFAULT "0"');
		$this->refreshTableSchema('competition');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'cancellation_end_time');
		$this->dropColumn('competition', 'reg_reopen_time');
		return true;
	}
}
