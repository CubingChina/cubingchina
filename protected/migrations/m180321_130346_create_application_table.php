<?php

class m180321_130346_create_application_table extends CDbMigration {
	public function up() {
		$this->createTable('application', [
			'id'=>'int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'name'=>'varchar(128) NOT NULL',
			'name_zh'=>'varchar(128) NOT NULL',
			'scopes'=>'varchar(1024) NOT NULL DEFAULT ""',
			'key'=>'varchar(32) NOT NULL',
			'secret'=>'varchar(32) NOT NULL',
			'status'=>'tinyint(1) NOT NULL DEFAULT "0"',
			'create_time'=>'int(11) NOT NULL DEFAULT "0"',
			'update_time'=>'int(11) NOT NULL DEFAULT "0"',
		]);
		$this->createIndex('application', 'key', 'key');
		$this->addColumn('pay', 'params', 'varchar(2048) NOT NULL DEFAULT ""');
		return true;
	}

	public function down() {
		$this->dropTable('application');
		return true;
	}
}
