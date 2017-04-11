<?php

class m170410_124332_add_unique_index extends CDbMigration {
	public function up() {
		$this->createIndex('email', 'user', 'email', true);
		$this->createIndex('competition_user', 'registration', ['competition_id', 'user_id'], true);
		return true;
	}

	public function down() {
		$this->createIndex('email', 'user');
		$this->createIndex('competition_user', 'registration');
		return true;
	}
}
