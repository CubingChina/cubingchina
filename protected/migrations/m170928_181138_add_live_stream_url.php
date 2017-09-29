<?php

class m170928_181138_add_live_stream_url extends CDbMigration {
	public function up() {
		$this->addColumn('competition', 'live_stream_url', 'VARCHAR(256) NOT NULL DEFAULT ""');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'live_stream_url');
		return true;
	}
}
