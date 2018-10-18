<?php

class m180930_113827_add_signin_to_user_ticket extends CDbMigration {
	public function up() {
		$this->addColumn('user_ticket', 'signed_in', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('user_ticket', 'signed_date', 'INT(11) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('user_ticket', 'signed_scan_code', 'VARCHAR(20) NOT NULL DEFAULT ""');
		return true;
	}

	public function down() {
		$this->dropColumn('user_ticket', 'signed_in');
		$this->dropColumn('user_ticket', 'signed_date');
		$this->dropColumn('user_ticket', 'signed_scan_code');
		return true;
	}
}
