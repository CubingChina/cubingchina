<?php

class m170812_213954_add_two_options extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 't_shirt', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('competition', 'staff', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('registration', 't_shirt_size', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('registration', 'staff_type', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('registration', 'staff_statement', 'VARCHAR(2048) DEFAULT ""');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 't_shirt');
		$this->dropColumn('competition', 'staff');
		$this->dropColumn('registration', 'staff_type');
		$this->dropColumn('registration', 'staff_statement');
		return true;
	}
}
