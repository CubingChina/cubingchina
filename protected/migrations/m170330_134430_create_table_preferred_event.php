<?php

class m170330_134430_create_table_preferred_event extends CDbMigration {

	public function up() {
		$this->createTable('preferred_event', [
			'id'=>'int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'user_id'=>"int(11) UNSIGNED NOT NULL",
			'event'=>'varchar(32)',
		]);
		$this->createIndex('user_id', 'preferred_event', 'user_id');
		return true;
	}

	public function down() {
		$this->dropTable('preferred_event');
		return true;
	}
}
