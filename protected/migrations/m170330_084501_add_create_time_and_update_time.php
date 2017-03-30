<?php

class m170330_084501_add_create_time_and_update_time extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'create_time', "int(11) UNSIGNED NOT NULL DEFAULT '0'");
		$this->addColumn('competition', 'update_time', "int(11) UNSIGNED NOT NULL DEFAULT '0'");
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'create_time');
		$this->dropColumn('competition', 'update_time');
		return true;
	}
}
