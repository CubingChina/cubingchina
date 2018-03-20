<?php

class m180320_130915_add_annoucement_posted extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'announcement_posted', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->update('competition', ['announcement_posted'=>Competition::YES], 'status=1');
		$this->refreshTableSchema('competition');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'announcement_posted');
		return true;
	}
}
