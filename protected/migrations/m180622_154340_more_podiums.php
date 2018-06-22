<?php

class m180622_154340_more_podiums extends CDbMigration {
	public $ages = [
		3, 4, 5, 6, 7,
		9, 11, 13, 14, 15, 16, 17, 18,
	];
	public function up() {
		foreach ($this->ages as $age) {
			$this->addColumn('competition', 'podiums_u' . $age, 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		}
		$this->refreshTableSchema('competition');
		return true;
	}

	public function down() {
		foreach ($this->ages as $age) {
			$this->dropColumn('competition', 'podiums_u' . $age);
		}
		$this->refreshTableSchema('competition');
		return true;
	}
}
