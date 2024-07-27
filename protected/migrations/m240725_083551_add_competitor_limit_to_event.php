<?php

class m240725_083551_add_competitor_limit_to_event extends CDbMigration {
	public function up() {
		$this->addColumn('competition_event', 'competitor_limit', 'mediumint UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('competition', 'competitor_limit_type', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		return true;
	}

	public function down() {
		$this->dropColumn('competition_event', 'competitor_limit');
		$this->dropColumn('competition', 'competitor_limit_type');
		return true;
	}
}
