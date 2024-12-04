<?php

class m241203_081720_add_more_old_man_podiums extends CDbMigration {
	public $oldManAges = [
		25, 30, 35, 45
	];
	public function up() {
		// freely define a old old old man podium age numbers.
		$this->addColumn('competition', 'podiums_o', "varchar(512) NOT NULL DEFAULT ''");
		foreach ($this->oldManAges as $age) {
			$this->addColumn('competition', 'podiums_o' . $age, 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		}
		$this->refreshTableSchema('competition');
	}

	public function down() {
		$this->dropColumn('competition', 'podiums_o');
		foreach ($this->oldManAges as $age) {
			$this->dropColumn('competition', 'podiums_o' . $age);
		}
		$this->refreshTableSchema('competition');
		return true;
	}
}
