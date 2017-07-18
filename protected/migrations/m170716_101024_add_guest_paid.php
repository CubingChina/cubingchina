<?php

class m170716_101024_add_guest_paid extends CDbMigration {
	public function up() {
		$this->addColumn('registration', 'guest_paid', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->refreshTableSchema('registration');
		$this->execute('UPDATE registration SET guest_paid=1 WHERE paid=1 AND has_entourage=1');
		return true;
	}

	public function down() {
		$this->dropColumn('registration', 'guest_paid');
		return true;
	}
}
