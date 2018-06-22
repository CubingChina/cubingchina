<?php

class m180622_162144_add_podiums_events extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'podiums_events', "VARCHAR(256) DEFAULT '[\"333\"]'");
		$this->refreshTableSchema('competition');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'podiums_events');
		$this->refreshTableSchema('competition');
		return true;
	}
}
