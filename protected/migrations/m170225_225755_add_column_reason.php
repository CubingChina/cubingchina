<?php

class m170225_225755_add_column_reason extends CDbMigration {
	public function up() {
		$this->addColumn('competition_application', 'reason', "TEXT NULL DEFAULT NULL AFTER `other`");
		return true;
	}

	public function down() {
		$this->dropColumn('competition_application', 'reason');
		return true;
	}
}
