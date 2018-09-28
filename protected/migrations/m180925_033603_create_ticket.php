<?php

class m180925_033603_create_ticket extends CDbMigration {
	public function up() {
		$this->createTable('ticket', [
			'id'=>'INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'type'=>'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
			'type_id'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'name'=>'varchar(32) NOT NULL',
			'name_zh'=>'varchar(32) NOT NULL',
			'description'=>'varchar(2048) NOT NULL',
			'description_zh'=>'varchar(2048) NOT NULL',
			'fee'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'purchase_deadline'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'number'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'status'=>'tinyint(1) NOT NULL DEFAULT "0"',
			'create_time'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'update_time'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
		]);
		$this->createIndex('type', 'ticket', ['type', 'type_id']);
		$this->createTable('user_ticket', [
			'id'=>'INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'ticket_id'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'user_id'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'total_amount'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'paid_amount'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'paid_time'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'discount'=>'tinyint(3) UNSIGNED NOT NULL DEFAULT "0"',
			'name'=>'VARCHAR(100) NOT NULL',
			'passport_type'=>'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"',
			'passport_name'=>'VARCHAR(100) NOT NULL DEFAULT ""',
			'passport_number'=>'VARCHAR(50) NOT NULL',
			'code'=>'VARCHAR(64) NOT NULL DEFAULT ""',
			'status'=>'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
			'create_time'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'update_time'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
			'cancel_time'=>'int(11) UNSIGNED NOT NULL DEFAULT "0"',
		]);
		$this->createIndex('ticket_id', 'user_ticket', ['ticket_id']);
		$this->createIndex('user_id', 'user_ticket', ['user_id', 'ticket_id']);
		$this->createIndex('user_discount', 'user_ticket', ['user_id', 'discount']);
		return true;
	}

	public function down() {
		$this->dropTable('ticket');
		$this->dropTable('user_ticket');
		return true;
	}
}
