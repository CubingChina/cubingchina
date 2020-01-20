<?php

class m200119_135056_add_regulations_options extends CDbMigration {
	public $options = [
		'automatic_regulations' => 1,
		'entry_ticket',
		'guest_limit',
		'attend_ceremory',
	];

	public $intOptions = [
		'name_card_fee',
	];

	public function up() {
		foreach ($this->options as $option=>$default) {
			if (!is_int($default)) {
				$option = $default;
				$default = 0;
			}
			$this->addColumn('competition', $option, 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "' . $default . '"');
		}
		foreach ($this->intOptions as $option) {
			$this->addColumn('competition', $option, 'INT(10) UNSIGNED NOT NULL DEFAULT "0"');
		}
		$this->update('competition', [
			'automatic_regulations'=>0,
		], 'status=1');
		$this->refreshTableSchema('competition');
		return true;
	}

	public function down() {
		foreach ($this->options as $option=>$default) {
			if (!is_int($default)) {
				$option = $default;
			}
			$this->dropColumn('competition', $option);
		}
		foreach ($this->intOptions as $option) {
			$this->dropColumn('competition', $option);
		}
		return true;
	}
}
