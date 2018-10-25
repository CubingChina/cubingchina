<?php

class m181025_093507_add_transfer_order_id extends CDbMigration {
	public function up() {
		$this->addColumn('pay', 'transfer_order_id', 'VARCHAR(64) NOT NULL DEFAULT ""');
		$this->refreshTableSchema('pay');
		return true;
	}

	public function down() {
		$this->dropColumn('pay', 'transfer_order_id');
		$this->refreshTableSchema('pay');
		return true;
	}
}
