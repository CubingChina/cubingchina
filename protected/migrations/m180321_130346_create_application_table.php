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
		$this->createIndex('key', 'application', 'key');
		$this->addColumn('pay', 'params', 'varchar(2048) NOT NULL DEFAULT ""');
		$this->addColumn('pay', 'notify_result', 'tinyint(1) NOT NULL DEFAULT "0"');
		$this->addColumn('pay', 'notify_times', 'smallint(3) NOT NULL DEFAULT "0"');
		$this->addColumn('pay', 'last_notify_time', 'int(11) NOT NULL DEFAULT "0"');
		return true;
	}

	public function down() {
		$this->dropTable('application');
		$this->dropColumn('pay', 'params');
		$this->dropColumn('pay', 'notify_result');
		$this->dropColumn('pay', 'notify_times');
		$this->dropColumn('pay', 'last_notify_time');
		return true;
	}
}
