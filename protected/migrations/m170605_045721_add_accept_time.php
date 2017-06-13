<?php

class m170605_045721_add_accept_time extends CDbMigration {
	public function up() {
		$this->addColumn('registration', 'accept_time', 'INT(11) UNSIGNED NOT NULL DEFAULT "0"');
		$this->refreshTableSchema('registration');
		$this->execute('UPDATE registration SET accept_time = date WHERE status=1');
		return true;
	}

	public function down() {
		$this->dropColumn('registration', 'accept_time');
		return true;
	}
}
