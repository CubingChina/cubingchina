<?php

class m171120_090234_add_has_icon extends CDbMigration {
	public function up() {
		$this->addColumn('custom_event', 'has_icon', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		return true;
	}

	public function down() {
		$this->dropColumn('custom_event', 'has_icon');
		return true;
	}
}
