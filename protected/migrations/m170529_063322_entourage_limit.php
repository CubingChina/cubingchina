<?php

class m170529_063322_entourage_limit extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'entourage_limit', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('competition', 'entourage_fee', 'SMALLINT(3) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('registration', 'has_entourage', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('registration', 'entourage_name', 'VARCHAR(100) DEFAULT ""');
		$this->renameColumn('registration', 'passport_type', 'entourage_passport_type');
		$this->renameColumn('registration', 'passport_name', 'entourage_passport_name');
		$this->renameColumn('registration', 'passport_number', 'entourage_passport_number');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'entourage_limit');
		$this->dropColumn('competition', 'entourage_fee');
		$this->dropColumn('registration', 'has_entourage');
		$this->dropColumn('registration', 'entourage_name');
		$this->renameColumn('registration', 'entourage_passport_type', 'passport_type');
		$this->renameColumn('registration', 'entourage_passport_name', 'passport_name');
		$this->renameColumn('registration', 'entourage_passport_number', 'passport_number');
		return true;
	}
}
