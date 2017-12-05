<?php

class m171202_191838_create_wechat_tables extends CDbMigration {
	public function up() {
		$this->createTable('wechat_user', [
			'id'=>'varchar(32) NOT NULL PRIMARY KEY',
			'nickname'=>'varchar(128) NOT NULL',
			'avatar'=>'varchar(256) NOT NULL',
			'nickname'=>'varchar(128) NOT NULL',
			'user_id'=>"int(11) UNSIGNED NOT NULL DEFAULT '0'",
		]);
		$this->createIndex('user_id', 'wechat_user', 'user_id');
		return true;
	}

	public function down() {
		$this->dropTable('wechat_user');
		return true;
	}
}
