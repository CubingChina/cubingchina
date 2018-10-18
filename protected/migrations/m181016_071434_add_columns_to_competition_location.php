<?php

class m181016_071434_add_columns_to_competition_location extends CDbMigration {
	public function up() {
		$this->addColumn('competition_location', 'delegate_email', 'VARCHAR(128) NOT NULL DEFAULT ""');
		$this->addColumn('competition_location', 'payment_method', 'VARCHAR(1024) NOT NULL DEFAULT ""');
		$this->renameColumn('competition_location', 'delegate_text', 'delegate_name');
		$locations = CompetitionLocation::model()->findAll([
			'condition'=>"delegate_name!=''",
		]);
		foreach ($locations as $location) {
			if (!preg_match('{i>\s*(?P<name>[^\]]+).+?mailto:((?P<email>[^\)]+))}', $location->delegate_name, $matches)) {
				echo 'Failed: ', $location->delegate_name, "\n";
				continue;
			}
			$location->delegate_name = $matches['name'];
			$location->delegate_email = $matches['email'];
			$location->save(false);
		}
		return true;
	}

	public function down() {
		$locations = CompetitionLocation::model()->findAll([
			'condition'=>"delegate_name!=''",
		]);
		foreach ($locations as $location) {
			$location->delegate_name = "[<i class=\"fa fa-envelope\"></i> {$location->delegate_name}](mailto:{$location->delegate_email})";
			$location->save(false);
		}
		$this->dropColumn('competition_location', 'delegate_email');
		$this->dropColumn('competition_location', 'payment_method');
		$this->renameColumn('competition_location', 'delegate_name', 'delegate_text');
		return true;
	}
}
