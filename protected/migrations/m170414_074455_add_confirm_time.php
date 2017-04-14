<?php

class m170414_074455_add_confirm_time extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'confirm_time', "INT(10) UNSIGNED DEFAULT '0'");
		$this->execute('UPDATE competition SET confirm_time = update_time WHERE status IN (' . implode(',', [Competition::STATUS_CONFIRMED, Competition::STATUS_REJECTED]) . ')');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'confirm_time');
		return true;
	}
}
