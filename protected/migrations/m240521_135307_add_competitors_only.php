<?php

class m240521_135307_add_competitors_only extends CDbMigration {
	public function up() {
		$this->addColumn('ticket', 'competitors_only', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
		$this->addColumn('ticket', 'purchase_limit', "TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'");
		return true;
	}

	public function down() {
		$this->dropColumn('ticket', 'competitors_only');
		$this->dropColumn('ticket', 'purchase_limit');
		return true;
	}
}
