<?php

class m240729_083249_create_score_taker extends CDbMigration {
	public function up() {
		$this->createTable('score_taker', [
			'id'=>'INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'competition_id'=>"int(11) UNSIGNED NOT NULL DEFAULT '0'",
			'user_id'=>"int(11) UNSIGNED NOT NULL DEFAULT '0'",
		]);
		return true;
	}

	public function down() {
		$this->dropTable('score_taker');
		return true;
	}
}
