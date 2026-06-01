<?php

class m260601_034800_add_dual_to_competition_event extends CDbMigration {
	public function up() {
		$this->addColumn('competition_event', 'dual', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		return true;
	}

	public function down() {
		$this->dropColumn('competition_event', 'dual');
		return true;
	}
}
