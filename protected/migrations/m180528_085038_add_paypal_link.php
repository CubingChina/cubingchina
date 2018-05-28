<?php

class m180528_085038_add_paypal_link extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'paypal_link', 'VARCHAR(256) DEFAULT ""');
		$this->refreshTableSchema('competition');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'paypal_link');
		return true;
	}
}
