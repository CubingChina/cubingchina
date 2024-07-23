<?php

class m240722_130652_create_competition_organizer_team_member extends CDbMigration {
	public function up() {
		$this->createTable('competition_organizer_team_member', [
			'id'=>'INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'competition_id'=>'int(11) UNSIGNED NOT NULL',
			'competition_id'=>"int(11) UNSIGNED NOT NULL DEFAULT '0'",
			'user_id'=>"int(11) UNSIGNED NOT NULL DEFAULT '0'",
		]);
		return true;
	}

	public function down() {
		$this->dropTable('competition_organizer_team_member');
		return true;
	}
}
