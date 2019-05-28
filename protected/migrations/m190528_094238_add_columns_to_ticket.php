<?php

class m190528_094238_add_columns_to_ticket extends CDbMigration {
	public function up() {
		$this->addColumn('ticket', 'multi_days', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
		return true;
	}

	public function down() {
		$this->dropColumn('ticket', 'multi_days');
		return true;
	}
}
