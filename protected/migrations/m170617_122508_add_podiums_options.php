<?php

class m170617_122508_add_podiums_options extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'podiums_children', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "1"');
		$this->addColumn('competition', 'podiums_females', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "1"');
		$this->addColumn('competition', 'podiums_new_comers', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "1"');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'podiums_children');
		$this->dropColumn('competition', 'podiums_females');
		$this->dropColumn('competition', 'podiums_new_comers');
		return true;
	}
}
