<?php

class m180706_155849_add_allow_change_event extends CDbMigration {
	public function up() {
		$this->createTable('pay_event', [
			'id'=>'INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'pay_id'=>"INT(11) UNSIGNED NOT NULL",
			'registration_event_id'=>'VARCHAR(6)',
			'status'=>'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"',
			'create_time'=>'INT(11) UNSIGNED NOT NULL DEFAULT "0"',
			'update_time'=>'INT(11) UNSIGNED NOT NULL DEFAULT "0"',
		]);
		$payments = Pay::model()->findAll();
		$count = count($payments);
		echo "Start to migrate payments...\n";
		$start = microtime(true);
		foreach ($payments as $index=>$pay) {
			if ($pay->type == Pay::TYPE_REGISTRATION) {
				$registration = $pay->registration;
				if ($registration === null) {
					continue;
				}
				foreach ($registration->allEvents as $registrationEvent) {
					$payEvent = new PayEvent();
					$payEvent->pay_id = $pay->id;
					$payEvent->registration_event_id = $registrationEvent->id;
					$payEvent->status = $pay->status;
					$payEvent->save();
				}
			}
			printf("%5d/%5d\r", $index + 1, $count);
		}
		$time = microtime(true) - $start;
		echo "Finished migration in {$time} seconds. {$payEvent->primaryKey} record created.\n";
		$this->createIndex('pay_id', 'pay_event', 'pay_id');
		$this->createIndex('registration_event_id', 'pay_event', 'registration_event_id');
		$this->addColumn('competition', 'allow_change_event', 'TINYINT(1) UNSIGNED NOT NULL DEFAULT "1"');
		$this->update('competition', ['allow_change_event'=>0]);
		$this->refreshTableSchema('competition');
		return true;
	}

	public function down() {
		$this->dropColumn('competition', 'allow_change_event');
		$this->dropTable('pay_event');
		return true;
	}
}
