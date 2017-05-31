<?php

class m170529_020116_move_passport_info extends CDbMigration {
	public function safeUp() {
		$this->addColumn('user', 'passport_type', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"');
		$this->addColumn('user', 'passport_name', 'VARCHAR(100) DEFAULT ""');
		$this->addColumn('user', 'passport_number', 'VARCHAR(50) DEFAULT ""');
		//import data
		$ac2016 = Competition::model()->findByPk(440);
		$registrations = Registration::getRegistrations($ac2016, true);
		foreach ($registrations as $registration) {
			$user = $registration->user;
			$user->passport_type = $registration->passport_type;
			$user->passport_name = $registration->passport_name;
			$user->passport_number = $registration->passport_number;
			$user->save();
		}
	}

	public function safeDown() {
		$this->dropColumn('user', 'passport_type');
		$this->dropColumn('user', 'passport_name');
		$this->dropColumn('user', 'passport_number');
		return true;
	}
}
