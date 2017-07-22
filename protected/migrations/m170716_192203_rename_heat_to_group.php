<?php

class m170716_192203_rename_heat_to_group extends CDbMigration {
	public function up() {
		$this->renameTable('heat_schedule', 'group_schedule');
		$this->renameTable('heat_schedule_user', 'user_schedule');
		$this->renameColumn('user_schedule', 'heat_id', 'group_id');
		$this->alterColumn('group_schedule', 'number', 'INT(10) UNSIGNED NOT NULL DEFAULT "0"');
		$this->alterColumn('group_schedule', 'time_limit', 'INT(10) UNSIGNED NOT NULL DEFAULT "0"');
		$this->alterColumn('group_schedule', 'cut_off', 'INT(10) UNSIGNED NOT NULL DEFAULT "0"');
		return true;
	}

	public function down() {
		$this->renameTable('group_schedule', 'heat_schedule');
		$this->renameTable('user_schedule', 'heat_schedule_user');
		$this->renameColumn('heat_schedule_user', 'group_id', 'heat_id');
		return true;
	}
}
