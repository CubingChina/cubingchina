<?php

class m250219_062240_add_column_newcomer extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'newcomer', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		return true;
	}

	public function down() 	{
		$this->dropColumn('competition', 'newcomer');
		return true;
	}
}
