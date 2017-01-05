<?php

class m170105_055652_add_coloums_for_pay_table extends CDbMigration {
	public function up() {
		$this->addColumn('pay', 'paid_time', "INT(11) UNSIGNED NOT NULL DEFAULT '0'");
		$this->addColumn('pay', 'paid_amount', "INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER amount");
		$this->execute('UPDATE `pay` SET paid_time=update_time, paid_amount=amount WHERE status=1');
		return true;
	}

	public function down() {
		$this->dropColumn('pay', 'paid_time');
		$this->dropColumn('pay', 'paid_amount');
		return true;
	}
}
