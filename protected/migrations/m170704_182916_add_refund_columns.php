<?php

class m170704_182916_add_refund_columns extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'refund_type', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('pay', 'refund_amount', 'INT(10) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('pay', 'refund_time', 'INT(11) UNSIGNED NOT NULL DEFAULT "0"');
		$this->refreshTableSchema('competition');
		$this->refreshTableSchema('pay');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'refund_type');
		$this->dropColumn('pay', 'refund_amount');
		$this->dropColumn('pay', 'refund_time');
		return true;
	}
}
