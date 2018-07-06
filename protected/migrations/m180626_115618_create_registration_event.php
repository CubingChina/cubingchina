<?php

class m180626_115618_create_registration_event extends CDbMigration {
	public function up() {
		$this->renameColumn('registration', 'events', 'old_events');
		$this->createTable('registration_event', [
			'id'=>'INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
			'registration_id'=>"INT(11) UNSIGNED NOT NULL",
			'event'=>'VARCHAR(6)',
			'fee'=>'MEDIUMINT(6) UNSIGNED NOT NULL DEFAULT "0"',
			'paid'=>'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"',
			'status'=>'TINYINT(1) UNSIGNED NOT NULL DEFAULT "0"',
			'create_time'=>'INT(11) UNSIGNED NOT NULL DEFAULT "0"',
			'update_time'=>'INT(11) UNSIGNED NOT NULL DEFAULT "0"',
			'accept_time'=>'INT(11) UNSIGNED NOT NULL DEFAULT "0"',
		]);
		$competitions = Competition::model()->findAllByAttributes([
			'status'=>Competition::STATUS_SHOW,
		]);
		$count = count($competitions);
		echo "Start to migrate registrations...\n";
		$start = microtime(true);
		foreach ($competitions as $index=>$competition) {
			$registrations = Registration::getRegistrations($competition, true);
			$c = count($registrations);
			foreach ($registrations as $i=>$registration) {
				$events = (array)json_decode($registration->old_events);
				foreach ($events as $event) {
					$registrationEvent = new RegistrationEvent();
					$registrationEvent->registration_id = $registration->id;
					$registrationEvent->event = $event;
					$registrationEvent->fee = $competition->getEventFee($event, $competition->calculateStage($registration->accept_time));
					$registrationEvent->paid = $registration->paid;
					$registrationEvent->status = $registration->status;
					$registrationEvent->create_time = $registration->date;
					$registrationEvent->update_time = max($registration->date, $registration->accept_time);
					$registrationEvent->accept_time = $registration->accept_time;
					$registrationEvent->save();
				}
				printf("%4d/%4d %4d/%4d\r", $index + 1, $count, $i + 1, $c);
			}
		}
		$time = microtime(true) - $start;
		echo "Finished migration in {$time} seconds. {$registrationEvent->id} record created.\n";
		$this->createIndex('registration_id', 'registration_event', 'registration_id');
		$this->createIndex('event', 'registration_event', 'event');
		$this->createIndex('registration_event', 'registration_event', ['registration_id', 'event'], true);
		return true;
	}

	public function down() {
		$this->dropTable('registration_event');
		$this->renameColumn('registration', 'old_events', 'events');
		return true;
	}
}
